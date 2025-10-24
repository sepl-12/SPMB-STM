<?php

namespace App\Registration\Actions;

use App\Enum\FormFieldType;
use App\Registration\Data\RegistrationWizard;
use App\Registration\Data\SaveStepResult;
use App\Registration\Exceptions\RegistrationStepValidationException;
use App\Services\FormFieldValidationService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SaveRegistrationStepAction
{
    public function __construct(private readonly FormFieldValidationService $validationService) {}

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

            $fieldsForValidation = $currentStep->formFields->filter(function ($field) use ($request, $registrationData) {
                $fieldKey = $field->field_key;
                $fieldType = FormFieldType::tryFrom($field->field_type);

                if ($fieldType?->isFileUpload()) {
                    if ($request->hasFile($fieldKey)) {
                        return true;
                    }

                    return empty($registrationData[$fieldKey]);
                }

                return true;
            });

            if (in_array($action, ['next', 'submit'], true)) {
                try {
                    if ($fieldsForValidation->isNotEmpty()) {
                        $dataForValidation = collect($stepData)
                            ->only($fieldsForValidation->pluck('field_key')->all())
                            ->toArray();

                        $this->validationService->validateFormData($dataForValidation, $fieldsForValidation);
                    }
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
                $stepData[$fieldKey] = $storeUploadedFile($fieldKey, $uploadedFile);
            }

            $registrationData = array_merge($registrationData, $stepData);
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
}
