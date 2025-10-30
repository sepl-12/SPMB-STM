<?php

namespace App\Services\Applicant;

use App\Models\Applicant;
use App\Models\SubmissionFile;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ExamCardDataResolver
{
    /**
     * Resolve all data needed to render exam card PDF.
     *
     * @return array{
     *     applicant: Applicant,
     *     registration_number: string,
     *     nisn: string|null,
     *     name: string,
     *     birth_place: string|null,
     *     birth_date: Carbon|null,
     *     address: string|null,
     *     parent_father: string|null,
     *     parent_mother: string|null,
     *     whatsapp_parent: string|null,
     *     whatsapp_student: string|null,
     *     email: string|null,
     *     major_first: string|null,
     *     major_second: string|null,
     *     major_third: string|null,
     *     previous_school: string|null,
     *     exam_date: Carbon|null,
     *     signature_city: string,
     *     signature_day_month: string|null,
     *     signature_name: string,
     *     photo_path: string|null,
     *     signature_image_path: string|null
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

        $answers = $applicant->getLatestSubmissionAnswers();

        $name = $this->firstFilled($answers, ['nama_lengkap', 'full_name', 'name']) ?? $applicant->applicant_full_name;
        $nisn = $this->firstFilled($answers, ['nisn']) ?? $applicant->applicant_nisn;
        $birthPlace = $this->firstFilled($answers, ['tempat_lahir', 'birth_place']);
        $birthDateRaw = $this->firstFilled($answers, ['tanggal_lahir', 'birth_date']);
        $address = $this->firstFilled($answers, ['alamat', 'address', 'alamat_lengkap']);
        $parentFather = $this->firstFilled($answers, ['nama_ayah', 'ayah', 'nama_orang_tua_laki', 'nama_orang_tua_ayah']);
        $parentMother = $this->firstFilled($answers, ['nama_ibu', 'ibu', 'nama_orang_tua_perempuan', 'nama_orang_tua_ibu']);
        $parentWhatsapp = $this->firstFilled($answers, [
            'no_hp_ortu',
            'no_hp_orangtua',
            'no_hp_orang_tua',
            'wa_ortu',
            'wa_orangtua',
            'no_hp',
            'phone_parent',
        ]) ?? $applicant->applicant_phone_number;
        $studentWhatsapp = $this->firstFilled($answers, [
            'no_hp_siswa',
            'wa_siswa',
            'phone_student',
            'telepon_siswa',
            'hp_siswa',
        ]);
        $email = $this->firstFilled($answers, ['email', 'email_address', 'email_siswa']) ?? $applicant->applicant_email_address;

        $majorFirst = $this->firstFilled($answers, [
            'pilihan_jurusan_1',
            'jurusan_1',
            'jurusan_pilihan_1',
            'program_studi_1',
            'jurusan',
            'major',
        ]) ?? $applicant->chosen_major_name;
        $majorSecond = $this->firstFilled($answers, [
            'pilihan_jurusan_2',
            'jurusan_2',
            'jurusan_pilihan_2',
            'program_studi_2',
        ]);
        $majorThird = $this->firstFilled($answers, [
            'pilihan_jurusan_3',
            'jurusan_3',
            'jurusan_pilihan_3',
            'program_studi_3',
        ]);

        $previousSchool = $this->firstFilled($answers, ['asal_sekolah', 'sekolah_asal', 'asal_smp', 'asal_sdlb']);
        $examDate = $this->resolveExamDate($applicant, $answers);

        $signatureCity = setting('exam_card_signature_city', 'Sangatta');
        $signatureDayMonth = $examDate?->translatedFormat('d F') ?? now()->translatedFormat('d F');
        $signatureName = $name;
        $signatureImagePath = $this->resolveSignaturePath($applicant);

        return [
            'applicant' => $applicant,
            'registration_number' => $applicant->registration_number,
            'nisn' => $nisn,
            'name' => $name,
            'birth_place' => $birthPlace,
            'birth_date' => $this->normalizeDate($birthDateRaw),
            'address' => $address,
            'parent_father' => $parentFather,
            'parent_mother' => $parentMother,
            'whatsapp_parent' => $this->cleanPhone($parentWhatsapp),
            'whatsapp_student' => $this->cleanPhone($studentWhatsapp),
            'email' => $email,
            'major_first' => $majorFirst,
            'major_second' => $majorSecond,
            'major_third' => $majorThird,
            'previous_school' => $previousSchool,
            'exam_date' => $examDate,
            'signature_city' => $signatureCity,
            'signature_day_month' => $signatureDayMonth,
            'signature_name' => $signatureName,
            'photo_path' => $this->resolvePhotoPath($applicant),
            'signature_image_path' => $signatureImagePath,
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
