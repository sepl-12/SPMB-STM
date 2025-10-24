<?php

namespace App\Registration\Services;

use Illuminate\Contracts\Session\Session;

class RegistrationSessionStore
{
    private const DATA_KEY = 'registration_data';
    private const STEP_KEY = 'current_step';

    public function __construct(private readonly Session $session)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->session->get(self::DATA_KEY, []);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function putData(array $data): void
    {
        $this->session->put(self::DATA_KEY, $data);
    }

    /**
     * @param array<string, mixed> $stepData
     * @return array<string, mixed>
     */
    public function mergeStepData(array $stepData): array
    {
        $merged = array_merge($this->getData(), $stepData);
        $this->putData($merged);

        return $merged;
    }

    public function clear(): void
    {
        $this->session->forget([self::DATA_KEY, self::STEP_KEY]);
    }

    public function getCurrentStepIndex(): int
    {
        return (int) $this->session->get(self::STEP_KEY, 0);
    }

    public function putCurrentStepIndex(int $index): void
    {
        $this->session->put(self::STEP_KEY, max(0, $index));
    }
}
