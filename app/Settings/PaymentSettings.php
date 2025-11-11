<?php

namespace App\Settings;

class PaymentSettings
{
    public static function instance(): SettingsRepositoryInterface
    {
        return app(SettingsRepositoryInterface::class);
    }

    public static function gmailSender(): ?string
    {
        return self::instance()->get('payment_gmail_sender');
    }

    public static function midtransCallbackUrl(): ?string
    {
        return self::instance()->get('midtrans_callback_url');
    }

    // ===== Emergency Payment Methods =====

    /**
     * Check if emergency payment mode is enabled
     */
    public static function isEmergencyModeEnabled(): bool
    {
        $value = self::instance()->get('emergency_payment_enabled', 'false');
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Enable or disable emergency payment mode
     */
    public static function setEmergencyMode(bool $enabled): void
    {
        self::instance()->set('emergency_payment_enabled', $enabled ? 'true' : 'false');
    }

    /**
     * Get QRIS image path
     */
    public static function getQrisImagePath(): ?string
    {
        return self::instance()->get('emergency_qris_image');
    }

    /**
     * Set QRIS image path
     */
    public static function setQrisImagePath(?string $path): void
    {
        self::instance()->set('emergency_qris_image', $path);
    }

    /**
     * Get emergency payment instructions
     */
    public static function getEmergencyInstructions(): string
    {
        return self::instance()->get(
            'emergency_payment_instructions',
            'Silakan scan QRIS dan upload bukti pembayaran.'
        );
    }

    /**
     * Get payment account name
     */
    public static function getAccountName(): string
    {
        return self::instance()->get('emergency_payment_account_name', 'Sekolah');
    }

    /**
     * Get all emergency payment settings
     */
    public static function getEmergencySettings(): array
    {
        return [
            'enabled' => self::isEmergencyModeEnabled(),
            'qris_image' => self::getQrisImagePath(),
            'instructions' => self::getEmergencyInstructions(),
            'account_name' => self::getAccountName(),
        ];
    }
}
