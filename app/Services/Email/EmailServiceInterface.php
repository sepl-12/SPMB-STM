<?php

namespace App\Services\Email;

use Illuminate\Mail\Mailable;

interface EmailServiceInterface
{
    /**
     * Send email immediately
     *
     * @param string $to Recipient email address
     * @param Mailable $mailable Laravel Mailable instance
     * @return string Message ID or tracking identifier
     */
    public function send(string $to, Mailable $mailable): string;

    /**
     * Queue email for background processing
     *
     * @param string $to Recipient email address
     * @param Mailable $mailable Laravel Mailable instance
     * @return void
     */
    public function queue(string $to, Mailable $mailable): void;

    /**
     * Send bulk emails to multiple recipients
     *
     * @param array $recipients Array of email addresses
     * @param Mailable $mailable Laravel Mailable instance
     * @return array Array of message IDs or tracking identifiers
     */
    public function bulk(array $recipients, Mailable $mailable): array;

    /**
     * Check if the service is available/healthy
     *
     * @return bool
     */
    public function isHealthy(): bool;

    /**
     * Get service name for logging/debugging
     *
     * @return string
     */
    public function getServiceName(): string;
}
