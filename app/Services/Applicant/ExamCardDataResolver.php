<?php

namespace App\Services\Applicant;

use App\Models\Applicant;
use App\Models\ExamCardFieldConfig;
use App\Models\SubmissionFile;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ExamCardDataResolver
{
    /**
     * Resolve all data needed to render exam card PDF using dynamic field configuration.
     *
     * @return array{
     *     applicant: Applicant,
     *     fields: array<string, array{value: mixed, config: ExamCardFieldConfig}>,
     *     legacy: array (for backward compatibility)
     * }
     */
    public function resolve(Applicant $applicant): array
    {
        if (! $applicant->relationLoaded('wave')) {
            $applicant->load('wave');
        }

        if (! $applicant->relationLoaded('latestSubmission') || ! $applicant->latestSubmission?->relationLoaded('submissionFiles')) {
            $applicant->load(['latestSubmission.submissionFiles.formField']);
        }

        // Load field configurations from database
        $fieldConfigs = ExamCardFieldConfig::enabled()->ordered()->get();

        $answers = $applicant->getLatestSubmissionAnswers();
        $fields = [];

        // Resolve each field based on configuration
        foreach ($fieldConfigs as $config) {
            $value = $this->resolveFieldValue($config, $applicant, $answers);

            $fields[$config->field_key] = [
                'value' => $value,
                'config' => $config,
            ];
        }

        // Build legacy format for backward compatibility
        $legacy = $this->buildLegacyFormat($applicant, $fields);

        return [
            'applicant' => $applicant,
            'fields' => $fields,
            'legacy' => $legacy,
            // Also merge legacy fields at root level for backward compatibility
            ...$legacy,
        ];
    }

    /**
     * Resolve value untuk specific field config
     */
    protected function resolveFieldValue(ExamCardFieldConfig $config, Applicant $applicant, array $answers): mixed
    {
        $fieldKey = $config->field_key;

        // Special handling untuk field-field tertentu
        switch ($fieldKey) {
            case 'registration_number':
                return $applicant->registration_number;

            case 'nisn':
                return $this->firstFilled($answers, $config->getAllKeys()) ?? $applicant->applicant_nisn;

            case 'name':
                return $this->firstFilled($answers, $config->getAllKeys()) ?? $applicant->applicant_full_name;

            case 'birth_place':
                return $this->firstFilled($answers, $config->getAllKeys());

            case 'birth_date':
                $dateRaw = $this->firstFilled($answers, $config->getAllKeys());
                return $this->normalizeDate($dateRaw);

            case 'address':
                return $this->firstFilled($answers, $config->getAllKeys());

            case 'parent_father':
                return $this->firstFilled($answers, $config->getAllKeys());

            case 'parent_mother':
                return $this->firstFilled($answers, $config->getAllKeys());

            case 'whatsapp_parent':
                $value = $this->firstFilled($answers, $config->getAllKeys()) ?? $applicant->applicant_phone_number;
                return $this->cleanPhone($value);

            case 'whatsapp_student':
                $value = $this->firstFilled($answers, $config->getAllKeys());
                return $this->cleanPhone($value);

            case 'email':
                return $this->firstFilled($answers, $config->getAllKeys()) ?? $applicant->applicant_email_address;

            case 'major_first':
                return $this->firstFilled($answers, $config->getAllKeys()) ?? $applicant->chosen_major_name;

            case 'major_second':
            case 'major_third':
                return $this->firstFilled($answers, $config->getAllKeys());

            case 'previous_school':
                return $this->firstFilled($answers, $config->getAllKeys());

            case 'exam_date':
                return $this->resolveExamDate($applicant, $answers);

            case 'signature_date':
                $examDate = $this->resolveExamDate($applicant, $answers);
                return $examDate?->translatedFormat('d F') ?? now()->translatedFormat('d F');

            case 'signature_name':
                $name = $this->firstFilled($answers, ['nama_lengkap', 'full_name', 'name']) ?? $applicant->applicant_full_name;
                return $name;

            case 'photo':
                return $this->resolvePhotoPath($applicant);

            case 'signature_image':
                return $this->resolveSignaturePath($applicant);

            default:
                // Generic field resolution using aliases
                return $this->firstFilled($answers, $config->getAllKeys());
        }
    }

    /**
     * Build legacy array format for backward compatibility
     */
    protected function buildLegacyFormat(Applicant $applicant, array $fields): array
    {
        $examDate = $fields['exam_date']['value'] ?? null;
        $birthDate = $fields['birth_date']['value'] ?? null;
        $birthPlace = $fields['birth_place']['value'] ?? null;

        $birthDateText = $birthDate?->translatedFormat('d F Y');
        $examDateText = $examDate?->translatedFormat('d F Y');

        $whatsappParent = $fields['whatsapp_parent']['value'] ?? null;
        $whatsappStudent = $fields['whatsapp_student']['value'] ?? null;

        return [
            'registration_number' => $fields['registration_number']['value'] ?? $applicant->registration_number,
            'nisn' => $fields['nisn']['value'] ?? null,
            'name' => $fields['name']['value'] ?? $applicant->applicant_full_name,
            'birth_place' => $birthPlace,
            'birth_date' => $birthDate,
            'address' => $fields['address']['value'] ?? null,
            'parent_father' => $fields['parent_father']['value'] ?? null,
            'parent_mother' => $fields['parent_mother']['value'] ?? null,
            'whatsapp_parent' => $whatsappParent,
            'whatsapp_student' => $whatsappStudent,
            'email' => $fields['email']['value'] ?? null,
            'major_first' => $fields['major_first']['value'] ?? null,
            'major_second' => $fields['major_second']['value'] ?? null,
            'major_third' => $fields['major_third']['value'] ?? null,
            'previous_school' => $fields['previous_school']['value'] ?? null,
            'exam_date' => $examDate,
            'signature_city' => setting('exam_card_signature_city', 'Sangatta'),
            'signature_day_month' => $fields['signature_date']['value'] ?? null,
            'signature_name' => $fields['signature_name']['value'] ?? null,
            'photo_path' => $fields['photo']['value'] ?? null,
            'signature_image_path' => $fields['signature_image']['value'] ?? null,
        ];
    }

    /**
     * @param array<string, mixed> $answers
     */
    protected function firstFilled(array $answers, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = Arr::get($answers, $key);

            if (is_array($value)) {
                $value = implode(', ', array_filter($value, fn ($v) => $v !== null && $v !== ''));
            }

            if ($value !== null && $value !== '') {
                return is_string($value) ? trim($value) : (string) $value;
            }
        }

        return null;
    }

    protected function normalizeDate(?string $date): ?Carbon
    {
        if (! $date) {
            return null;
        }

        try {
            return Carbon::parse($date);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Build exam date from answers or wave schedule.
     *
     * @param array<string, mixed> $answers
     */
    protected function resolveExamDate(Applicant $applicant, array $answers): ?Carbon
    {
        $date = $this->firstFilled($answers, ['tanggal_tes', 'tanggal_ujian', 'exam_date']);

        if ($date) {
            $normalized = $this->normalizeDate($date);
            if ($normalized) {
                return $normalized;
            }
        }

        if ($applicant->wave?->end_datetime) {
            return $applicant->wave->end_datetime->copy()->addDays(7);
        }

        return null;
    }

    protected function cleanPhone(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $clean = preg_replace('/\s+/', '', trim($value));

        return $clean !== '' ? $clean : null;
    }

    protected function resolvePhotoPath(Applicant $applicant): ?string
    {
        return $this->resolveFilePathByFieldKeys($applicant, [
            'foto_siswa',
            'pas_foto',
            'pas_foto_siswa',
            'photo',
        ]);
    }

    protected function resolveSignaturePath(Applicant $applicant): ?string
    {
        return $this->resolveFilePathByFieldKeys($applicant, [
            'tanda_tangan_peserta',
        ]);
    }

    /**
     * @param array<int, string> $fieldKeys
     */
    protected function resolveFilePathByFieldKeys(Applicant $applicant, array $fieldKeys): ?string
    {
        $submission = $applicant->latestSubmission;

        if (! $submission) {
            return null;
        }

        /** @var Collection<int, SubmissionFile> $files */
        $files = $submission->relationLoaded('submissionFiles')
            ? $submission->submissionFiles
            : $submission->submissionFiles()->get();

        $file = $files->first(function (SubmissionFile $file) use ($fieldKeys) {
            if ($file->formField) {
                return in_array($file->formField->field_key, $fieldKeys, true);
            }

            return false;
        });

        if (! $file) {
            return null;
        }

        if (! Storage::disk($file->stored_disk_name)->exists($file->stored_file_path)) {
            return null;
        }

        return Storage::disk($file->stored_disk_name)->path($file->stored_file_path);
    }
}
