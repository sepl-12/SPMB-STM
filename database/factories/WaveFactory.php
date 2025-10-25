<?php

namespace Database\Factories;

use App\Models\Wave;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Wave>
 */
class WaveFactory extends Factory
{
    protected $model = Wave::class;

    public function definition(): array
    {
        $start = now()->subDays(1);

        return [
            'wave_name' => 'Gelombang ' . $this->faker->unique()->numerify('##'),
            'wave_code' => $this->faker->unique()->lexify('wave-????'),
            'start_datetime' => $start,
            'end_datetime' => $start->copy()->addWeeks(2),
            'quota_limit' => null,
            'registration_fee_amount' => $this->faker->numberBetween(0, 500000),
            'is_active' => true,
        ];
    }

    public function inactive(): self
    {
        return $this->state(function () {
            return [
                'is_active' => false,
                'start_datetime' => now()->subWeeks(2),
                'end_datetime' => now()->subWeek(),
            ];
        });
    }

    public function withQuota(int $quota): self
    {
        return $this->state(fn () => ['quota_limit' => $quota]);
    }
}
