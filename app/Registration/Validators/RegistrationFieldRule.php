<?php

namespace App\Registration\Validators;

class RegistrationFieldRule
{
    /**
     * @param array<int, mixed> $rules
     * @param array<string, string> $messages
     * @param array<string, array<int, mixed>> $additionalRules
     */
    public function __construct(
        public readonly array $rules,
        public readonly array $messages = [],
        public readonly array $additionalRules = [],
        public readonly ?string $attribute = null
    ) {
    }
}
