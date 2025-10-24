<?php

namespace App\Registration\Support;

use App\Enum\FormFieldType;
use App\Models\FormField;
use Illuminate\Support\Collection;

class RegistrationEmailExtractor
{
    /**
     * @param array<string, mixed> $registrationData
     * @param Collection<int, FormField> $fields
     */
    public function extract(array $registrationData, Collection $fields): string
    {
        $emailFields = $fields->where('field_type', FormFieldType::EMAIL->value);

        foreach ($emailFields as $field) {
            $value = $registrationData[$field->field_key] ?? null;
            if ($value && filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return strtolower(trim((string) $value));
            }
        }

        $commonEmailKeys = ['email', 'email_address', 'applicant_email', 'user_email'];

        foreach ($commonEmailKeys as $key) {
            $value = $registrationData[$key] ?? null;
            if ($value && filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return strtolower(trim((string) $value));
            }
        }

        return '-';
    }
}
