<?php

namespace App\Settings;

class GeneralSettings
{
    public static function instance(): SettingsRepositoryInterface
    {
        return app(SettingsRepositoryInterface::class);
    }

    public static function heroTitle(): ?string
    {
        return self::instance()->get('hero_title');
    }

    public static function contactEmail(): ?string
    {
        return self::instance()->get('contact_email');
    }
}
