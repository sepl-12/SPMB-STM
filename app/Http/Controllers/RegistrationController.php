<?php

namespace App\Http\Controllers;

use App\Enum\FormFieldType;
use App\Mail\ApplicantRegistered;
use App\Models\Applicant;
use App\Models\Form;
use App\Models\Submission;
use App\Models\SubmissionAnswer;
use App\Models\SubmissionFile;
use App\Models\Wave;
use App\Services\FormFieldValidationService;
use App\Services\GmailMailableSender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class RegistrationController extends Controller
{
    /**
     * Show registration form
     */
    public function index()
    {
        // Check if there's an active wave
        $activeWave = Wave::where('is_active', true)
            ->where('start_datetime', '<=', now())
            ->where('end_datetime', '>=', now())
            ->first();

        if (!$activeWave) {
            return view('registration-closed');
        }

        return view('registration');
    }

    /**
     * Save current step data
     */
    public function saveStep(Request $request)
    {
        $currentStepIndex = $request->input('current_step', 0);
        $action = $request->input('action', 'next');

        // Get form data
        $form = Form::with(['activeFormVersion.formSteps.formFields'])->first();
        $formVersion = $form->activeFormVersion;
        $steps = $formVersion->formSteps()->where('is_visible_for_public', true)->orderBy('step_order_number')->get();
        $currentStep = $steps[$currentStepIndex] ?? null;

        // Get all form data from session
        $registrationData = session('registration_data', []);

        // Save current step data
        if ($currentStep) {
            $stepData = [];

            foreach ($currentStep->formFields as $field) {
                $fieldKey = $field->field_key;

                $fieldType = FormFieldType::tryFrom($field->field_type);

                // Handle file uploads
                if ($fieldType?->isFileUpload()) {
                    if ($request->hasFile($fieldKey)) {
                        $file = $request->file($fieldKey);
                        $path = $file->store('registration-files', 'public');
                        $stepData[$fieldKey] = $path;
                    }
                }
                // Handle multiselect
                elseif ($fieldType === FormFieldType::MULTI_SELECT) {
                    $stepData[$fieldKey] = $request->input($fieldKey, []);
                }
                // Handle other fields
                else {
                    if ($request->has($fieldKey)) {
                        $stepData[$fieldKey] = $request->input($fieldKey);
                    }
                }
            }

            // Validate current step data if moving forward or submitting
            if (in_array($action, ['next', 'submit'])) {
                try {
                    $validationService = app(FormFieldValidationService::class);
                    $validationService->validateFormData($stepData, $currentStep->formFields);
                } catch (ValidationException $e) {
                    return redirect()->route('registration.index')
                        ->withErrors($e->validator)
                        ->withInput()
                        ->with('validation_step', $currentStepIndex);
                }
            }

            // Merge step data with existing registration data
            $registrationData = array_merge($registrationData, $stepData);
        }

        // Save to session
        session(['registration_data' => $registrationData]);

        // Determine next step
        if ($action === 'previous') {
            $nextStepIndex = max(0, $currentStepIndex - 1);
        } elseif ($action === 'next') {
            $nextStepIndex = min($steps->count() - 1, $currentStepIndex + 1);
        } elseif ($action === 'submit') {
            return $this->submitRegistration($request);
        } else {
            $nextStepIndex = $currentStepIndex;
        }

        session(['current_step' => $nextStepIndex]);

        return redirect()->route('registration.index');
    }

    /**
     * Jump to specific step
     */
    public function jumpToStep(Request $request)
    {
        $jumpToStep = $request->input('jump_to_step', 0);

        // Get form to validate step exists
        $form = Form::with(['activeFormVersion.formSteps'])->first();
        $formVersion = $form->activeFormVersion;
        $steps = $formVersion->formSteps()->where('is_visible_for_public', true)->orderBy('step_order_number')->get();

        $jumpToStep = max(0, min($steps->count() - 1, $jumpToStep));

        session(['current_step' => $jumpToStep]);

        return redirect()->route('registration.index');
    }

    /**
     * Submit complete registration
     */
    protected function submitRegistration(Request $request)
    {
        $form = Form::with(['activeFormVersion.formSteps.formFields'])->first();
        $formVersion = $form->activeFormVersion;
        $registrationData = session('registration_data', []);

        // Get active wave
        $activeWave = Wave::where('is_active', true)
            ->where('start_datetime', '<=', now())
            ->where('end_datetime', '>=', now())
            ->first();

        if (!$activeWave) {
            return redirect()->route('registration.index')->with('error', 'Gelombang pendaftaran tidak aktif.');
        }

        // Validate all form data before submission
        try {
            $allFields = $formVersion->formFields()->where('is_archived', false)->get();
            $validationService = app(FormFieldValidationService::class);
            $validatedData = $validationService->validateFormData($registrationData, $allFields);

            // Use validated data for submission
            $registrationData = $validatedData;
        } catch (ValidationException $e) {
            return redirect()->route('registration.index')
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Terdapat kesalahan pada data yang diisi. Silakan periksa kembali.');
        }

        DB::beginTransaction();
        try {
            // Check quota with database lock to prevent race condition
            if (!$this->checkQuotaAvailability($activeWave)) {
                DB::rollBack();
                return redirect()
                    ->route('registration.index')
                    ->with('error', 'Kuota pendaftaran untuk gelombang ini sudah penuh.');
            }

            // Create applicant record
            $registrationNumber = $this->generateRegistrationNumber();

            // Extract email from form fields (prioritize email field type)
            $emailValue = $this->extractEmailFromFormData($registrationData, $allFields);

            $applicant = Applicant::create([
                'registration_number' => $registrationNumber,
                'applicant_full_name' => $registrationData['nama_lengkap'] ?? $registrationData['full_name'] ?? 'Nama Belum Diisi',
                'applicant_nisn' => $registrationData['nisn'] ?? '-',
                'applicant_phone_number' => $registrationData['no_hp'] ?? $registrationData['phone'] ?? '-',
                'applicant_email_address' => $emailValue,
                'chosen_major_name' => $registrationData['jurusan'] ?? $registrationData['major'] ?? 'Belum Dipilih',
                'wave_id' => $activeWave->id,
                // Note: payment_status is now computed from Payment relation
                'registered_datetime' => now(),
            ]);

            // Create submission
            $submission = Submission::create([
                'applicant_id' => $applicant->id,
                'form_id' => $form->id,
                'form_version_id' => $formVersion->id,
                'answers_json' => $registrationData,
                'submitted_datetime' => now(),
            ]);

            // Save individual answers and files
            $allFields = $formVersion->formFields()->get();

            foreach ($allFields as $field) {
                $fieldKey = $field->field_key;
                $fieldValue = $registrationData[$fieldKey] ?? null;

                if ($fieldValue === null) {
                    continue;
                }

                $fieldType = FormFieldType::tryFrom($field->field_type);

                // Handle file uploads
                if ($fieldType?->isFileUpload() && is_string($fieldValue)) {
                    $disk = Storage::disk('public');
                    $mimeType = 'application/octet-stream';
                    if ($disk->exists($fieldValue)) {
                        $extension = pathinfo($fieldValue, PATHINFO_EXTENSION);
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
                        'stored_disk_name' => 'public',
                        'stored_file_path' => $fieldValue,
                        'original_file_name' => basename($fieldValue),
                        'mime_type_name' => $mimeType,
                        'file_size_bytes' => $disk->exists($fieldValue) ? $disk->size($fieldValue) : 0,
                        'uploaded_datetime' => now(),
                    ]);
                } else {
                    // Create submission answer
                    $answerData = [
                        'submission_id' => $submission->id,
                        'form_field_id' => $field->id,
                        'field_key' => $fieldKey,
                    ];

                    // Map value to appropriate column based on field type
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
                            // Store email in text field with additional validation marker
                            $answerData['answer_value_text'] = strtolower(trim($fieldValue));
                            break;
                        default:
                            $answerData['answer_value_text'] = $fieldValue;
                    }

                    SubmissionAnswer::create($answerData);
                }
            }

            DB::commit();

            // Send email notification
            try {
                if ($applicant->applicant_email_address && $applicant->applicant_email_address !== '-') {
                    app(GmailMailableSender::class)->send($applicant->applicant_email_address, new ApplicantRegistered($applicant));
                }
            } catch (\Exception $e) {
                // Log error but don't stop the flow
                \Log::error('Failed to send registration email: ' . $e->getMessage());
            }

            // Clear session data
            session()->forget(['registration_data', 'current_step']);

            return redirect()->route('registration.success', ['registration_number' => $registrationNumber]);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('registration.index')
                ->with('error', 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.');
        }
    }

    /**
     * Show success page
     */
    public function success($registration_number)
    {
        $applicant = Applicant::where('registration_number', $registration_number)->firstOrFail();

        return view('registration-success', compact('applicant'));
    }

    /**
     * Check if wave quota is available with database lock
     * 
     * @param Wave $wave
     * @return bool
     * @throws \Exception
     */
    protected function checkQuotaAvailability(Wave $wave): bool
    {
        if (!$wave->quota_limit) {
            return true; // No quota limit set
        }

        // Lock the wave record to prevent concurrent quota checks
        $lockedWave = Wave::where('id', $wave->id)->lockForUpdate()->first();

        if (!$lockedWave) {
            throw new \Exception('Wave not found or locked');
        }

        // Count current applicants with lock to ensure accuracy
        $currentCount = Applicant::where('wave_id', $wave->id)
            ->lockForUpdate()
            ->count();

        return $currentCount < $wave->quota_limit;
    }

    /**
     * Generate unique registration number with database lock to prevent duplicates
     */
    protected function generateRegistrationNumber(): string
    {
        $year = now()->year;
        $prefix = 'PPDB-' . $year . '-';

        // Use database lock to prevent race condition on registration number generation
        $lastNumber = Applicant::where('registration_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderBy('id', 'desc')
            ->value('registration_number');

        if ($lastNumber) {
            $lastNum = (int) substr($lastNumber, -5);
            $newNum = $lastNum + 1;
        } else {
            $newNum = 1;
        }

        $registrationNumber = $prefix . str_pad($newNum, 5, '0', STR_PAD_LEFT);

        // Double check uniqueness (extra safety)
        $attempts = 0;
        while (Applicant::where('registration_number', $registrationNumber)->exists() && $attempts < 10) {
            $newNum++;
            $registrationNumber = $prefix . str_pad($newNum, 5, '0', STR_PAD_LEFT);
            $attempts++;
        }

        if ($attempts >= 10) {
            throw new \Exception('Unable to generate unique registration number after 10 attempts');
        }

        return $registrationNumber;
    }

    /**
     * Extract email value from form data, prioritizing email field type
     *
     * @param array $registrationData
     * @param \Illuminate\Database\Eloquent\Collection $fields
     * @return string
     */
    protected function extractEmailFromFormData(array $registrationData, $fields): string
    {
        // First, look for fields with email type
        $emailFields = $fields->where('field_type', FormFieldType::EMAIL->value);

        foreach ($emailFields as $field) {
            $value = $registrationData[$field->field_key] ?? null;
            if ($value && filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return strtolower(trim($value));
            }
        }

        // Fallback to common email field keys
        $commonEmailKeys = ['email', 'email_address', 'applicant_email', 'user_email'];

        foreach ($commonEmailKeys as $key) {
            $value = $registrationData[$key] ?? null;
            if ($value && filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return strtolower(trim($value));
            }
        }

        return '-';
    }
}
