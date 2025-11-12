<?php

namespace App\Services;

use App\Models\FormField;
use App\Models\FormVersion;
use App\Models\SubmissionFile;
use Carbon\Carbon;

class FormPreviewService
{
    /**
     * Compile all form data into structured preview format
     *
     * @param array $sessionData Raw form data from session
     * @param FormVersion $formVersion The form version being filled
     * @return array Structured preview data grouped by steps
     */
    public function compilePreviewData(array $sessionData, FormVersion $formVersion): array
    {
        $previewData = [];

        // Group by steps
        $steps = $formVersion->formSteps()
            ->with(['formFields' => function ($query) {
                $query->where('is_archived', false)
                    ->orderBy('field_order_number');
            }])
            ->where('is_visible_for_public', true)
            ->orderBy('step_order_number')
            ->get();

        foreach ($steps as $step) {
            $stepData = [
                'step_id' => $step->id,
                'step_title' => $step->step_title,
                'step_description' => $step->step_description,
                'step_order' => $step->step_order_number,
                'fields' => []
            ];

            foreach ($step->formFields as $field) {
                // Skip fields that should not be displayed based on conditional rules
                if (!$this->shouldDisplayField($field, $sessionData)) {
                    continue;
                }

                $rawValue = $sessionData[$field->field_key] ?? null;

                // Skip empty non-required fields (optional)
                if (!$field->is_required && $this->isEmpty($rawValue)) {
                    continue;
                }

                $stepData['fields'][] = [
                    'field_id' => $field->id,
                    'field_key' => $field->field_key,
                    'field_label' => $field->field_label,
                    'field_type' => $field->field_type,
                    'field_help_text' => $field->field_help_text,
                    'raw_value' => $rawValue,
                    'formatted_value' => $this->formatValue($field, $rawValue),
                    'is_required' => $field->is_required,
                ];
            }

            // Only add step if it has visible fields
            if (!empty($stepData['fields'])) {
                $previewData[] = $stepData;
            }
        }

        return $previewData;
    }

    /**
     * Format value based on field type
     *
     * @param FormField $field The form field definition
     * @param mixed $rawValue The raw value from user input
     * @return string Formatted HTML string for display
     */
    public function formatValue(FormField $field, $rawValue): string
    {
        if ($this->isEmpty($rawValue)) {
            return '<span class="text-gray-400 italic">Tidak diisi</span>';
        }

        return match ($field->field_type) {
            'date' => $this->formatDate($rawValue),
            'email' => $this->formatEmail($rawValue),
            'phone', 'tel' => $this->formatPhone($rawValue),
            'number' => $this->formatNumber($rawValue),
            'select', 'radio' => $this->getLabelFromOptions($field, $rawValue),
            'checkbox', 'multi_select' => $this->getLabelsFromOptions($field, $rawValue),
            'file', 'image' => $this->getFileDisplay($rawValue),
            'signature' => $this->getSignatureDisplay($rawValue),
            'boolean' => $this->formatBoolean($rawValue),
            'textarea' => $this->formatTextarea($rawValue),
            'url' => $this->formatUrl($rawValue),
            default => $this->formatText($rawValue)
        };
    }

    /**
     * Check if field should be displayed based on conditional rules
     *
     * @param FormField $field The field to check
     * @param array $sessionData All form data
     * @return bool True if field should be shown
     */
    public function shouldDisplayField(FormField $field, array $sessionData): bool
    {
        // No conditional rules means always visible
        if (!$field->conditional_rules || empty($field->conditional_rules)) {
            return true;
        }

        // Evaluate all conditional rules (AND logic)
        foreach ($field->conditional_rules as $rule) {
            $dependentFieldKey = $rule['field'] ?? null;
            $operator = $rule['operator'] ?? 'equals';
            $expectedValue = $rule['value'] ?? null;

            if (!$dependentFieldKey) {
                continue;
            }

            $actualValue = $sessionData[$dependentFieldKey] ?? null;

            // Evaluate based on operator
            $conditionMet = $this->evaluateCondition($actualValue, $operator, $expectedValue);

            // If any condition is not met, hide the field
            if (!$conditionMet) {
                return false;
            }
        }

        return true;
    }

