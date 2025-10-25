<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Payment;
use App\Payment\Exceptions\PaymentEmailMismatchException;
use App\Payment\Exceptions\PaymentLinkCreationFailed;
use App\Payment\Exceptions\PaymentNotFoundException;
use App\Payment\Services\PaymentLinkService;
use App\Payment\Services\PaymentNotificationService;
use App\Payment\Services\PaymentStatusService;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        protected readonly MidtransService $midtransService,
        protected readonly PaymentLinkService $paymentLinkService,
        protected readonly PaymentStatusService $paymentStatusService,
        protected readonly PaymentNotificationService $paymentNotificationService
    ) {
    }

    /**
     * Show payment page
     */
    public function show($registration_number)
    {
        try {
            $result = $this->paymentLinkService->showForm($registration_number);
        } catch (PaymentNotFoundException $e) {
            abort(404, $e->getMessage());
        } catch (PaymentLinkCreationFailed $e) {
            return back()->with('error', $e->getMessage());
        }

        if ($result->shouldRedirect()) {
            $redirect = redirect()->route($result->redirectRoute, $result->redirectParams ?? []);

            if ($result->flash) {
                foreach ($result->flash as $key => $message) {
                    $redirect->with($key, $message);
                }
            }

            return $redirect;
        }

        return view('payment.show', [
            'applicant' => $result->applicant,
            'snapToken' => $result->snapToken,
        ]);
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

        return redirect()->route('payment.status', $payment->applicant->registration_number);
    }

    /**
     * Show payment status page
     */
    public function status($registration_number)
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

    /**
     * Show payment success page
     */
    public function success($registration_number)
    {
        try {
            $result = $this->paymentStatusService->getSuccessPage($registration_number);
        } catch (PaymentNotFoundException $e) {
            abort(404, $e->getMessage());
        }

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

        if (!$result->shouldRedirect()) {
            return redirect()->route('payment.show', $request->registration_number);
        }

        $redirect = redirect()->route($result->redirectRoute, $result->redirectParams ?? []);

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
        } catch (PaymentNotFoundException|PaymentEmailMismatchException $e) {
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

        $paymentUrl = route('payment.show', $request->registration_number);

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
}
