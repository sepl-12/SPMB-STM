<?php

namespace App\Registration\Data;

use App\Models\Form;
use App\Models\FormField;
use App\Models\FormStep;
use App\Models\FormVersion;
use Illuminate\Support\Collection;

class RegistrationWizard
{
    /**
     * @param Collection<int, FormStep> $steps
     */
    public function __construct(
        protected readonly Form $form,
        protected readonly FormVersion $formVersion,
        protected readonly Collection $steps
    ) {
    }

    public function form(): Form
    {
        return $this->form;
    }

    public function formVersion(): FormVersion
    {
        return $this->formVersion;
    }

    /**
     * @return Collection<int, FormStep>
     */
    public function steps(): Collection
    {
        return $this->steps;
    }

    public function stepCount(): int
    {
        return $this->steps->count();
    }

    public function hasStep(int $index): bool
    {
        return $index >= 0 && $index < $this->stepCount();
    }

    public function stepAt(int $index): ?FormStep
    {
        return $this->steps->get($index);
    }

    /**
     * Get all unique non-archived fields from visible steps.
     *
     * @return Collection<int, FormField>
     */
    public function allFields(): Collection
    {
        return $this->steps
            ->flatMap(fn (FormStep $step) => $step->formFields)
            ->unique('id')
            ->values();
    }

    /**
     * Get linked field groups configuration for a specific step
     *
     * @param int $stepIndex
     * @return array<string, array<string>>
     */
    public function getLinkedGroupsForStep(int $stepIndex): array
    {
        $step = $this->stepAt($stepIndex);

        if (!$step) {
            return [];
        }

        $linkedGroups = [];

        foreach ($step->formFields as $field) {
            if (!empty($field->linked_field_group) && !$field->is_archived) {
                $groupName = $field->linked_field_group;

                if (!isset($linkedGroups[$groupName])) {
                    $linkedGroups[$groupName] = [];
                }

                $linkedGroups[$groupName][] = $field->field_key;
            }
        }

        return $linkedGroups;
    }
}
