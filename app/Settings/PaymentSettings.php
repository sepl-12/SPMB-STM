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
}
