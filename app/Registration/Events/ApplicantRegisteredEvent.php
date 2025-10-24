<?php

namespace App\Registration\Events;

use App\Models\Applicant;

class ApplicantRegisteredEvent
{
    public function __construct(public readonly Applicant $applicant)
    {
    }
}
