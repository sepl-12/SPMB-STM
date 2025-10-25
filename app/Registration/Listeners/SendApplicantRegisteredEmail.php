<?php

namespace App\Registration\Listeners;

use App\Registration\Events\ApplicantRegisteredEvent;
use App\Services\Email\EmailServiceInterface;
use App\Mail\ApplicantRegistered;
use Illuminate\Support\Facades\Log;

class SendApplicantRegisteredEmail
{
    public function __construct(private readonly EmailServiceInterface $emailService) {}

    public function handle(ApplicantRegisteredEvent $event): void
    {
        $email = $event->applicant->applicant_email_address;

        if (!$email || $email === '-') {
            return;
        }

        try {
            $this->emailService->queue($email, new ApplicantRegistered($event->applicant));

            Log::info('Registration email queued successfully', [
                'applicant_id' => $event->applicant->id,
                'recipient' => $email,
                'service' => $this->emailService->getServiceName(),
            ]);
        } catch (\Throwable $exception) {
            Log::error('Failed to queue registration email', [
                'applicant_id' => $event->applicant->id,
                'recipient' => $email,
                'service' => $this->emailService->getServiceName(),
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
