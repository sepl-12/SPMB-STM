<?php

namespace Database\Factories;

use App\Models\FormStep;
use App\Models\FormVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FormStep>
 */
class FormStepFactory extends Factory
{
    protected $model = FormStep::class;

    public function definition(): array
    {
        return [
            'form_version_id' => FormVersion::factory()->active(),
            'step_key' => $this->faker->unique()->slug(2),
            'step_title' => $this->faker->sentence(3),
            'step_description' => $this->faker->optional()->sentence(6),
            'step_order_number' => null,
            'is_visible_for_public' => true,
        ];
    }

    public function hidden(): self
    {
        return $this->state(fn () => ['is_visible_for_public' => false]);
    }
}
