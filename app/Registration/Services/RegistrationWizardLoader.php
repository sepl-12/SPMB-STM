<?php

namespace App\Registration\Services;

use App\Models\Form;
use App\Registration\Data\RegistrationWizard;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RegistrationWizardLoader
{
    public function load(): RegistrationWizard
    {
        $form = Form::with([
            'activeFormVersion' => function ($query) {
                $query->with(['formSteps' => function ($stepQuery) {
                    $stepQuery
                        ->where('is_visible_for_public', true)
                        ->orderBy('step_order_number')
                        ->with(['formFields' => function ($fieldQuery) {
                            $fieldQuery
                                ->where('is_archived', false)
                                ->orderBy('field_order_number');
                        }]);
                }]);
            },
        ])
            ->whereHas('activeFormVersion')
            ->orderByDesc('id')
            ->first();

        if (!$form) {
            throw new ModelNotFoundException('Registration form is not configured.');
        }

        $formVersion = $form->activeFormVersion;
        if (!$formVersion) {
            throw new ModelNotFoundException('Active registration form version is not available.');
        }

        $steps = $formVersion->formSteps->values();

        return new RegistrationWizard($form, $formVersion, $steps);
    }
}
