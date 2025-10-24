<?php

namespace App\Registration\Data;

class SaveStepResult
{
    /**
     * @param array<string, mixed> $registrationData
     */
    public function __construct(
        public readonly int $currentStepIndex,
        public readonly array $registrationData,
        public readonly ?int $nextStepIndex,
        public readonly bool $shouldSubmit
    ) {
    }
}
