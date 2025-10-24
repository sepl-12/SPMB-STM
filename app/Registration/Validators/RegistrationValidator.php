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
        [$rules, $messages, $attributes] = $this->buildRuleSets($fields, $context);

        $validator = ValidatorFacade::make($data, $rules, $messages, $attributes);

        $validator->after(function ($validator) use ($fields, $data) {
            $relevantData = collect($data)
                ->only($fields->pluck('field_key')->all())
                ->toArray();

            $this->emailFieldInspector->inspect($validator, $fields, $relevantData);
        });

        $validated = $validator->validate();

        return collect($validated)
            ->only($fields->pluck('field_key')->all())
            ->toArray();
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