    /**
     * Evaluate a single conditional rule
     *
     * @param mixed $actualValue Actual value from form data
     * @param string $operator Comparison operator
     * @param mixed $expectedValue Expected value to compare against
     * @return bool True if condition is met
     */
    protected function evaluateCondition($actualValue, string $operator, $expectedValue): bool
    {
        return match ($operator) {
            'equals', '==' => $actualValue == $expectedValue,
            'not_equals', '!=' => $actualValue != $expectedValue,
            'contains' => is_string($actualValue) && str_contains($actualValue, $expectedValue),
            'not_contains' => is_string($actualValue) && !str_contains($actualValue, $expectedValue),
            'greater_than', '>' => is_numeric($actualValue) && $actualValue > $expectedValue,
            'less_than', '<' => is_numeric($actualValue) && $actualValue < $expectedValue,
            'greater_equal', '>=' => is_numeric($actualValue) && $actualValue >= $expectedValue,
            'less_equal', '<=' => is_numeric($actualValue) && $actualValue <= $expectedValue,
            'is_empty' => $this->isEmpty($actualValue),
            'is_not_empty' => !$this->isEmpty($actualValue),
            default => false
        };
    }

    /**
     * Check if value is empty
     */
    protected function isEmpty($value): bool
    {
        if (is_null($value)) {
            return true;
        }

        if (is_string($value) && trim($value) === '') {
            return true;
        }

        if (is_array($value) && empty($value)) {
            return true;
        }

        return false;
    }

    // ==================== Formatting Methods ====================

    /**
     * Format date value
     */
    protected function formatDate($value): string
    {
        try {
            return Carbon::parse($value)->translatedFormat('d F Y');
        } catch (\Exception $e) {
            return e($value);
        }
    }

    /**
     * Format email with mailto link
     */
    protected function formatEmail($value): string
    {
        return '<a href="mailto:' . e($value) . '" class="text-blue-600 hover:underline">' . e($value) . '</a>';
    }

    /**
     * Format phone number
     */
    protected function formatPhone($value): string
    {
        // Add formatting for Indonesian phone numbers
        $cleaned = preg_replace('/[^0-9]/', '', $value);
        if (strlen($cleaned) >= 10) {
            return preg_replace('/(\d{4})(\d{4})(\d+)/', '$1-$2-$3', $cleaned);
        }
        return e($value);
    }

    /**
     * Format number with thousand separator
     */
    protected function formatNumber($value): string
    {
        if (is_numeric($value)) {
            return number_format($value, 0, ',', '.');
        }
        return e($value);
    }

    /**
     * Format boolean as Yes/No
     */
    protected function formatBoolean($value): string
    {
        $isTrue = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        $badge = $isTrue
            ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Ya</span>'
            : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Tidak</span>';
        return $badge;
    }

    /**
     * Format textarea with line breaks
     */
    protected function formatTextarea($value): string
    {
        return '<div class="whitespace-pre-wrap">' . nl2br(e($value)) . '</div>';
    }

    /**
     * Format URL with link
     */
    protected function formatUrl($value): string
    {
        return '<a href="' . e($value) . '" target="_blank" class="text-blue-600 hover:underline inline-flex items-center gap-1">' .
            e($value) .
            '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>' .
            '</a>';
    }

    /**
     * Format plain text
     */
    protected function formatText($value): string
    {
        return '<span class="font-medium">' . e($value) . '</span>';
    }

    /**
     * Get label from select/radio options
     */
    protected function getLabelFromOptions(FormField $field, $value): string
    {
        $options = $field->field_options_json ?? [];

        foreach ($options as $option) {
            // Use loose comparison to handle type mismatches (string "1" vs int 1)
            if (isset($option['value']) && $option['value'] == $value) {
                return '<span class="font-medium">' . e($option['label'] ?? $value) . '</span>';
            }
        }

        return '<span class="font-medium">' . e($value) . '</span>';
    }

