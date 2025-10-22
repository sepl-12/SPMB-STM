<?php

namespace App\Services;

use App\Enum\FormFieldType;
use App\Models\FormField;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class FormFieldValidationService
{
    /**
     * Validate form fields data
     *
     * @param array $data
     * @param \Illuminate\Database\Eloquent\Collection $fields
     * @return array
     * @throws ValidationException
     */
    public function validateFormData(array $data, $fields): array
    {
        $rules = [];
        $messages = [];
        $attributes = [];

        foreach ($fields as $field) {
            $fieldKey = $field->field_key;
            $fieldRules = $this->getFieldValidationRules($field);

            if (!empty($fieldRules)) {
                $rules[$fieldKey] = $fieldRules;
            }

            // Custom messages
            $fieldMessages = $this->getFieldValidationMessages($field);
            foreach ($fieldMessages as $rule => $message) {
                $messages["{$fieldKey}.{$rule}"] = $message;
            }

            // Attribute names for better error messages
            $attributes[$fieldKey] = $field->field_label;
        }

        $validator = Validator::make($data, $rules, $messages, $attributes);

        // Add custom validation for email fields
        $validator->after(function ($validator) use ($data, $fields) {
            $this->validateEmailFields($validator, $data, $fields);
        });

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Get validation rules for a specific field
     *
     * @param FormField $field
     * @return array
     */
    protected function getFieldValidationRules(FormField $field): array
    {
        $rules = [];

        // Required validation
        if ($field->is_required) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        // Type-specific validation
        $fieldType = FormFieldType::tryFrom($field->field_type);

        switch ($fieldType) {
            case FormFieldType::TEXT:
                $rules[] = 'string';
                $rules[] = 'max:255';
                break;

            case FormFieldType::EMAIL:
                $rules[] = 'string';
                $rules[] = 'max:255';
                // Basic email validation - custom validation will be added in validateEmailFields
                $rules[] = 'email:rfc';
                break;

            case FormFieldType::TEXTAREA:
                $rules[] = 'string';
                $rules[] = 'max:5000';
                break;

            case FormFieldType::NUMBER:
                $rules[] = 'numeric';
                break;

            case FormFieldType::DATE:
                $rules[] = 'date';
                break;

            case FormFieldType::BOOLEAN:
                $rules[] = 'boolean';
                break;

            case FormFieldType::SELECT:
                if ($field->field_options_json) {
                    $validValues = collect($field->field_options_json)->pluck('value')->toArray();
                    $rules[] = 'in:' . implode(',', $validValues);
                }
                break;

            case FormFieldType::MULTI_SELECT:
                $rules[] = 'array';
                if ($field->field_options_json) {
                    $validValues = collect($field->field_options_json)->pluck('value')->toArray();
                    $rules[] = 'array';
                    foreach ($validValues as $value) {
                        $rules["*."] = 'in:' . implode(',', $validValues);
                    }
                }
                break;

            case FormFieldType::FILE:
            case FormFieldType::IMAGE:
                $rules[] = 'file';
                if ($fieldType === FormFieldType::IMAGE) {
                    $rules[] = 'image';
                    $rules[] = 'mimes:jpeg,png,jpg,gif';
                } else {
                    $rules[] = 'mimes:pdf,doc,docx,jpeg,png,jpg';
                }
                $rules[] = 'max:5120'; // 5MB max
                break;
        }

        return $rules;
    }

    /**
     * Get custom validation messages for a field
     *
     * @param FormField $field
     * @return array
     */
    protected function getFieldValidationMessages(FormField $field): array
    {
        $messages = [];
        $fieldLabel = $field->field_label;

        $fieldType = FormFieldType::tryFrom($field->field_type);

        switch ($fieldType) {
            case FormFieldType::EMAIL:
                $messages['email'] = "Format {$fieldLabel} tidak valid. Pastikan menggunakan format email yang benar (contoh: nama@domain.com).";
                $messages['email.rfc'] = "Format {$fieldLabel} tidak sesuai standar RFC. Gunakan format email yang valid.";
                $messages['email.dns'] = "Domain email pada {$fieldLabel} tidak valid atau tidak dapat diverifikasi.";
                break;

            case FormFieldType::NUMBER:
                $messages['numeric'] = "{$fieldLabel} harus berupa angka.";
                break;

            case FormFieldType::DATE:
                $messages['date'] = "{$fieldLabel} harus berupa tanggal yang valid.";
                break;

            case FormFieldType::FILE:
            case FormFieldType::IMAGE:
                $messages['file'] = "{$fieldLabel} harus berupa file.";
                $messages['image'] = "{$fieldLabel} harus berupa gambar.";
                $messages['mimes'] = "{$fieldLabel} harus berupa file dengan format yang diizinkan.";
                $messages['max'] = "{$fieldLabel} tidak boleh lebih dari 5MB.";
                break;
        }

        if ($field->is_required) {
            $messages['required'] = "{$fieldLabel} wajib diisi.";
        }

        return $messages;
    }

    /**
     * Custom validation for email fields with enhanced checks
     *
     * @param \Illuminate\Validation\Validator $validator
     * @param array $data
     * @param \Illuminate\Database\Eloquent\Collection $fields
     */
    protected function validateEmailFields($validator, array $data, $fields): void
    {
        $emailFields = $fields->where('field_type', FormFieldType::EMAIL->value);

        foreach ($emailFields as $field) {
            $fieldKey = $field->field_key;
            $email = $data[$fieldKey] ?? null;

            if (!$email) {
                continue; // Skip if empty (required validation handles this)
            }

            // Enhanced email validation
            $errors = $this->validateEmailAddress($email, $field);

            foreach ($errors as $error) {
                $validator->errors()->add($fieldKey, $error);
            }
        }
    }

    /**
     * Enhanced email validation with custom rules
     *
     * @param string $email
     * @param FormField $field
     * @return array
     */
    protected function validateEmailAddress(string $email, FormField $field): array
    {
        $errors = [];
        $fieldLabel = $field->field_label;

        // Check for common email patterns that should be rejected
        $suspiciousPatterns = [
            '/^[0-9]+@/',  // Starts with numbers only
            '/\.\.|@@/',   // Double dots or double @
            '/^\.|\.$/',   // Starts or ends with dot
            '/\s/',        // Contains whitespace
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $email)) {
                $errors[] = "{$fieldLabel} mengandung karakter atau pola yang tidak diizinkan.";
                break;
            }
        }

        // Check for minimum length
        if (strlen($email) < 5) {
            $errors[] = "{$fieldLabel} terlalu pendek. Minimal 5 karakter.";
        }

        // Check for maximum length
        if (strlen($email) > 254) {
            $errors[] = "{$fieldLabel} terlalu panjang. Maksimal 254 karakter.";
        }

        // Check local part (before @) length
        $parts = explode('@', $email);
        if (count($parts) === 2) {
            $localPart = $parts[0];
            $domainPart = $parts[1];

            if (strlen($localPart) > 64) {
                $errors[] = "Bagian sebelum @ pada {$fieldLabel} terlalu panjang. Maksimal 64 karakter.";
            }

            if (strlen($localPart) < 1) {
                $errors[] = "Bagian sebelum @ pada {$fieldLabel} tidak boleh kosong.";
            }

            // Check for common disposable email domains (optional)
            $disposableDomains = [
                '10minutemail.com',
                'tempmail.org',
                'guerrillamail.com',
                'mailinator.com',
                'throwaway.email',
            ];

            if (in_array(strtolower($domainPart), $disposableDomains)) {
                $errors[] = "{$fieldLabel} tidak boleh menggunakan layanan email sementara.";
            }
        }

        return $errors;
    }

    /**
     * Validate a single field value
     *
     * @param mixed $value
     * @param FormField $field
     * @return array
     * @throws ValidationException
     */
    public function validateSingleField($value, FormField $field): array
    {
        $data = [$field->field_key => $value];
        $fields = collect([$field]);

        return $this->validateFormData($data, $fields);
    }
}
