<?php

namespace App\Settings;

use Carbon\Carbon;

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

    /**
     * Get exam start date
     */
    public static function examStartDate(): ?Carbon
    {
        $date = self::instance()->get('exam_start_date');
        return $date ? Carbon::parse($date) : null;
    }

    /**
     * Get exam end date
     */
    public static function examEndDate(): ?Carbon
    {
        $date = self::instance()->get('exam_end_date');
        return $date ? Carbon::parse($date) : null;
    }

    /**
     * Get formatted exam date range for display
     * Example: "20 - 25 Februari 2026"
     */
    public static function getExamDateRange(): ?string
    {
        $startDate = self::examStartDate();
        $endDate = self::examEndDate();

        if (!$startDate || !$endDate) {
            return null;
        }

        // Jika bulan dan tahun sama, format: "20 - 25 Februari 2026"
        if ($startDate->format('m Y') === $endDate->format('m Y')) {
            return $startDate->format('d') . ' - ' . $endDate->format('d F Y');
        }

        // Jika bulan berbeda tapi tahun sama, format: "28 Februari - 5 Maret 2026"
        if ($startDate->format('Y') === $endDate->format('Y')) {
            return $startDate->format('d F') . ' - ' . $endDate->format('d F Y');
        }

        // Jika tahun berbeda, format lengkap: "28 Februari 2026 - 5 Maret 2027"
        return $startDate->format('d F Y') . ' - ' . $endDate->format('d F Y');
    }

    /**
     * Get exam location
     */
    public static function examLocation(): ?string
    {
        return self::instance()->get('exam_location') ?? self::instance()->get('contact_address');
    }
}
