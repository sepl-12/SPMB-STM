<?php

namespace App\Registration\Validators;

use App\Enum\FormFieldType;
use App\Models\FormField;
use Illuminate\Support\Collection;
use Illuminate\Validation\Validator;

class EmailFieldInspector
{
    /**
     * @param Collection<int, FormField> $fields
     * @param array<string, mixed> $data
     */
    public function inspect(Validator $validator, Collection $fields, array $data): void
    {
        $emailFields = $fields->where('field_type', FormFieldType::EMAIL->value);

        foreach ($emailFields as $field) {
            $fieldKey = $field->field_key;
            $email = $data[$fieldKey] ?? null;

            if (!$email) {
                continue;
            }

            $errors = $this->validateEmailAddress((string) $email, $field);

            foreach ($errors as $error) {
                $validator->errors()->add($fieldKey, $error);
            }
        }
    }

    /**
     * @return array<int, string>
     */
    protected function validateEmailAddress(string $email, FormField $field): array
    {
        $errors = [];
        $fieldLabel = $field->field_label;

        $suspiciousPatterns = [
            '/^[0-9]+@/',
            '/\.\.|@@/',
            '/^\.|\.$/',
            '/\s/',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $email)) {
                $errors[] = sprintf('%s mengandung karakter atau pola yang tidak diizinkan.', $fieldLabel);
                break;
            }
        }

        if (strlen($email) < 5) {
            $errors[] = sprintf('%s terlalu pendek. Minimal 5 karakter.', $fieldLabel);
        }

        if (strlen($email) > 254) {
            $errors[] = sprintf('%s terlalu panjang. Maksimal 254 karakter.', $fieldLabel);
        }

        $parts = explode('@', $email);
        if (count($parts) === 2) {
            [$localPart, $domainPart] = $parts;

            if (strlen($localPart) > 64) {
                $errors[] = sprintf('Bagian sebelum @ pada %s terlalu panjang. Maksimal 64 karakter.', $fieldLabel);
            }

            if (strlen($localPart) < 1) {
                $errors[] = sprintf('Bagian sebelum @ pada %s tidak boleh kosong.', $fieldLabel);
            }

            $disposableDomains = [
                '10minutemail.com',
                'tempmail.org',
                'guerrillamail.com',
                'mailinator.com',
                'throwaway.email',
            ];

            if (in_array(strtolower($domainPart), $disposableDomains, true)) {
                $errors[] = sprintf('%s tidak boleh menggunakan layanan email sementara.', $fieldLabel);
            }
        }

        return $errors;
    }
}
