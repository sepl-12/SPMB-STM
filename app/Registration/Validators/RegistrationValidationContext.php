<?php

namespace App\Registration\Validators;

class RegistrationValidationContext
{
    public const SCENARIO_STEP = 'step';
    public const SCENARIO_SUBMIT = 'submit';

    /**
     * @param array<string, mixed> $existingData
     * @param array<string, mixed> $requestData
     */
    public function __construct(
        private readonly array $existingData,
        private readonly array $requestData,
        private readonly string $action,
        private readonly int $stepIndex,
        private readonly string $scenario = self::SCENARIO_STEP
    ) {
    }

    public function action(): string
    {
        return $this->action;
    }

    public function scenario(): string
    {
        return $this->scenario;
    }

    public function stepIndex(): int
    {
        return $this->stepIndex;
    }

    public function shouldValidateFields(): bool
    {
        if ($this->scenario === self::SCENARIO_SUBMIT) {
            return true;
        }

        return in_array($this->action, ['next', 'submit'], true);
    }

    public function hasExistingValue(string $fieldKey): bool
    {
        return !empty($this->existingData[$fieldKey]);
    }

    public function requestValue(string $fieldKey): mixed
    {
        return $this->requestData[$fieldKey] ?? null;
    }

    public function shouldRequireFile(string $fieldKey, bool $isRequired): bool
    {
        if (!$isRequired) {
            return false;
        }

        if ($this->scenario === self::SCENARIO_SUBMIT) {
            return !$this->hasExistingValue($fieldKey);
        }

        // Step validation: only require upload if belum ada file di session dan tidak upload baru
        return !$this->hasExistingValue($fieldKey);
    }
}
