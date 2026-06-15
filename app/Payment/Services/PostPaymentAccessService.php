<?php

namespace App\Payment\Services;

use App\Enum\PaymentMethod;
use App\Enum\PaymentStatus;
use App\Models\Payment;
use App\Settings\SettingsRepositoryInterface;

class PostPaymentAccessService
{
    public function __construct(
        private readonly SettingsRepositoryInterface $settingsRepository,
    ) {}

    public function getWhatsappGroupUrl(?Payment $payment): ?string
    {
        if (! $this->canAccessWhatsappGroup($payment)) {
            return null;
        }

        return $this->getConfiguredWhatsappGroupUrl();
    }

    public function canAccessWhatsappGroup(?Payment $payment): bool
    {
        if (! $payment) {
            return false;
        }

        $paymentStatus = $payment->payment_status_name;
        $paymentMethod = $payment->payment_method_name;

        if (! $paymentStatus instanceof PaymentStatus || ! $paymentMethod instanceof PaymentMethod) {
            return false;
        }

        if ($paymentMethod === PaymentMethod::MANUAL_TRANSFER) {
            return $paymentStatus === PaymentStatus::SETTLEMENT;
        }

        return in_array($paymentStatus, [PaymentStatus::SETTLEMENT, PaymentStatus::CAPTURE], true);
    }

    public function getConfiguredWhatsappGroupUrl(): ?string
    {
        $url = trim((string) $this->settingsRepository->get('post_payment_whatsapp_group_url', ''));

        return $this->isValidWhatsappGroupUrl($url) ? $url : null;
    }

    public function isValidWhatsappGroupUrl(?string $url): bool
    {
        if (! $url) {
            return false;
        }

        $parts = parse_url($url);

        if (! is_array($parts)) {
            return false;
        }

        $scheme = strtolower($parts['scheme'] ?? '');
        $host = strtolower($parts['host'] ?? '');
        $path = trim((string) ($parts['path'] ?? ''), '/');

        if ($scheme !== 'https' || $host !== 'chat.whatsapp.com' || $path === '') {
            return false;
        }

        return preg_match('/^[A-Za-z0-9]+$/', $path) === 1;
    }
}
