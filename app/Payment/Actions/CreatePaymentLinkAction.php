<?php

namespace App\Payment\Actions;

use App\Models\Applicant;
use App\Payment\DTO\SnapTransaction;
use App\Payment\Exceptions\PaymentLinkCreationFailed;
use App\Services\MidtransService;

class CreatePaymentLinkAction
{
    public function __construct(private readonly MidtransService $midtransService)
    {
    }

    public function execute(Applicant $applicant): SnapTransaction
    {
        $result = $this->midtransService->createTransaction($applicant);

        if (!($result['success'] ?? false)) {
            throw new PaymentLinkCreationFailed($result['error'] ?? 'Tidak dapat membuat transaksi Midtrans');
        }

        $payment = $applicant->payments()->find($result['payment_id'] ?? null);

        if (!$payment) {
            throw new PaymentLinkCreationFailed('Payment record tidak ditemukan setelah createTransaction.');
        }

        return new SnapTransaction(
            orderId: $result['order_id'],
            snapToken: $result['snap_token'],
            payment: $payment,
        );
    }
}
