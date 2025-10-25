<?php

namespace Database\Factories;

use App\Models\Form;
use App\Models\FormVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FormVersion>
 */
class FormVersionFactory extends Factory
{
    protected $model = FormVersion::class;

    public function definition(): array
    {
        return [
            'form_id' => Form::factory(),
            'version_number' => 1,
            'is_active' => false,
            'published_datetime' => null,
        ];
    }

    public function active(): self
    {
        return $this->state(function () {
            return [
                'is_active' => true,
                'published_datetime' => now(),
            ];
        });
    }
}
