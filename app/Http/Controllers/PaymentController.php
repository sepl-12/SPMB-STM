<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadManualPaymentRequest;
use App\Models\Applicant;
use App\Models\Payment;
use App\Payment\Exceptions\PaymentEmailMismatchException;
use App\Payment\Exceptions\PaymentLinkCreationFailed;
use App\Payment\Exceptions\PaymentNotFoundException;
use App\Payment\Services\PaymentLinkService;
use App\Payment\Services\PaymentNotificationService;
use App\Payment\Services\PaymentStatusService;
use App\Services\Applicant\ExamCardPdfGenerator;
use App\Services\ManualPayment\ManualPaymentService;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        protected readonly MidtransService $midtransService,
        protected readonly PaymentLinkService $paymentLinkService,
        protected readonly PaymentStatusService $paymentStatusService,
        protected readonly PaymentNotificationService $paymentNotificationService,
        protected readonly ExamCardPdfGenerator $examCardPdfGenerator,
        protected readonly ManualPaymentService $manualPaymentService
    ) {}

    /**
     * Upload manual payment proof
     */
    public function uploadManualPayment(UploadManualPaymentRequest $request, string $registration_number)
    {
        try {
            // Find applicant
            $applicant = Applicant::where('registration_number', $registration_number)
                ->with('wave')
                ->firstOrFail();

            // Check if emergency mode is enabled
            if (!\App\Settings\PaymentSettings::isEmergencyModeEnabled()) {
                return back()->with('error', 'Mode pembayaran darurat tidak aktif.');
            }

            // Check if can upload
            if (!$this->manualPaymentService->canUploadManualPayment($applicant)) {
                return back()->with('error', 'Anda sudah mengupload bukti pembayaran atau pembayaran sudah berhasil.');
            }

            // Validate amount
            $paidAmount = (float) $request->input('paid_amount');
            if (!$this->manualPaymentService->validateAmount($applicant, $paidAmount)) {
                $expectedAmount = number_format($applicant->wave->registration_fee_amount, 0, ',', '.');
                return back()
                    ->withInput()
                    ->with('error', "Jumlah pembayaran tidak sesuai. Harap bayar Rp {$expectedAmount}");
            }

            // Create manual payment
            $manualPayment = $this->manualPaymentService->createManualPayment(
                $applicant,
                $request->file('proof_image'),
                $paidAmount,
                $request->input('payment_notes')
            );

            Log::info('Manual payment uploaded', [
                'registration_number' => $registration_number,
                'manual_payment_id' => $manualPayment->id,
                'amount' => $paidAmount,
            ]);

            return redirect($applicant->getStatusUrl())
                ->with('success', 'Bukti pembayaran berhasil diupload. Mohon tunggu verifikasi dari admin (maksimal 1x24 jam).');
        } catch (\Exception $e) {
            Log::error('Manual payment upload error', [
                'registration_number' => $registration_number,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat upload bukti pembayaran. Silakan coba lagi.');
        }
    }

    /**
     * Handle payment notification from Midtrans
     */
    public function notification(Request $request)
    {
        try {
            $notification = $request->all();

            Log::info('Midtrans Notification Received', $notification);

            // Handle notification
            $this->paymentNotificationService->handle($notification);

            return response()->json(['message' => 'Notification handled successfully']);
        } catch (\Exception $e) {
            Log::error('Midtrans Notification Error: ' . $e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Payment finish callback
     */
    public function finish(Request $request)
    {
        $orderId = $request->input('order_id');

        if (!$orderId) {
            return redirect()->route('home')->with('error', 'Order ID tidak ditemukan.');
        }

        // Find payment
        $payment = Payment::where('merchant_order_code', $orderId)->first();

        if (!$payment) {
            return redirect()->route('home')->with('error', 'Pembayaran tidak ditemukan.');
        }

        // Check transaction status
        $statusCheck = $this->midtransService->checkTransactionStatus($orderId);

        if ($statusCheck['success']) {
            $status = $statusCheck['status'];

            // Update payment based on status check
            $this->paymentNotificationService->handle((array) $status);
        }

        return redirect($payment->applicant->getStatusUrl());
    }

    /**
     * Show payment success page via signed URL (SECURE)
     * This method is for secure access via email links
     */
    public function successSecure(Request $request, string $registration_number)
    {
        // Laravel sudah validasi signed URL di middleware
        // Jika sampai sini, berarti URL valid dan belum expired

        try {
            $result = $this->paymentStatusService->getSuccessPage($registration_number);
        } catch (PaymentNotFoundException $e) {
            abort(404, $e->getMessage());
        }

        // Optional: Log access untuk security monitoring
        Log::channel('stack')->info('Payment success page accessed via signed URL', [
            'registration_number' => $registration_number,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now(),
        ]);

        return view('payment.success', [
            'applicant' => $result->applicant,
            'latestPayment' => $result->latestPayment,
        ]);
    }

    /**
     * Check payment status via AJAX
     */
    public function checkStatus(Request $request)
    {
        $orderId = $request->input('order_id');

        if (!$orderId) {
            return response()->json(['error' => 'Order ID required'], 400);
        }

        $result = $this->paymentStatusService->checkAjaxStatus($orderId);

        return response()->json($result);
    }

    /**
     * Show check payment form
     */
    public function checkPaymentForm()
    {
        return view('payment.check-status');
    }

    /**
     * Find payment by registration number and email
     */
    public function findPayment(Request $request)
    {
        $request->validate([
            'registration_number' => 'required|string',
            'email' => 'required|email',
        ]);

        try {
            $result = $this->paymentLinkService->findPayment(
                $request->registration_number,
                $request->email
            );
        } catch (PaymentNotFoundException $e) {
            return back()->with('error', $e->getMessage());
        } catch (PaymentEmailMismatchException $e) {
            return back()->with('error', $e->getMessage());
        }

        $redirectUrl = $result->redirectUrl ?? $result->applicant->getPaymentUrl();
        $redirect = redirect()->to($redirectUrl);

        if ($result->flash) {
            foreach ($result->flash as $key => $message) {
                $redirect->with($key, $message);
            }
        }

        return $redirect;
    }

    /**
     * Resend payment link via email
     */
    public function resendPaymentLink(Request $request)
    {
        $request->validate([
            'registration_number' => 'required|string',
            'email' => 'required|email',
        ]);

        try {
            $result = $this->paymentLinkService->resendLink(
                $request->registration_number,
                $request->email
            );
        } catch (PaymentNotFoundException | PaymentEmailMismatchException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        } catch (PaymentLinkCreationFailed $e) {
            Log::error('Failed to send payment link: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat transaksi pembayaran'
            ], 500);
        }

        $paymentUrl = $result->applicant->getPaymentUrl();

        Log::info('Payment link requested', [
            'registration_number' => $request->registration_number,
            'email' => $request->email,
            'payment_url' => $paymentUrl,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Link pembayaran: ' . $paymentUrl . ' (Email akan dikirim saat mail dikonfigurasi)',
            'payment_url' => $paymentUrl,
        ]);
    }

    /**
     * Show payment details via signed URL
     * This method is for secure access via email links
     */
    public function showSecure(Request $request, string $registration_number)
    {
        // Laravel sudah validasi signed URL di middleware
        // Jika sampai sini, berarti URL valid dan belum expired

        try {
            $result = $this->paymentLinkService->showForm($registration_number);
        } catch (PaymentNotFoundException $e) {
            abort(404, $e->getMessage());
        } catch (PaymentLinkCreationFailed $e) {
            return back()->with('error', $e->getMessage());
        }

        if ($result->shouldRedirect()) {
            $redirect = redirect()->to($result->redirectUrl);

            if ($result->flash) {
                foreach ($result->flash as $key => $message) {
                    $redirect->with($key, $message);
                }
            }

            return $redirect;
        }

        if (\App\Settings\PaymentSettings::isEmergencyModeEnabled()) {
            return view('payment.emergency', [
                'applicant' => $result->applicant,
                'qrisImage' => \App\Settings\PaymentSettings::getQrisImagePath(),
                'instructions' => \App\Settings\PaymentSettings::getEmergencyInstructions(),
                'accountName' => \App\Settings\PaymentSettings::getAccountName(),
                'registrationFee' => $result->applicant->wave->registration_fee_amount,
            ]);
        }

        return view('payment.show', [
            'applicant' => $result->applicant,
            'snapToken' => $result->snapToken,
        ]);
    }

    /**
     * Show exam card via signed URL
     * This method is for secure access via email links
     */
    public function examCard(Request $request, string $registration_number)
    {
        $applicant = Applicant::where('registration_number', $registration_number)->firstOrFail();
        $applicant->load(['wave', 'latestPayment', 'latestSubmission', 'latestSubmission.submissionFiles.formField']);

        // Validasi applicant sudah bayar
        if (!$applicant->hasSuccessfulPayment()) {
            return response()->view('errors.payment-required', [
                'message' => 'Pembayaran belum dikonfirmasi. Silakan selesaikan pembayaran terlebih dahulu.',
                'applicant' => $applicant,
            ], 403);
        }

        // Reuse cached PDF unless caller requests refresh or data changed after render.
        $forceRefresh = $request->boolean('refresh');
        $absolutePath = $this->examCardPdfGenerator->generateAndStore($applicant, $forceRefresh);
        $fileName = 'kartu-ujian-' . $applicant->registration_number . '.pdf';
        $headers = ['Content-Type' => 'application/pdf'];

        if ($request->boolean('download')) {
            return response()->download($absolutePath, $fileName, $headers);
        }

        return response()->file($absolutePath, array_merge($headers, [
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
        ]));
    }

    /**
     * Show applicant status via signed URL
     * This method is for secure access via email links
     */
    public function statusSecure(Request $request, string $registration_number)
    {
        try {
            $result = $this->paymentStatusService->getStatusPage($registration_number);
        } catch (PaymentNotFoundException $e) {
            abort(404, $e->getMessage());
        }

        return view('payment.status', [
            'applicant' => $result->applicant,
            'latestPayment' => $result->latestPayment,
        ]);
    }
}
