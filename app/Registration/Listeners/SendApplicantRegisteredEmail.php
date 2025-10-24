<?php

namespace App\Registration\Listeners;

use App\Registration\Events\ApplicantRegisteredEvent;
use App\Services\GmailMailableSender;
use App\Mail\ApplicantRegistered;

class SendApplicantRegisteredEmail
{
    public function __construct(private readonly GmailMailableSender $mailableSender)
    {
    }

    public function handle(ApplicantRegisteredEvent $event): void
    {
        $email = $event->applicant->applicant_email_address;

        if (!$email || $email === '-') {
            return;
        }

        try {
            $this->mailableSender->send($email, new ApplicantRegistered($event->applicant));
        } catch (\Throwable $exception) {
            \Log::error('Failed to send registration email', [
                'applicant_id' => $event->applicant->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
