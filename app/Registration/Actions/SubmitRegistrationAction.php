<?php

namespace App\Registration\Actions;

use App\Enum\FormFieldType;
use App\Models\Applicant;
use App\Models\Submission;
use App\Models\FormVersion;
use App\Models\Wave;
use App\Registration\Data\RegistrationWizard;
use App\Registration\Data\SubmitRegistrationResult;
use App\Registration\Events\ApplicantRegisteredEvent;
use App\Registration\Exceptions\RegistrationClosedException;
use App\Registration\Exceptions\RegistrationQuotaExceededException;
use App\Registration\Support\RegistrationAnswerMapper;
use App\Registration\Support\RegistrationEmailExtractor;
use App\Registration\Services\RegistrationNumberGenerator;
use App\Registration\Services\WaveQuotaGuard;
use App\Registration\Validators\RegistrationValidationContext;
use App\Registration\Validators\RegistrationValidator;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SubmitRegistrationAction
{
    public function __construct(
        private readonly RegistrationValidator $validator,
        private readonly WaveQuotaGuard $waveQuotaGuard,
        private readonly RegistrationNumberGenerator $registrationNumberGenerator,
        private readonly RegistrationAnswerMapper $answerMapper,
        private readonly RegistrationEmailExtractor $emailExtractor,
        private readonly Dispatcher $events
    ) {
    }

    /**
     * @param array<string, mixed> $registrationData
     * @throws RegistrationClosedException
     * @throws RegistrationQuotaExceededException
     */
    public function execute(RegistrationWizard $wizard, array $registrationData): SubmitRegistrationResult
    {
        $form = $wizard->form();
        $formVersion = $wizard->formVersion();

        $activeWave = Wave::where('is_active', true)
            ->where('start_datetime', '<=', now())
            ->where('end_datetime', '>=', now())
            ->first();

        if (!$activeWave) {
            throw new RegistrationClosedException();
        }

        $validationOutcome = $this->validateRegistrationData($formVersion, $registrationData);
        $validatedData = $validationOutcome['validatedData'];
        $allFields = $validationOutcome['allFields'];

        $result = DB::transaction(function () use (
            $form,
            $formVersion,
            $validatedData,
            $activeWave,
            $allFields
        ) {
            $this->waveQuotaGuard->assertAvailability($activeWave);

            $registrationNumber = $this->registrationNumberGenerator->generate();

            $emailValue = $this->emailExtractor->extract($validatedData, $allFields);

            $applicant = Applicant::create([
                'registration_number' => $registrationNumber,
                'applicant_full_name' => $validatedData['nama_lengkap'] ?? $validatedData['full_name'] ?? 'Nama Belum Diisi',
                'applicant_nisn' => $validatedData['nisn'] ?? '-',
                'applicant_phone_number' => $validatedData['no_hp'] ?? $validatedData['phone'] ?? '-',
                'applicant_email_address' => $emailValue,
                'chosen_major_name' => $validatedData['jurusan'] ?? $validatedData['major'] ?? 'Belum Dipilih',
                'wave_id' => $activeWave->id,
                'registered_datetime' => now(),
            ]);

            $submission = Submission::create([
                'applicant_id' => $applicant->id,
                'form_id' => $form->id,
                'form_version_id' => $formVersion->id,
                'answers_json' => $validatedData,
                'submitted_datetime' => now(),
            ]);

            $allFieldsForAnswers = $formVersion->formFields()->get();
            $this->answerMapper->persistAnswers($submission, $allFieldsForAnswers, $validatedData);

            return new SubmitRegistrationResult($registrationNumber, $applicant, $submission);
        });

        $this->events->dispatch(new ApplicantRegisteredEvent($result->applicant));

        return $result;
    }

    /**
     * @param array<string, mixed> $registrationData
     * @return array{validatedData: array<string, mixed>, allFields: \Illuminate\Support\Collection}
     * @throws ValidationException
     */
    protected function validateRegistrationData(FormVersion $formVersion, array $registrationData): array
    {
        $allFields = $formVersion->formFields()->where('is_archived', false)->get();
        $context = new RegistrationValidationContext(
            $registrationData,
            $registrationData,
            'submit',
            0,
            RegistrationValidationContext::SCENARIO_SUBMIT
        );

        $validatedData = $this->validator->validate($allFields, $context, $registrationData);
        $validatedData = array_merge(
            collect($registrationData)->only($allFields->pluck('field_key')->all())->toArray(),
            $validatedData
        );

        $fileFieldErrors = [];
        $fileFields = $allFields->filter(fn($field) => FormFieldType::tryFrom($field->field_type)?->isFileUpload());
        $signatureFields = $allFields->filter(fn($field) => FormFieldType::tryFrom($field->field_type)?->isSignature());

        foreach ($fileFields as $field) {
            $fieldKey = $field->field_key;
            $value = $validatedData[$fieldKey] ?? null;

            if ($field->is_required && empty($value)) {
                $fileFieldErrors[$fieldKey][] = "{$field->field_label} wajib diunggah.";
                continue;
            }

            if ($value && !Storage::disk('public')->exists((string) $value)) {
                $fileFieldErrors[$fieldKey][] = "File untuk {$field->field_label} tidak ditemukan. Silakan unggah ulang.";
            }
        }

        foreach ($signatureFields as $field) {
            $fieldKey = $field->field_key;
            $value = $validatedData[$fieldKey] ?? null;

            if ($field->is_required && empty($value)) {
                $fileFieldErrors[$fieldKey][] = "Tanda tangan untuk {$field->field_label} wajib diisi.";
                continue;
            }

            if (!$value) {
                continue;
            }

            if (!Str::startsWith($value, 'registration-signatures/')) {
                $fileFieldErrors[$fieldKey][] = "Format tanda tangan untuk {$field->field_label} tidak dikenal. Mohon tandatangani ulang.";
                continue;
            }

            if (!Storage::disk('public')->exists((string) $value)) {
                $fileFieldErrors[$fieldKey][] = "Tanda tangan untuk {$field->field_label} tidak ditemukan. Mohon tandatangani ulang.";
            }
        }

        if (!empty($fileFieldErrors)) {
            throw ValidationException::withMessages($fileFieldErrors);
        }

        return [
            'validatedData' => $validatedData,
            'allFields' => $allFields,
        ];
    }
}
