<?php

namespace App\Services\Email;

use App\Jobs\SendEmailJob;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LaravelEmailService implements EmailServiceInterface
{
    public function send(string $to, Mailable $mailable): string
    {
        try {
            Mail::to($to)->send($mailable);

            $messageId = 'laravel-' . time() . '-' . uniqid();

            Log::info('Laravel email sent successfully', [
                'service' => $this->getServiceName(),
                'recipient' => $to,
                'message_id' => $messageId,
                'mailable' => get_class($mailable),
            ]);

            return $messageId;
        } catch (\Throwable $e) {
            Log::error('Laravel email send failed', [
                'service' => $this->getServiceName(),
                'recipient' => $to,
                'mailable' => get_class($mailable),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function queue(string $to, Mailable $mailable): void
    {
        SendEmailJob::dispatch($to, $mailable)
            ->onQueue('emails');
    }

    public function bulk(array $recipients, Mailable $mailable): array
    {
        $results = [];

        foreach ($recipients as $recipient) {
            try {
                $results[$recipient] = $this->send($recipient, $mailable);
            } catch (\Throwable $e) {
                $results[$recipient] = null;
                Log::error('Laravel bulk email failed for recipient', [
                    'service' => $this->getServiceName(),
                    'recipient' => $recipient,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }

    public function isHealthy(): bool
    {
        try {
            // Check if mail configuration is set
            $mailer = config('mail.default');
            $host = config("mail.mailers.{$mailer}.host");

            return !empty($mailer) && !empty($host);
        } catch (\Throwable) {
            return false;
        }
    }

    public function getServiceName(): string
    {
        return 'laravel_mail';
    }
}
