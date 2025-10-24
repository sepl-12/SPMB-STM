<?php

namespace App\Registration\Data;

use App\Models\Applicant;
use App\Models\Submission;

class SubmitRegistrationResult
{
    public function __construct(
        public readonly string $registrationNumber,
        public readonly Applicant $applicant,
        public readonly Submission $submission
    ) {
    }
}
