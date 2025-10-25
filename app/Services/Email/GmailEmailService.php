<?php

namespace App\Services\Email;

use App\Jobs\SendEmailJob;
use App\Services\GmailMailableSender;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;

class GmailEmailService implements EmailServiceInterface
{
    public function __construct(
        private readonly GmailMailableSender $gmailSender
    ) {}

    public function send(string $to, Mailable $mailable): string
    {
        try {
            $messageId = $this->gmailSender->send($to, $mailable);

            Log::info('Gmail email sent successfully', [
                'service' => $this->getServiceName(),
                'recipient' => $to,
                'message_id' => $messageId,
                'mailable' => get_class($mailable),
            ]);

            return $messageId;
        } catch (\Throwable $e) {
            Log::error('Gmail email send failed', [
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
                Log::error('Gmail bulk email failed for recipient', [
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
            // Simple health check - verify Gmail API credentials
            $refreshToken = env('GOOGLE_REFRESH_TOKEN');
            $clientId = env('GOOGLE_CLIENT_ID');
            $clientSecret = env('GOOGLE_CLIENT_SECRET');

            return !empty($refreshToken) && !empty($clientId) && !empty($clientSecret);
        } catch (\Throwable) {
            return false;
        }
    }

    public function getServiceName(): string
    {
        return 'gmail_api';
    }
}
