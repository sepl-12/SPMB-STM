<?php

namespace App\Payment\Events;

use App\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentSettled
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Payment $payment)
    {
    }
}
