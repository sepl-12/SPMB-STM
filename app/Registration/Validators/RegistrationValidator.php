<?php

namespace App\Registration\Validators;

use App\Models\FormField;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\ValidationException;

class RegistrationValidator
{
    public function __construct(
        private readonly RegistrationRuleFactory $ruleFactory,
        private readonly EmailFieldInspector $emailFieldInspector
    ) {
    }

    /**
     * @param Collection<int, FormField> $fields
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     * @throws ValidationException
     */
    public function validate(Collection $fields, RegistrationValidationContext $context, array $data): array
    {
        // Filter fields to only include visible ones based on conditional rules
        $visibleFields = $this->filterVisibleFields($fields, $data);

        [$rules, $messages, $attributes] = $this->buildRuleSets($visibleFields, $context);

        $validator = ValidatorFacade::make($data, $rules, $messages, $attributes);

        $validator->after(function ($validator) use ($visibleFields, $data) {
            $relevantData = collect($data)
                ->only($visibleFields->pluck('field_key')->all())
                ->toArray();

            $this->emailFieldInspector->inspect($validator, $visibleFields, $relevantData);
        });

        $validated = $validator->validate();

        return collect($validated)
            ->only($visibleFields->pluck('field_key')->all())
            ->toArray();
    }

    /**
     * Filter fields to only include visible ones based on conditional rules
     *
     * @param Collection<int, FormField> $fields
     * @param array<string, mixed> $data
     * @return Collection<int, FormField>
     */
    protected function filterVisibleFields(Collection $fields, array $data): Collection
    {
        return $fields->filter(function (FormField $field) use ($data) {
            // If field has no conditional rules, it's always visible
            if (!$field->hasConditionalRules()) {
                return true;
            }

            // Check if field should be visible based on current form data
            return $field->shouldBeVisible($data);
        });
    }

    /**
     * @param Collection<int, FormField> $fields
     * @return array{0: array<string, mixed>, 1: array<string, string>, 2: array<string, string>}
     */
    protected function buildRuleSets(Collection $fields, RegistrationValidationContext $context): array
    {
        $rules = [];
        $messages = [];
        $attributes = [];

        foreach ($fields as $field) {
            $definition = $this->ruleFactory->make($field, $context);

            if (!empty($definition->rules)) {
                $rules[$field->field_key] = $definition->rules;
            }

            foreach ($definition->additionalRules as $key => $additionalRuleSet) {
                $rules[$key] = $additionalRuleSet;
            }

            $messages = array_merge($messages, $definition->messages);

            if ($definition->attribute) {
                $attributes[$field->field_key] = $definition->attribute;
            }
        }

        return [$rules, $messages, $attributes];
    }
}
