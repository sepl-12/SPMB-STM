<?php

namespace Database\Factories;

use App\Models\Applicant;
use App\Models\Wave;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Applicant>
 */
class ApplicantFactory extends Factory
{
    protected $model = Applicant::class;

    public function definition(): array
    {
        return [
            'registration_number' => $this->faker->unique()->regexify('PPDB-2024-[0-9]{5}'),
            'applicant_full_name' => $this->faker->name(),
            'applicant_nisn' => $this->faker->numerify('##########'),
            'applicant_phone_number' => $this->faker->phoneNumber(),
            'applicant_email_address' => $this->faker->unique()->safeEmail(),
            'chosen_major_name' => $this->faker->randomElement(['TKJ', 'AKL', 'DKV']),
            'wave_id' => Wave::factory(),
            'registered_datetime' => now(),
        ];
    }
}
