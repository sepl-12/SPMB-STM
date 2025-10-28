<?php

namespace App\Jobs;

use App\Services\Email\EmailServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60; // seconds
    public int $timeout = 120; // seconds

    public function __construct(
        public readonly string $recipient,
        public readonly Mailable $mailable
    ) {
        $this->onQueue('emails');
    }

    public function handle(EmailServiceInterface $emailService): void
    {
        try {
            if (!$emailService->isHealthy()) {
                throw new \RuntimeException("Email service {$emailService->getServiceName()} is not healthy");
            }

            $messageId = $emailService->send($this->recipient, $this->mailable);

            Log::info('Queued email sent successfully', [
                'service' => $emailService->getServiceName(),
                'recipient' => $this->recipient,
                'message_id' => $messageId,
                'mailable' => get_class($this->mailable),
                'attempt' => $this->attempts(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Queued email send failed', [
                'service' => get_class($emailService ?? 'unknown'),
                'recipient' => $this->recipient,
                'mailable' => get_class($this->mailable),
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw untuk trigger retry
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Email job failed after all retries', [
            'recipient' => $this->recipient,
            'mailable' => get_class($this->mailable),
            'attempts' => $this->attempts(),
            'error' => $exception->getMessage(),
        ]);
    }

    /**
     * Get the unique ID for the job (prevent duplicate emails)
     */
    public function uniqueId(): string
    {
        return md5($this->recipient . get_class($this->mailable) . serialize($this->mailable));
    }
}
