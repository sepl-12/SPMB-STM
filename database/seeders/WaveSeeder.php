<?php

namespace Database\Seeders;

use App\Models\Wave;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WaveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $waves = [
            [
                'wave_name' => 'Gelombang 1',
                'wave_code' => 'G1-2025',
                'start_datetime' => '2025-01-01 00:00:00',
                'end_datetime' => '2025-03-31 23:59:59',
                'quota_limit' => 200,
                'registration_fee_amount' => 300000.00,
                'is_active' => false,
            ],
            [
                'wave_name' => 'Gelombang 2',
                'wave_code' => 'G2-2025',
                'start_datetime' => '2025-04-01 00:00:00',
                'end_datetime' => '2025-06-30 23:59:59',
                'quota_limit' => 150,
                'registration_fee_amount' => 350000.00,
                'is_active' => false,
            ],
            [
                'wave_name' => 'Gelombang 3',
                'wave_code' => 'G3-2025',
                'start_datetime' => '2025-07-01 00:00:00',
                'end_datetime' => '2025-09-30 23:59:59',
                'quota_limit' => 100,
                'registration_fee_amount' => 400000.00,
                'is_active' => true,
            ],
            [
                'wave_name' => 'Gelombang 4',
                'wave_code' => 'G4-2025',
                'start_datetime' => '2025-10-01 00:00:00',
                'end_datetime' => '2025-12-31 23:59:59',
                'quota_limit' => 50,
                'registration_fee_amount' => 450000.00,
                'is_active' => true,
            ],
        ];

        foreach ($waves as $wave) {
            Wave::create($wave);
        }
    }
}
