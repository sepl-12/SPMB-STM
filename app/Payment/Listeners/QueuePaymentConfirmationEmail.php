<?php

namespace App\Payment\Listeners;

use App\Mail\PaymentConfirmed;
use App\Payment\Events\PaymentSettled;
use App\Services\Email\EmailServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class QueuePaymentConfirmationEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(private readonly EmailServiceInterface $emailService) {}

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

        try {
            $this->emailService->queue($applicant->applicant_email_address, new PaymentConfirmed($payment));

            Log::info('Payment confirmation email queued successfully', [
                'payment_id' => $payment->id,
                'recipient' => $applicant->applicant_email_address,
                'service' => $this->emailService->getServiceName(),
            ]);
        } catch (\Throwable $exception) {
            Log::error('Failed to queue payment confirmation email', [
                'payment_id' => $payment->id,
                'recipient' => $applicant->applicant_email_address,
                'service' => $this->emailService->getServiceName(),
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
