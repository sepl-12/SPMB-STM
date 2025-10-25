<?php

namespace Tests\Concerns;

use App\Enum\FormFieldType;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormStep;
use App\Models\FormVersion;
use App\Models\Wave;
use App\Registration\Data\RegistrationWizard;
use App\Registration\Services\RegistrationWizardLoader;
use Illuminate\Support\Arr;

trait InteractsWithRegistrationForm
{
    /**
     * @param array<int, array{step?: array, fields?: array<int, array>}> $stepDefinitions
     */
    protected function createRegistrationStructure(array $stepDefinitions = []): Form
    {
        $form = Form::factory()->create();
        $version = FormVersion::factory()->for($form)->active()->create([
            'published_datetime' => now(),
        ]);

        if (empty($stepDefinitions)) {
            $stepDefinitions = [
                [
                    'step' => [
                        'step_key' => 'data_diri',
                        'step_title' => 'Data Diri',
                    ],
                    'fields' => [
                        [
                            'field_key' => 'nama_lengkap',
                            'field_label' => 'Nama Lengkap',
                            'field_type' => FormFieldType::TEXT->value,
                            'is_required' => true,
                        ],
                        [
                            'field_key' => 'email',
                            'field_label' => 'Email',
                            'field_type' => FormFieldType::EMAIL->value,
                            'is_required' => true,
                        ],
                    ],
                ],
            ];
        }

        foreach ($stepDefinitions as $index => $definition) {
            $stepAttributes = Arr::get($definition, 'step', []);
            $step = FormStep::factory()
                ->for($version)
                ->create(array_merge([
                    'step_key' => $stepAttributes['step_key'] ?? 'step_' . ($index + 1),
                    'step_title' => $stepAttributes['step_title'] ?? 'Step ' . ($index + 1),
                    'step_description' => $stepAttributes['step_description'] ?? null,
                    'step_order_number' => $stepAttributes['step_order_number'] ?? ($index + 1),
                    'is_visible_for_public' => $stepAttributes['is_visible_for_public'] ?? true,
                ], $stepAttributes));

            foreach (Arr::get($definition, 'fields', []) as $fieldIndex => $fieldAttributes) {
                FormField::factory()
                    ->for($version)
                    ->for($step)
                    ->create(array_merge([
                        'field_key' => $fieldAttributes['field_key'] ?? 'field_' . ($index + 1) . '_' . ($fieldIndex + 1),
                        'field_label' => $fieldAttributes['field_label'] ?? 'Field ' . ($index + 1) . '.' . ($fieldIndex + 1),
                        'field_type' => $fieldAttributes['field_type'] ?? FormFieldType::TEXT->value,
                        'is_required' => $fieldAttributes['is_required'] ?? false,
                        'field_options_json' => $fieldAttributes['field_options_json'] ?? null,
                    ], $fieldAttributes));
            }
        }

        return $form->fresh(['activeFormVersion.formSteps.formFields']);
    }

    protected function createActiveWave(array $attributes = []): Wave
    {
        return Wave::factory()->create(array_merge([
            'start_datetime' => now()->subDay(),
            'end_datetime' => now()->addWeek(),
            'is_active' => true,
        ], $attributes));
    }

    protected function loadRegistrationWizard(): RegistrationWizard
    {
        return app(RegistrationWizardLoader::class)->load();
    }
}
