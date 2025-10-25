<?php

namespace App\Payment\DTO;

use App\Models\Payment;

final class SnapTransaction
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $snapToken,
        public readonly Payment $payment,
    ) {
    }
}
