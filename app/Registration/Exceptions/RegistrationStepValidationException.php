<?php

namespace App\Registration\Exceptions;

use Illuminate\Validation\ValidationException;

class RegistrationStepValidationException extends \RuntimeException
{
    /**
     * @param array<string, mixed> $registrationData
     */
    public function __construct(
        private readonly ValidationException $validationException,
        private readonly array $registrationData,
        private readonly int $stepIndex
    ) {
        parent::__construct('Registration step validation failed.');
    }

    public function getValidationException(): ValidationException
    {
        return $this->validationException;
    }

    /**
     * @return array<string, mixed>
     */
    public function getRegistrationData(): array
    {
        return $this->registrationData;
    }

    public function getStepIndex(): int
    {
        return $this->stepIndex;
    }
}
