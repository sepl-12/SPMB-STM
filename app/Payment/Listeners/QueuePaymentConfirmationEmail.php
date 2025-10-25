<?php

namespace App\Payment\Listeners;

use App\Mail\PaymentConfirmed;
use App\Payment\Events\PaymentSettled;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class QueuePaymentConfirmationEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(PaymentSettled $event): void
    {
        $payment = $event->payment->loadMissing('applicant');
        $applicant = $payment->applicant;

        if (!$applicant || !$applicant->applicant_email_address || $applicant->applicant_email_address === '-') {
            Log::warning('PaymentSettled event without valid applicant email', [
                'payment_id' => $payment->id,
            ]);

            return;
        }

        Mail::to($applicant->applicant_email_address)
            ->queue(new PaymentConfirmed($payment));
    }
}
