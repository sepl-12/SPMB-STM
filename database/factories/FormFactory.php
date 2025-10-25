<?php

namespace Database\Factories;

use App\Models\Form;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Form>
 */
class FormFactory extends Factory
{
    protected $model = Form::class;

    public function definition(): array
    {
        return [
            'form_name' => $this->faker->unique()->sentence(3),
            'form_code' => $this->faker->unique()->slug(),
        ];
    }
}
