<?php

namespace App\Registration\Actions;

use App\Enum\FormFieldType;
use App\Registration\Data\RegistrationWizard;
use App\Registration\Data\SaveStepResult;
use App\Registration\Exceptions\RegistrationStepValidationException;
use App\Registration\Validators\LinkedFieldValidator;
use App\Registration\Validators\RegistrationValidationContext;
use App\Registration\Validators\RegistrationValidator;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SaveRegistrationStepAction
{
    public function __construct(
        private readonly RegistrationValidator $validator,
        private readonly LinkedFieldValidator $linkedFieldValidator
    ) {
    }

    /**
     * @param array<string, mixed> $existingData
     */
    public function execute(
        Request $request,
        RegistrationWizard $wizard,
        array $existingData,
        int $requestedStepIndex,
        string $action
    ): SaveStepResult {
        $steps = $wizard->steps();
        $stepCount = $steps->count();
        $normalizedIndex = $this->normalizeStepIndex($requestedStepIndex, $stepCount);
        $currentStep = $wizard->stepAt($normalizedIndex);

        $registrationData = $existingData;

        if ($currentStep) {
            $stepData = [];
            /** @var array<string, UploadedFile> $filesToStore */
            $filesToStore = [];

            foreach ($currentStep->formFields as $field) {
                $fieldKey = $field->field_key;
                $fieldType = FormFieldType::tryFrom($field->field_type);

                if ($fieldType?->isFileUpload()) {
                    if ($request->hasFile($fieldKey)) {
                        $uploadedFile = $request->file($fieldKey);
                        if ($uploadedFile instanceof UploadedFile) {
                            $stepData[$fieldKey] = $uploadedFile;
                            $filesToStore[$fieldKey] = $uploadedFile;
                        }
                    }
                    continue;
                }

                if ($fieldType?->isSignature()) {
                    $signatureValue = $request->input($fieldKey);
                    if ($signatureValue !== null) {
                        $stepData[$fieldKey] = $signatureValue;
                    } elseif (array_key_exists($fieldKey, $registrationData)) {
                        $stepData[$fieldKey] = null;
                    }

                    continue;
                }

                if ($fieldType === FormFieldType::MULTI_SELECT) {
                    $stepData[$fieldKey] = $request->input($fieldKey, []);
                    continue;
                }

                if ($request->has($fieldKey)) {
                    $stepData[$fieldKey] = $request->input($fieldKey);
                }
            }

            $storeUploadedFile = function (string $fieldKey, UploadedFile $uploadedFile) use (&$registrationData): string {
                // Store the new file first in PRIVATE disk for security
                $storedPath = $uploadedFile->store('registration-files', 'private');

                // Verify the upload was successful
                if (!$storedPath) {
                    throw new \RuntimeException("Failed to store uploaded file for field: {$fieldKey}");
                }

                // Verify the file actually exists after upload
                if (!Storage::disk('private')->exists($storedPath)) {
                    throw new \RuntimeException("File was not properly stored for field: {$fieldKey}");
                }

                // Only delete old file AFTER new file is successfully stored
                $existingPath = $registrationData[$fieldKey] ?? null;
                if ($existingPath && $existingPath !== $storedPath && Storage::disk('private')->exists($existingPath)) {
                    try {
                        Storage::disk('private')->delete($existingPath);
                    } catch (\Exception $e) {
                        // Log the error but don't fail the upload if old file deletion fails
                        // The new file is already stored successfully
                        \Log::warning("Failed to delete old file: {$existingPath}", ['error' => $e->getMessage()]);
                    }
                }

                $registrationData[$fieldKey] = $storedPath;

                return $storedPath;
            };

            $validatedStepData = $stepData;
            $context = new RegistrationValidationContext(
                $registrationData,
                $request->all(),
                $action,
                $normalizedIndex,
                RegistrationValidationContext::SCENARIO_STEP
            );

            if ($context->shouldValidateFields()) {
                try {
                    $validatedStepData = $this->validator->validate(
                        $currentStep->formFields,
                        $context,
                        $stepData
                    );

                    // Validate linked fields (no duplicate selections in same group)
                    $this->linkedFieldValidator->validateLinkedFields(
                        $currentStep->formFields,
                        $stepData
                    );
                } catch (ValidationException $e) {
                    foreach ($filesToStore as $fieldKey => $uploadedFile) {
                        $fieldErrors = $e->validator->errors()->get($fieldKey);
                        if (!empty($fieldErrors)) {
                            continue;
                        }

                        $registrationData[$fieldKey] = $storeUploadedFile($fieldKey, $uploadedFile);
                    }

                    throw new RegistrationStepValidationException($e, $registrationData, $normalizedIndex);
                }
            }

            foreach ($filesToStore as $fieldKey => $uploadedFile) {
                $validatedStepData[$fieldKey] = $storeUploadedFile($fieldKey, $uploadedFile);
            }

            foreach ($currentStep->formFields as $field) {
                $fieldKey = $field->field_key;
                $fieldType = FormFieldType::tryFrom($field->field_type);

                if (!$fieldType?->isSignature()) {
                    continue;
                }

                $submittedValue = $stepData[$fieldKey] ?? null;
                $validatedValue = $validatedStepData[$fieldKey] ?? null;
                $existingValue = $registrationData[$fieldKey] ?? null;

                if ($submittedValue === null || $submittedValue === '') {
                    if ($existingValue && Storage::disk('private')->exists($existingValue)) {
                        try {
                            Storage::disk('private')->delete($existingValue);
                        } catch (\Exception $e) {
                            // Log the error but continue - the field value will be cleared anyway
                            \Log::warning("Failed to delete signature file: {$existingValue}", ['error' => $e->getMessage()]);
                        }
                    }

                    unset($validatedStepData[$fieldKey]);
                    unset($registrationData[$fieldKey]);

                    continue;
                }

                if (is_string($validatedValue) && Str::startsWith($validatedValue, 'data:image')) {
                    $storedPath = $this->storeSignatureData($fieldKey, $validatedValue, $existingValue);
                    $validatedStepData[$fieldKey] = $storedPath;
                    $registrationData[$fieldKey] = $storedPath;

                    continue;
                }

                if (is_string($validatedValue) && Str::startsWith($validatedValue, 'registration-signatures/')) {
                    $registrationData[$fieldKey] = $validatedValue;
                }
            }

            $registrationData = array_merge($registrationData, $validatedStepData);
        }

        if ($action === 'submit') {
            return new SaveStepResult($normalizedIndex, $registrationData, null, true);
        }

        if ($action === 'previous') {
            $nextStepIndex = max(0, $normalizedIndex - 1);
        } elseif ($action === 'next') {
            $nextStepIndex = $stepCount > 0 ? min($stepCount - 1, $normalizedIndex + 1) : 0;
        } else {
            $nextStepIndex = $normalizedIndex;
        }

        return new SaveStepResult($normalizedIndex, $registrationData, $nextStepIndex, false);
    }

    protected function normalizeStepIndex(int $index, int $stepCount): int
    {
        if ($stepCount <= 0) {
            return 0;
        }

        return max(0, min($stepCount - 1, $index));
    }

    protected function storeSignatureData(string $fieldKey, string $dataUrl, ?string $existingPath): string
    {
        if (!preg_match('/^data:image\/(png|jpe?g);base64,/', $dataUrl, $matches)) {
            throw new \RuntimeException("Invalid signature data for {$fieldKey}");
        }

        $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
        $decoded = base64_decode(substr($dataUrl, strpos($dataUrl, ',') + 1));

        if ($decoded === false) {
            throw new \RuntimeException("Failed to decode signature for {$fieldKey}");
        }

        $fileName = sprintf('registration-signatures/%s_%s.%s', $fieldKey, uniqid('', true), $extension);

        // Store the new signature file in PRIVATE disk for security
        $putResult = Storage::disk('private')->put($fileName, $decoded);

        // Verify the storage was successful
        if (!$putResult) {
            throw new \RuntimeException("Failed to store signature file for field: {$fieldKey}");
        }

        // Verify the file actually exists after storage
        if (!Storage::disk('private')->exists($fileName)) {
            throw new \RuntimeException("Signature file was not properly stored for field: {$fieldKey}");
        }

        // Only delete old signature AFTER new signature is successfully stored
        if ($existingPath && Storage::disk('private')->exists($existingPath)) {
            try {
                Storage::disk('private')->delete($existingPath);
            } catch (\Exception $e) {
                // Log the error but don't fail the upload if old file deletion fails
                // The new signature is already stored successfully
                \Log::warning("Failed to delete old signature: {$existingPath}", ['error' => $e->getMessage()]);
            }
        }

        return $fileName;
    }
}
