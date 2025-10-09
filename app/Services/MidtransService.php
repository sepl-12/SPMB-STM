<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\Payment;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        // Set Midtrans configuration
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * Create Snap transaction for applicant payment
     */
    public function createTransaction(Applicant $applicant): array
    {
        // Generate unique order ID
        $orderId = $this->generateOrderId($applicant);

        // Get registration fee from wave
        $amount = $applicant->wave->registration_fee_amount;

        // Prepare transaction details
        $transactionDetails = [
            'order_id' => $orderId,
            'gross_amount' => (int) $amount,
        ];

        // Customer details
        $customerDetails = [
            'first_name' => $applicant->applicant_full_name,
            'email' => $applicant->getLatestAnswerForField('email') ?? 'no-email@ppdb.com',
            'phone' => $applicant->getLatestAnswerForField('no_hp') ?? $applicant->getLatestAnswerForField('phone') ?? '08123456789',
        ];

        // Item details
        $itemDetails = [
            [
                'id' => 'REG_FEE',
                'price' => (int) $amount,
                'quantity' => 1,
                'name' => 'Biaya Pendaftaran PPDB ' . $applicant->wave->wave_name,
            ],
        ];

        // Build transaction parameters
        $params = [
            'transaction_details' => $transactionDetails,
            'customer_details' => $customerDetails,
            'item_details' => $itemDetails,
            'callbacks' => [
                'finish' => route('payment.finish'),
            ],
        ];

        try {
            // Get Snap token
            $snapToken = Snap::getSnapToken($params);

            // Create payment record
            $payment = Payment::create([
                'applicant_id' => $applicant->id,
                'payment_gateway_name' => 'Midtrans',
                'merchant_order_code' => $orderId,
                'paid_amount_total' => $amount,
                'currency_code' => 'IDR',
                'payment_method_name' => 'Midtrans Snap',
                'payment_status_name' => 'PENDING',
                'status_updated_datetime' => now(),
                'gateway_payload_json' => [
                    'snap_token' => $snapToken,
                    'transaction_params' => $params,
                ],
            ]);

            return [
                'success' => true,
                'snap_token' => $snapToken,
                'order_id' => $orderId,
                'payment_id' => $payment->id,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Handle notification from Midtrans
     */
    public function handleNotification(array $notification): void
    {
        $orderId = $notification['order_id'] ?? null;
        $transactionStatus = $notification['transaction_status'] ?? null;
        $fraudStatus = $notification['fraud_status'] ?? null;

        if (!$orderId) {
            return;
        }

        // Find payment by order ID
        $payment = Payment::where('merchant_order_code', $orderId)->first();

        if (!$payment) {
            return;
        }

        // Determine payment status
        $paymentStatus = $this->determinePaymentStatus($transactionStatus, $fraudStatus);

        // Update payment record
        $payment->update([
            'payment_status_name' => $paymentStatus,
            'payment_method_name' => $notification['payment_type'] ?? 'Midtrans Snap',
            'status_updated_datetime' => now(),
            'gateway_payload_json' => array_merge(
                $payment->gateway_payload_json ?? [],
                ['notification' => $notification]
            ),
        ]);

        // Update applicant payment status
        if ($paymentStatus === 'PAID' || $paymentStatus === 'settlement') {
            $payment->applicant->update([
                'payment_status' => 'paid',
            ]);
        } elseif (in_array($paymentStatus, ['FAILED', 'cancel', 'deny', 'expire'])) {
            $payment->applicant->update([
                'payment_status' => 'unpaid',
            ]);
        }
    }

    /**
     * Determine payment status from Midtrans notification
     */
    protected function determinePaymentStatus(string $transactionStatus, ?string $fraudStatus): string
    {
        if ($transactionStatus === 'capture') {
            return $fraudStatus === 'accept' ? 'PAID' : 'PENDING';
        } elseif ($transactionStatus === 'settlement') {
            return 'PAID';
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            return 'FAILED';
        } elseif ($transactionStatus === 'pending') {
            return 'PENDING';
        }

        return strtoupper($transactionStatus);
    }

    /**
     * Generate unique order ID
     */
    protected function generateOrderId(Applicant $applicant): string
    {
        return 'ORD-' . $applicant->registration_number . '-' . time();
    }

    /**
     * Check transaction status
     */
    public function checkTransactionStatus(string $orderId): array
    {
        try {
            $status = \Midtrans\Transaction::status($orderId);
            
            return [
                'success' => true,
                'status' => $status,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Cancel transaction
     */
    public function cancelTransaction(string $orderId): array
    {
        try {
            $cancel = \Midtrans\Transaction::cancel($orderId);
            
            return [
                'success' => true,
                'data' => $cancel,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
