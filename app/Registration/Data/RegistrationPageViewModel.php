<?php

namespace App\Registration\Data;

class RegistrationPageViewModel
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $steps;

    private int $currentStepIndex;

    /**
     * @param array<string, mixed> $registrationData
     */
    public function __construct(
        private readonly RegistrationWizard $wizard,
        private readonly array $registrationData,
        int $currentStepIndex
    ) {
        $this->steps = $this->transformSteps();
        $this->currentStepIndex = $this->normalizeIndex($currentStepIndex);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function steps(): array
    {
        return $this->steps;
    }

    /**
     * @return array<int, array{title: string}>
     */
    public function stepsForProgress(): array
    {
        return array_map(fn ($step) => ['title' => $step['step_title']], $this->steps);
    }

    public function currentStepIndex(): int
    {
        return $this->currentStepIndex;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function currentStep(): ?array
    {
        $step = $this->steps[$this->currentStepIndex] ?? null;

        if ($step) {
            // Add linked groups configuration
            $step['linked_groups'] = $this->wizard->getLinkedGroupsForStep($this->currentStepIndex);
        }

        return $step;
    }

    /**
     * @return array<string, mixed>
     */
    public function registrationData(): array
    {
        return $this->registrationData;
    }

    public function value(string $fieldKey): mixed
    {
        return $this->registrationData[$fieldKey] ?? null;
    }

    private function transformSteps(): array
    {
        return $this->wizard->steps()->map(function ($step) {
            return [
                'id' => $step->id,
                'step_title' => $step->step_title,
                'step_description' => $step->step_description,
                'fields' => $step->formFields->map(function ($field) {
                    return [
                        'id' => $field->id,
                        'field_key' => $field->field_key,
                        'field_label' => $field->field_label,
                        'field_type' => strtolower($field->field_type),
                        'is_required' => (bool) $field->is_required,
                        'field_placeholder_text' => $field->field_placeholder_text,
                        'field_help_text' => $field->field_help_text,
                        'linked_field_group' => $field->linked_field_group,
                        'options' => collect($field->field_options_json ?? [])->map(fn ($option) => [
                            'label' => $option['label'] ?? $option['value'] ?? '',
                            'value' => $option['value'] ?? $option['label'] ?? '',
                        ])->toArray(),
                    ];
                })->toArray(),
            ];
        })->toArray();
    }

    private function normalizeIndex(int $index): int
    {
        $maxIndex = max(count($this->steps) - 1, 0);

        return max(0, min($maxIndex, $index));
    }
}
