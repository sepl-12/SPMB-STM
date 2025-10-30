<?php

namespace App\Registration\Support;

use App\Enum\FormFieldType;
use App\Models\FormField;
use App\Models\Submission;
use App\Models\SubmissionAnswer;
use App\Models\SubmissionFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class RegistrationAnswerMapper
{
    /**
     * @param Collection<int, FormField> $fields
     * @param array<string, mixed> $registrationData
     */
    public function persistAnswers(Submission $submission, Collection $fields, array $registrationData): void
    {
        foreach ($fields as $field) {
            $fieldKey = $field->field_key;
            $fieldValue = $registrationData[$fieldKey] ?? null;

            if ($fieldValue === null) {
                continue;
            }

            $fieldType = FormFieldType::tryFrom($field->field_type);

            if (($fieldType?->isFileUpload() || $fieldType?->isSignature()) && is_string($fieldValue)) {
                $this->createFileRecord($submission, $field, $fieldValue);
                continue;
            }

            $this->createAnswerRecord($submission, $field, $fieldValue, $fieldType);
        }
    }

    protected function createFileRecord(Submission $submission, FormField $field, string $storedPath): void
    {
        // Use PRIVATE disk for security - files should only be accessible via signed URLs
        $disk = Storage::disk('private');
        $mimeType = 'application/octet-stream';

        if ($disk->exists($storedPath)) {
            $extension = pathinfo($storedPath, PATHINFO_EXTENSION);
            $mimeTypes = [
                'pdf' => 'application/pdf',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ];
            $mimeType = $mimeTypes[strtolower($extension)] ?? $mimeType;
        }

        SubmissionFile::create([
            'submission_id' => $submission->id,
            'form_field_id' => $field->id,
            'stored_disk_name' => 'private',
            'stored_file_path' => $storedPath,
            'original_file_name' => basename($storedPath),
            'mime_type_name' => $mimeType,
            'file_size_bytes' => $disk->exists($storedPath) ? $disk->size($storedPath) : 0,
            'uploaded_datetime' => now(),
        ]);
    }

    protected function createAnswerRecord(Submission $submission, FormField $field, mixed $fieldValue, ?FormFieldType $fieldType): void
    {
        $answerData = [
            'submission_id' => $submission->id,
            'form_field_id' => $field->id,
            'field_key' => $field->field_key,
        ];

        switch ($fieldType) {
            case FormFieldType::NUMBER:
                $answerData['answer_value_number'] = $fieldValue;
                break;
            case FormFieldType::DATE:
                $answerData['answer_value_date'] = $fieldValue;
                break;
            case FormFieldType::BOOLEAN:
                $answerData['answer_value_boolean'] = (bool) $fieldValue;
                break;
            case FormFieldType::MULTI_SELECT:
                $answerData['answer_value_text'] = is_array($fieldValue) ? json_encode($fieldValue) : $fieldValue;
                break;
            case FormFieldType::EMAIL:
                $answerData['answer_value_text'] = strtolower(trim((string) $fieldValue));
                break;
            default:
                $answerData['answer_value_text'] = $fieldValue;
        }

        SubmissionAnswer::create($answerData);
    }
}
