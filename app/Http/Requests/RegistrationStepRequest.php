<?php

namespace App\Http\Requests;

use App\Models\Form;
use App\Services\FormFieldValidationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class RegistrationStepRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // Dynamic rules will be handled in validateResolved method
        return [];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateCurrentStep($validator);
        });
    }

    /**
     * Validate current step data using FormFieldValidationService
     */
    protected function validateCurrentStep($validator): void
    {
        $currentStepIndex = $this->input('current_step', 0);
        $action = $this->input('action', 'next');

        // Skip validation if going back
        if ($action === 'previous') {
            return;
        }

        try {
            // Get form data
            $form = Form::with(['activeFormVersion.formSteps.formFields'])->first();

            if (!$form || !$form->activeFormVersion) {
                $validator->errors()->add('form', 'Form tidak ditemukan atau tidak aktif.');
                return;
            }

            $formVersion = $form->activeFormVersion;
            $steps = $formVersion->formSteps()
                ->where('is_visible_for_public', true)
                ->orderBy('step_order_number')
                ->get();

            $currentStep = $steps[$currentStepIndex] ?? null;

            if (!$currentStep) {
                $validator->errors()->add('step', 'Langkah form tidak valid.');
                return;
            }

            // Collect step data
            $stepData = [];
            foreach ($currentStep->formFields as $field) {
                $fieldKey = $field->field_key;

                // Handle file uploads
                if (in_array($field->field_type, ['file', 'image'])) {
                    if ($this->hasFile($fieldKey)) {
                        $stepData[$fieldKey] = $this->file($fieldKey);
                    }
                }
                // Handle multi_select
                elseif ($field->field_type === 'multi_select') {
                    $stepData[$fieldKey] = $this->input($fieldKey, []);
                }
                // Handle other fields
                else {
                    if ($this->has($fieldKey)) {
                        $stepData[$fieldKey] = $this->input($fieldKey);
                    }
                }
            }

            // Use FormFieldValidationService for validation
            $validationService = app(FormFieldValidationService::class);
            $validationService->validateFormData($stepData, $currentStep->formFields);
        } catch (ValidationException $e) {
            // Merge validation errors
            foreach ($e->validator->errors()->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $validator->errors()->add($field, $message);
                }
            }
        } catch (\Exception $e) {
            $validator->errors()->add('general', 'Terjadi kesalahan saat validasi: ' . $e->getMessage());
        }
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'form' => 'Form tidak ditemukan atau tidak aktif.',
            'step' => 'Langkah form tidak valid.',
            'general' => 'Terjadi kesalahan saat validasi data.',
        ];
    }
}
