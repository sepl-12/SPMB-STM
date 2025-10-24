<?php

namespace App\Registration\Services;

use App\Models\Applicant;

class RegistrationNumberGenerator
{
    public function generate(): string
    {
        $year = now()->year;
        $prefix = 'PPDB-' . $year . '-';

        $lastNumber = Applicant::where('registration_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderBy('id', 'desc')
            ->value('registration_number');

        if ($lastNumber) {
            $lastNum = (int) substr($lastNumber, -5);
            $newNum = $lastNum + 1;
        } else {
            $newNum = 1;
        }

        $registrationNumber = $prefix . str_pad($newNum, 5, '0', STR_PAD_LEFT);

        $attempts = 0;
        while (Applicant::where('registration_number', $registrationNumber)->exists() && $attempts < 10) {
            $newNum++;
            $registrationNumber = $prefix . str_pad($newNum, 5, '0', STR_PAD_LEFT);
            $attempts++;
        }

        if ($attempts >= 10) {
            throw new \RuntimeException('Unable to generate unique registration number after 10 attempts');
        }

        return $registrationNumber;
    }
}
