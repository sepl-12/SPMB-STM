<?php

namespace App\Payment\DTO;

use App\Models\Applicant;
use App\Models\Payment;

final class PaymentStatusResult
{
    /**
     * @param array<string, mixed> $extra
     */
    public function __construct(
        public readonly Applicant $applicant,
        public readonly ?Payment $latestPayment,
        public readonly array $extra = [],
    ) {
    }
}
