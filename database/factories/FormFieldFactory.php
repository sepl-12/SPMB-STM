<?php

namespace Database\Factories;

use App\Enum\FormFieldType;
use App\Models\FormField;
use App\Models\FormStep;
use App\Models\FormVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FormField>
 */
class FormFieldFactory extends Factory
{
    protected $model = FormField::class;

    public function definition(): array
    {
        return [
            'form_version_id' => FormVersion::factory()->active(),
            'form_step_id' => FormStep::factory(),
            'field_key' => $this->faker->unique()->slug(2),
            'field_label' => $this->faker->sentence(2),
            'field_type' => FormFieldType::TEXT->value,
            'field_placeholder_text' => $this->faker->optional()->sentence(4),
            'field_help_text' => $this->faker->optional()->sentence(6),
            'field_options_json' => null,
            'field_order_number' => null,
            'is_required' => false,
            'is_filterable' => false,
            'is_exportable' => true,
            'is_archived' => false,
            'is_system_field' => false,
        ];
    }

    public function required(): self
    {
        return $this->state(fn () => ['is_required' => true]);
    }

    public function select(array $options = null): self
    {
        return $this->state(function () use ($options) {
            return [
                'field_type' => FormFieldType::SELECT->value,
                'field_options_json' => $options ?? [
                    ['label' => 'Option A', 'value' => 'a'],
                    ['label' => 'Option B', 'value' => 'b'],
                ],
            ];
        });
    }

    public function radio(array $options = null): self
    {
        return $this->state(function () use ($options) {
            return [
                'field_type' => FormFieldType::RADIO->value,
                'field_options_json' => $options ?? [
                    ['label' => 'Option A', 'value' => 'a'],
                    ['label' => 'Option B', 'value' => 'b'],
                ],
            ];
        });
    }

    public function multiSelect(array $options = null): self
    {
        return $this->state(function () use ($options) {
            return [
                'field_type' => FormFieldType::MULTI_SELECT->value,
                'field_options_json' => $options ?? [
                    ['label' => 'Option A', 'value' => 'a'],
                    ['label' => 'Option B', 'value' => 'b'],
                ],
            ];
        });
    }

    public function fileField(): self
    {
        return $this->state(fn () => ['field_type' => FormFieldType::FILE->value]);
    }

    public function imageField(): self
    {
        return $this->state(fn () => ['field_type' => FormFieldType::IMAGE->value]);
    }
}
