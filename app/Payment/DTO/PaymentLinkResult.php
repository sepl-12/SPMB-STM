<?php

namespace App\Payment\DTO;

use App\Models\Applicant;
use App\Models\Payment;

final class PaymentLinkResult
{
    public function __construct(
        public readonly Applicant $applicant,
        public readonly ?Payment $payment,
        public readonly ?string $snapToken,
        public readonly ?string $redirectRoute = null,
        public readonly ?array $redirectParams = null,
        public readonly ?array $flash = null,
    ) {
    }

    public function shouldRedirect(): bool
    {
        return $this->redirectRoute !== null;
    }
}
