<?php

namespace App\Payment\Services;

use App\Enum\PaymentMethod;
use App\Enum\PaymentStatus;
use App\Helpers\PaymentHelper;
use App\Models\Payment;
use App\Payment\Events\PaymentSettled;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class PaymentNotificationService
{
    public function handle(array $notification): void
    {
        $orderId = $notification['order_id'] ?? null;
        $transactionStatus = $notification['transaction_status'] ?? null;

        if (!$orderId || !$transactionStatus) {
            Log::warning('Midtrans Notification without order_id/transaction_status', $notification);
            return;
        }

        if (!$this->isValidSignature($notification)) {
            Log::warning('Midtrans Notification: Invalid signature key', ['order_id' => $orderId]);
            return;
        }

        $payment = Payment::where('merchant_order_code', $orderId)->first();

        if (!$payment) {
            Log::warning('Midtrans Notification: Payment not found', ['order_id' => $orderId]);
            return;
        }

        $paymentStatus = PaymentHelper::mapMidtransStatus($transactionStatus, $notification['fraud_status'] ?? null);
        $paymentMethod = PaymentHelper::mapMidtransPaymentType($notification['payment_type'] ?? PaymentMethod::ECHANNEL->value);

        $payment->update([
            'payment_status_name' => $paymentStatus->value,
            'payment_method_name' => $paymentMethod->value,
            'status_updated_datetime' => now(),
            'gateway_payload_json' => array_merge(
                $payment->gateway_payload_json ?? [],
                ['notification' => $notification]
            ),
        ]);

        if ($paymentStatus === PaymentStatus::SETTLEMENT || $paymentStatus === PaymentStatus::CAPTURE) {
            event(new PaymentSettled($payment));
        }
    }

    protected function isValidSignature(array $notification): bool
    {
        $signatureKey = $notification['signature_key'] ?? '';
        $orderId = $notification['order_id'] ?? '';
        $statusCode = $notification['status_code'] ?? '';
        $grossAmount = $notification['gross_amount'] ?? '';
        $serverKey = config('payment.midtrans.server_key');

        if (!$signatureKey || !$serverKey) {
            return false;
        }

        $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        return hash_equals($expected, $signatureKey);
    }
}
