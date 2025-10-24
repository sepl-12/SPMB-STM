<?php

namespace App\Registration\Services;

use App\Models\Applicant;
use App\Models\Wave;
use App\Registration\Exceptions\RegistrationQuotaExceededException;

class WaveQuotaGuard
{
    public function assertAvailability(Wave $wave): void
    {
        if (!$wave->quota_limit) {
            return;
        }

        $lockedWave = Wave::where('id', $wave->id)->lockForUpdate()->first();

        if (!$lockedWave) {
            throw new \RuntimeException('Wave not found or locked.');
        }

        $currentCount = Applicant::where('wave_id', $wave->id)
            ->lockForUpdate()
            ->count();

        if ($currentCount >= $wave->quota_limit) {
            throw new RegistrationQuotaExceededException();
        }
    }
}
