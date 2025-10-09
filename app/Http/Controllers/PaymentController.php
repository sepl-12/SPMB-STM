<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Payment;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Show payment page
     */
    public function show($registration_number)
    {
        $applicant = Applicant::where('registration_number', $registration_number)
            ->with('wave', 'payments')
            ->firstOrFail();

        // Check if applicant already paid
        if ($applicant->payment_status === 'paid') {
            return redirect()->route('payment.success', $registration_number)
                ->with('message', 'Pembayaran Anda sudah berhasil.');
        }

        // Get or create payment
        $existingPayment = $applicant->payments()
            ->where('payment_status_name', 'PENDING')
            ->latest()
            ->first();

        if ($existingPayment && isset($existingPayment->gateway_payload_json['snap_token'])) {
            $snapToken = $existingPayment->gateway_payload_json['snap_token'];
        } else {
            // Create new transaction
            $result = $this->midtransService->createTransaction($applicant);

            if (!$result['success']) {
                return back()->with('error', 'Gagal membuat transaksi pembayaran: ' . $result['error']);
            }

            $snapToken = $result['snap_token'];
        }

        return view('payment.show', compact('applicant', 'snapToken'));
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
            $this->midtransService->handleNotification($notification);

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
            $this->midtransService->handleNotification((array) $status);
        }

        return redirect()->route('payment.status', $payment->applicant->registration_number);
    }

    /**
     * Show payment status page
     */
    public function status($registration_number)
    {
        $applicant = Applicant::where('registration_number', $registration_number)
            ->with('wave', 'payments')
            ->firstOrFail();

        $latestPayment = $applicant->payments()->latest()->first();

        return view('payment.status', compact('applicant', 'latestPayment'));
    }

    /**
     * Show payment success page
     */
    public function success($registration_number)
    {
        $applicant = Applicant::where('registration_number', $registration_number)
            ->with('wave', 'payments')
            ->firstOrFail();

        $latestPayment = $applicant->payments()
            ->where('payment_status_name', 'PAID')
            ->orWhere('payment_status_name', 'settlement')
            ->latest()
            ->first();

        return view('payment.success', compact('applicant', 'latestPayment'));
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

        $result = $this->midtransService->checkTransactionStatus($orderId);

        return response()->json($result);
    }
}
