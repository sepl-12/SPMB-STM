<?php

namespace App\Registration\Validators;

use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class LinkedFieldValidator
{
    /**
     * Validate that linked fields don't have duplicate selections
     *
     * @param Collection $fields Collection of FormField models
     * @param array $data User submitted data
     * @throws ValidationException
     */
    public function validateLinkedFields(Collection $fields, array $data): void
    {
        // Group fields by linked_field_group
        $linkedGroups = $fields
            ->filter(fn($field) => !empty($field->linked_field_group))
            ->groupBy('linked_field_group');

        foreach ($linkedGroups as $groupName => $groupFields) {
            $selectedValues = [];
            $fieldLabels = [];

            foreach ($groupFields as $field) {
                $value = $data[$field->field_key] ?? null;

                // Skip if no value selected
                if (empty($value)) {
                    continue;
                }

                // Check for duplicate
                if (in_array($value, $selectedValues, true)) {
                    $conflictingField = array_search($value, array_combine(
                        $groupFields->pluck('field_key')->toArray(),
                        array_map(fn($k) => $data[$k] ?? null, $groupFields->pluck('field_key')->toArray())
                    ));

                    $conflictingLabel = $groupFields
                        ->where('field_key', $conflictingField)
                        ->first()
                        ?->field_label ?? 'field lain';

                    throw ValidationException::withMessages([
                        $field->field_key => "Pilihan ini sudah dipilih di '{$conflictingLabel}'. Silakan pilih opsi yang berbeda."
                    ]);
                }

                $selectedValues[] = $value;
                $fieldLabels[$field->field_key] = $field->field_label;
            }
        }
    }

    /**
     * Get linked groups configuration for frontend
     *
     * @param Collection $fields
     * @return array
     */
    public function getLinkedGroupsConfig(Collection $fields): array
    {
        $linkedGroups = [];

        foreach ($fields as $field) {
            if (!empty($field->linked_field_group)) {
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
