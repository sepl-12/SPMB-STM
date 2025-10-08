<?php

namespace App\Exports;

use App\Models\Applicant;
use App\Models\ExportTemplate;
use App\Models\FormField;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ApplicantsExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles
{
    protected ExportTemplate $template;
    protected Collection $applicants;
    protected Collection $columns;

    public function __construct(ExportTemplate $template, Collection $applicants)
    {
        $this->template = $template;
        $this->applicants = $applicants;
        $this->columns = $template->exportTemplateColumns()
            ->orderBy('column_order_number')
            ->get();
    }

    public function collection(): Collection
    {
        return $this->applicants;
    }

    public function headings(): array
    {
        return $this->columns->map(fn ($column) => $column->column_header_label)->toArray();
    }

    public function map($applicant): array
    {
        $row = [];

        foreach ($this->columns as $column) {
            $row[] = $this->getColumnValue($applicant, $column);
        }

        return $row;
    }

    protected function getColumnValue(Applicant $applicant, $column): mixed
    {
        $value = match ($column->source_type_name) {
            'form_field' => $this->getFormFieldValue($applicant, $column->source_key_name),
            'expression' => $this->evaluateExpression($applicant, $column->source_key_name),
            default => null,
        };

        // Apply format hint if exists
        if ($column->column_format_hint && $value !== null) {
            $value = $this->applyFormat($value, $column->column_format_hint);
        }

        return $value;
    }

    protected function getFormFieldValue(Applicant $applicant, string $fieldKey): mixed
    {
        $answer = $applicant->getLatestAnswerForField($fieldKey);

        if ($answer === null) {
            return null;
        }

        // Format based on field type
        $field = FormField::query()
            ->where('field_key', $fieldKey)
            ->first();

        if (!$field) {
            return $answer;
        }

        return match ($field->field_type) {
            'date' => $answer ? \Carbon\Carbon::parse($answer)->format('d/m/Y') : null,
            'checkbox' => is_array($answer) ? implode(', ', $answer) : $answer,
            'file_upload' => is_array($answer) ? implode(', ', array_column($answer, 'url')) : $answer,
            default => $answer,
        };
    }

    protected function evaluateExpression(Applicant $applicant, string $expression): mixed
    {
        // Simple expression evaluation
        // Support common patterns like: registration_number, wave.name, etc.
        
        try {
            return match ($expression) {
                'registration_number' => $applicant->registration_number,
                'registered_datetime' => $applicant->registered_datetime?->format('d/m/Y H:i'),
                'wave.name' => $applicant->wave?->wave_name,
                'wave.year' => $applicant->wave?->year,
                'created_at' => $applicant->created_at?->format('d/m/Y H:i'),
                'updated_at' => $applicant->updated_at?->format('d/m/Y H:i'),
                default => $this->evaluateCustomExpression($applicant, $expression),
            };
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function evaluateCustomExpression(Applicant $applicant, string $expression): mixed
    {
        // Support nested attributes like "wave.wave_name", "form.form_name"
        if (str_contains($expression, '.')) {
            $parts = explode('.', $expression);
            $value = $applicant;

            foreach ($parts as $part) {
                if ($value === null) {
                    return null;
                }

                if (is_object($value)) {
                    $value = $value->$part ?? null;
                } else {
                    return null;
                }
            }

            return $value;
        }

        // Try to get attribute directly
        return $applicant->$expression ?? null;
    }

    protected function applyFormat(mixed $value, string $format): mixed
    {
        // Apply format hints
        return match (strtolower($format)) {
            'uppercase' => strtoupper($value),
            'lowercase' => strtolower($value),
            'capitalize' => ucwords($value),
            'date' => is_string($value) ? \Carbon\Carbon::parse($value)->format('d/m/Y') : $value,
            'datetime' => is_string($value) ? \Carbon\Carbon::parse($value)->format('d/m/Y H:i') : $value,
            'number' => is_numeric($value) ? number_format($value, 0, ',', '.') : $value,
            'decimal' => is_numeric($value) ? number_format($value, 2, ',', '.') : $value,
            default => $value,
        };
    }

    public function title(): string
    {
        return 'Data Pendaftar';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
        ];
    }
}