    /**
     * Get labels from checkbox/multi_select options (array)
     */
    protected function getLabelsFromOptions(FormField $field, $values): string
    {
        // Handle null values
        if (is_null($values)) {
            return '<span class="text-gray-400 italic">Tidak ada yang dipilih</span>';
        }

        // Convert to array if needed
        if (!is_array($values)) {
            if (is_string($values)) {
                // Try to decode as JSON
                $decoded = json_decode($values, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $values = $decoded;
                } else {
                    // Not valid JSON array, wrap single value in array
                    $values = [$values];
                }
            } else {
                // It's a scalar value (int, float, bool), wrap in array
                $values = [$values];
            }
        }

        if (empty($values)) {
            return '<span class="text-gray-400 italic">Tidak ada yang dipilih</span>';
        }

        $options = $field->field_options_json ?? [];
        $labels = [];

        foreach ($values as $value) {
            $found = false;
            foreach ($options as $option) {
                if (isset($option['value']) && $option['value'] == $value) {
                    $labels[] = $option['label'] ?? $value;
                    $found = true;
                    break;
                }
            }
            // If option not found in field options, use the raw value
            if (!$found && !empty($value)) {
                $labels[] = $value;
            }
        }

        if (empty($labels)) {
            return '<span class="text-gray-400 italic">Tidak ada yang dipilih</span>';
        }

        $html = '<ul class="list-disc list-inside space-y-1">';
        foreach ($labels as $label) {
            $html .= '<li>' . e($label) . '</li>';
        }
        $html .= '</ul>';

        return $html;
    }

    /**
     * Get file display with thumbnail/icon
     */
    protected function getFileDisplay($value): string
    {
        // $value could be file path, SubmissionFile ID, or temporary file info
        if (is_numeric($value)) {
            $file = SubmissionFile::find($value);
            if ($file) {
                return $this->renderFilePreview($file);
            }
        }

        // For new uploads (temporary files in session)
        if (is_array($value) && isset($value['name'])) {
            return $this->renderTempFilePreview($value);
        }

        return '<span class="inline-flex items-center gap-2 text-sm">' .
            '<svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>' .
            '<span class="font-medium">File diupload</span>' .
            '</span>';
    }

    /**
     * Render file preview for saved files
     */
    protected function renderFilePreview(SubmissionFile $file): string
    {
        $isImage = str_starts_with($file->mime_type_name, 'image/');

        if ($isImage) {
            return '<div class="inline-block">' .
                '<img src="' . $file->getTemporaryUrl() . '" alt="' . e($file->original_file_name) . '" class="max-w-xs rounded-lg shadow-md border border-gray-200" />' .
                '<p class="text-xs text-gray-500 mt-1">' . e($file->original_file_name) . '</p>' .
                '</div>';
        }

        return '<div class="inline-flex items-center gap-2 px-3 py-2 bg-gray-50 rounded-lg border border-gray-200">' .
            '<svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>' .
            '<div>' .
            '<p class="text-sm font-medium text-gray-900">' . e($file->original_file_name) . '</p>' .
            '<p class="text-xs text-gray-500">' . $this->formatFileSize($file->file_size_bytes ?? 0) . '</p>' .
            '</div>' .
            '</div>';
    }

    /**
     * Render temporary file preview
     */
    protected function renderTempFilePreview(array $fileInfo): string
    {
        $fileName = $fileInfo['name'] ?? 'unknown';
        $fileSize = $fileInfo['size'] ?? 0;

        return '<div class="inline-flex items-center gap-2 px-3 py-2 bg-gray-50 rounded-lg border border-gray-200">' .
            '<svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>' .
            '<div>' .
            '<p class="text-sm font-medium text-gray-900">' . e($fileName) . '</p>' .
            '<p class="text-xs text-gray-500">' . $this->formatFileSize($fileSize) . '</p>' .
            '</div>' .
            '</div>';
    }

    /**
     * Format file size to human readable
     */
    protected function formatFileSize(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        } elseif ($bytes < 1048576) {
            return round($bytes / 1024, 2) . ' KB';
        } else {
            return round($bytes / 1048576, 2) . ' MB';
        }
    }

    /**
     * Get signature display (base64 image)
     */
    protected function getSignatureDisplay($value): string
    {
        if (is_string($value) && str_starts_with($value, 'data:image')) {
            return '<div class="inline-block">' .
                '<img src="' . $value . '" alt="Tanda tangan" class="max-w-sm border-2 border-gray-300 rounded-lg p-2 bg-white" style="max-height: 150px;" />' .
                '<p class="text-xs text-gray-500 mt-1">Tanda tangan digital</p>' .
                '</div>';
        }

        return '<span class="inline-flex items-center gap-2 text-sm">' .
            '<svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>' .
            '<span class="font-medium">Tanda tangan tersimpan</span>' .
            '</span>';
    }
}
