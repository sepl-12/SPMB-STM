<?php

namespace App\Registration\Validators;

use App\Enum\FormFieldType;
use App\Models\FormField;
use Illuminate\Validation\Rule;

class RegistrationRuleFactory
{
    public function make(FormField $field, RegistrationValidationContext $context): RegistrationFieldRule
    {
        $fieldKey = $field->field_key;
        $rules = [];
        $messages = [];
        $additionalRules = [];

        $isRequired = (bool) $field->is_required;
        $fieldType = FormFieldType::tryFrom($field->field_type);

        if ($context->shouldValidateFields()) {
            if ($fieldType?->isFileUpload()) {
                if ($context->shouldRequireFile($fieldKey, $isRequired)) {
                    $rules[] = 'required';
                } else {
                    $rules[] = 'nullable';
                }
            } else {
                $rules[] = $isRequired ? 'required' : 'nullable';
            }
        } else {
            // Ketika tidak perlu validasi (mis. aksi previous), tetap izinkan field kosong
            $rules[] = 'nullable';
        }

        if ($fieldType) {
            $this->appendTypeSpecificRules($fieldType, $field, $fieldKey, $context, $rules, $additionalRules, $messages);
        }

        if ($isRequired) {
            $messages["{$fieldKey}.required"] = sprintf('%s wajib diisi.', $field->field_label);
        }

        return new RegistrationFieldRule($rules, $messages, $additionalRules, $field->field_label);
    }

    /**
     * @param array<int, mixed> $rules
     * @param array<string, array<int, mixed>> $additionalRules
     * @param array<string, string> $messages
     */
    protected function appendTypeSpecificRules(
        FormFieldType $fieldType,
        FormField $field,
        string $fieldKey,
        RegistrationValidationContext $context,
        array &$rules,
        array &$additionalRules,
        array &$messages
    ): void {
        switch ($fieldType) {
            case FormFieldType::TEXT:
                $rules[] = 'string';
                $rules[] = 'max:255';
                break;
            case FormFieldType::EMAIL:
                $rules[] = 'string';
                $rules[] = 'max:255';
                $rules[] = 'email:rfc';
                $messages["{$fieldKey}.email"] = sprintf('Format %s tidak valid. Pastikan menggunakan format email yang benar (contoh: nama@domain.com).', $field->field_label);
                $messages["{$fieldKey}.email.rfc"] = sprintf('Format %s tidak sesuai standar RFC. Gunakan format email yang valid.', $field->field_label);
                break;
            case FormFieldType::TEXTAREA:
                $rules[] = 'string';
                $rules[] = 'max:5000';
                break;
            case FormFieldType::NUMBER:
                $rules[] = 'numeric';
                $messages["{$fieldKey}.numeric"] = sprintf('%s harus berupa angka.', $field->field_label);
                break;
            case FormFieldType::DATE:
                $rules[] = 'date';
                $messages["{$fieldKey}.date"] = sprintf('%s harus berupa tanggal yang valid.', $field->field_label);
                break;
            case FormFieldType::BOOLEAN:
                $rules[] = 'boolean';
                break;
            case FormFieldType::SELECT:
            case FormFieldType::RADIO:
                $rules[] = 'string';
                $options = $this->extractOptionValues($field);
                if (!empty($options)) {
                    $rules[] = Rule::in($options);
                    $messages["{$fieldKey}.in"] = sprintf('%s harus dipilih dari opsi yang tersedia.', $field->field_label);
                }
                break;
            case FormFieldType::MULTI_SELECT:
                $rules[] = 'array';
                $options = $this->extractOptionValues($field);
                if (!empty($options)) {
                    $additionalRules["{$fieldKey}.*"] = [Rule::in($options)];
                    $messages["{$fieldKey}.*.in"] = sprintf('Setiap pilihan pada %s harus berasal dari opsi yang tersedia.', $field->field_label);
                }
                break;
            case FormFieldType::FILE:
            case FormFieldType::IMAGE:
                if ($context->scenario() === RegistrationValidationContext::SCENARIO_SUBMIT) {
                    $rules[] = 'string';
                } else {
                    $rules[] = 'file';
                    if ($fieldType === FormFieldType::IMAGE) {
                        $rules[] = 'image';
                        $messages["{$fieldKey}.image"] = sprintf('%s harus berupa file gambar.', $field->field_label);
                        $rules[] = 'mimes:jpeg,png,jpg,gif';
                    } else {
                        $rules[] = 'mimes:pdf,doc,docx,jpeg,png,jpg';
                    }
                    $rules[] = 'max:5120';
                    $messages["{$fieldKey}.mimes"] = sprintf('%s harus berupa file dengan format yang diizinkan.', $field->field_label);
                    $messages["{$fieldKey}.max"] = sprintf('%s tidak boleh lebih dari 5MB.', $field->field_label);
                }
                break;
        }
    }

    /**
     * @return array<int, string>
     */
    protected function extractOptionValues(FormField $field): array
    {
        $options = $field->field_options_json ?? [];
        if (!is_array($options)) {
            return [];
        }

        return collect($options)
            ->map(fn ($option) => $option['value'] ?? $option['label'] ?? null)
            ->filter()
            ->values()
            ->all();
    }
}
