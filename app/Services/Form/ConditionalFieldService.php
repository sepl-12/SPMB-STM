<?php

namespace App\Services\Form;

use App\Models\FormField;
use Illuminate\Support\Collection;

class ConditionalFieldService
{
    /**
     * Get configuration for conditional fields (for frontend)
     *
     * @param Collection $fields
     * @return array
     */
    public function getConditionalFieldsConfig(Collection $fields): array
    {
        $config = [];

        foreach ($fields as $field) {
            if ($field->hasConditionalRules()) {
                $config[$field->field_key] = $field->conditional_rules;
            }
        }

        return $config;
    }

    /**
     * Filter fields to only show visible ones based on form data
     *
     * @param Collection $fields
     * @param array $formData
     * @return Collection
     */
    public function filterVisibleFields(Collection $fields, array $formData): Collection
    {
        return $fields->filter(function (FormField $field) use ($formData) {
            return $field->shouldBeVisible($formData);
        });
    }

    /**
     * Get all parent (controller) fields from a collection
     *
     * @param Collection $fields
     * @return Collection
     */
    public function getControllerFields(Collection $fields): Collection
    {
        $controllerFieldKeys = [];

        foreach ($fields as $field) {
            if ($field->hasConditionalRules()) {
                $rule = $field->conditional_rules['show_if'] ?? [];

                if (isset($rule['field'])) {
                    $controllerFieldKeys[] = $rule['field'];
                } elseif (isset($rule['all']) || isset($rule['any'])) {
                    $conditions = $rule['all'] ?? $rule['any'] ?? [];
                    foreach ($conditions as $condition) {
                        if (isset($condition['field'])) {
                            $controllerFieldKeys[] = $condition['field'];
                        }
                    }
                }
            }
        }

        $uniqueKeys = array_unique($controllerFieldKeys);

        return $fields->whereIn('field_key', $uniqueKeys);
    }

    /**
     * Get dependent fields (fields that depend on a specific field)
     *
     * @param Collection $fields
     * @param string $fieldKey
     * @return Collection
     */
    public function getDependentFields(Collection $fields, string $fieldKey): Collection
    {
        return $fields->filter(function (FormField $field) use ($fieldKey) {
            if (!$field->hasConditionalRules()) {
                return false;
            }

            $rule = $field->conditional_rules['show_if'] ?? [];

            // Check single condition
            if (isset($rule['field']) && $rule['field'] === $fieldKey) {
                return true;
            }

            // Check multiple conditions
            if (isset($rule['all']) || isset($rule['any'])) {
                $conditions = $rule['all'] ?? $rule['any'] ?? [];
                foreach ($conditions as $condition) {
                    if (isset($condition['field']) && $condition['field'] === $fieldKey) {
                        return true;
                    }
                }
            }

            return false;
        });
    }

    /**
     * Validate that required fields are provided only if they are visible
     *
     * @param Collection $fields
     * @param array $formData
     * @return array Fields that should be validated
     */
    public function getFieldsToValidate(Collection $fields, array $formData): array
    {
        $fieldsToValidate = [];

        foreach ($fields as $field) {
            // Only include field in validation if it's visible
            if ($field->shouldBeVisible($formData)) {
                $fieldsToValidate[] = $field->field_key;
            }
        }

        return $fieldsToValidate;
    }

    /**
     * Get validation rules only for visible fields
     *
     * @param Collection $fields
     * @param array $formData
     * @return array
     */
    public function getConditionalValidationRules(Collection $fields, array $formData): array
    {
        $rules = [];

        foreach ($fields as $field) {
            // Only add validation rules for visible fields
            if ($field->shouldBeVisible($formData)) {
                $fieldRules = [];

                if ($field->is_required) {
                    $fieldRules[] = 'required';
                } else {
                    $fieldRules[] = 'nullable';
                }

                // Add field-type specific rules
                $fieldRules = array_merge($fieldRules, $this->getFieldTypeRules($field));

                $rules[$field->field_key] = $fieldRules;
            }
        }

        return $rules;
    }

    /**
     * Get validation rules based on field type
     *
     * @param FormField $field
     * @return array
     */
    protected function getFieldTypeRules(FormField $field): array
    {
        return match ($field->field_type) {
            'email' => ['email'],
            'number' => ['numeric'],
            'date' => ['date'],
            'file', 'image' => ['file'],
            'image' => ['image', 'max:2048'],
            default => [],
        };
    }

    /**
     * Check if there are any circular dependencies
     *
     * @param Collection $fields
     * @return array|null Array of circular dependency chain, or null if none found
     */
    public function detectCircularDependencies(Collection $fields): ?array
    {
        foreach ($fields as $field) {
            $visited = [];
            $chain = [];

            if ($this->hasCircularDependency($field, $fields, $visited, $chain)) {
                return $chain;
            }
        }

        return null;
    }

    /**
     * Recursively check for circular dependencies
     */
    protected function hasCircularDependency(
        FormField $field,
        Collection $allFields,
        array &$visited,
        array &$chain
    ): bool {
        if (in_array($field->field_key, $visited)) {
            $chain[] = $field->field_key;
            return true;
        }

        if (!$field->hasConditionalRules()) {
            return false;
        }

        $visited[] = $field->field_key;
        $chain[] = $field->field_key;

        $controllerField = $field->getControllerField();

        if ($controllerField) {
            if ($this->hasCircularDependency($controllerField, $allFields, $visited, $chain)) {
                return true;
            }
        }

        array_pop($chain);

        return false;
    }
}
