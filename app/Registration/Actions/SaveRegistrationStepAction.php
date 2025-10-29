<?php

namespace App\Registration\Actions;

use App\Enum\FormFieldType;
use App\Registration\Data\RegistrationWizard;
use App\Registration\Data\SaveStepResult;
use App\Registration\Exceptions\RegistrationStepValidationException;
use App\Registration\Validators\RegistrationValidationContext;
use App\Registration\Validators\RegistrationValidator;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SaveRegistrationStepAction
{
    public function __construct(private readonly RegistrationValidator $validator)
    {
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
                $storedPath = $uploadedFile->store('registration-files', 'public');

                $existingPath = $registrationData[$fieldKey] ?? null;
                if ($existingPath && $existingPath !== $storedPath && Storage::disk('public')->exists($existingPath)) {
                    Storage::disk('public')->delete($existingPath);
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
                    if ($existingValue && Storage::disk('public')->exists($existingValue)) {
                        Storage::disk('public')->delete($existingValue);
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
        Storage::disk('public')->put($fileName, $decoded);

        if ($existingPath && Storage::disk('public')->exists($existingPath)) {
            Storage::disk('public')->delete($existingPath);
        }

        return $fileName;
    }
}
