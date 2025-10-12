<?php

namespace Database\Seeders;

use App\Models\Applicant;
use App\Models\Wave;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ApplicantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $waves = Wave::all();
        
        if ($waves->isEmpty()) {
            $this->command->warn('Tidak ada data gelombang. Jalankan WaveSeeder terlebih dahulu.');
            return;
        }

        $majors = [
            'Teknik Komputer dan Jaringan',
            'Teknik Kendaraan Ringan Otomotif',
            'Teknik dan Bisnis Sepeda Motor',
            'Teknik Pemesinan',
            'Teknik Pengelasan',
        ];

        $paymentStatuses = ['pending', 'paid', 'verified'];

        $firstNames = [
            'Ahmad', 'Budi', 'Citra', 'Dewi', 'Eko', 'Fajar', 'Gita', 'Hadi',
            'Indra', 'Joko', 'Kartika', 'Lestari', 'Made', 'Nana', 'Omar', 'Putri',
            'Qori', 'Rina', 'Sari', 'Tono', 'Umar', 'Vina', 'Wati', 'Yoga',
            'Zahra', 'Andi', 'Bayu', 'Candra', 'Desi', 'Edi'
        ];

        $lastNames = [
            'Pratama', 'Saputra', 'Kusuma', 'Wijaya', 'Permana', 'Santoso',
            'Wibowo', 'Nugraha', 'Setiawan', 'Kurniawan', 'Hidayat', 'Ramadhan',
            'Firmansyah', 'Cahyono', 'Utomo', 'Surya', 'Gunawan', 'Pranata'
        ];

        $applicantData = [];

        // Generate applicants untuk setiap gelombang
        foreach ($waves as $wave) {
            $count = match ($wave->wave_name) {
                'Gelombang 1' => 45,
                'Gelombang 2' => 35,
                'Gelombang 3' => 25,
                'Gelombang 4' => 15,
                default => 20,
            };

            for ($i = 1; $i <= $count; $i++) {
                $firstName = $firstNames[array_rand($firstNames)];
                $lastName = $lastNames[array_rand($lastNames)];
                $fullName = $firstName . ' ' . $lastName;
                
                // Generate NISN (10 digit)
                $nisn = '00' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
                
                // Generate nomor registrasi berdasarkan wave code
                $regNumber = $wave->wave_code . '-' . str_pad($i, 4, '0', STR_PAD_LEFT);
                
                // Generate phone number
                $phone = '08' . rand(1, 9) . rand(100000000, 999999999);
                
                // Generate email
                $emailName = strtolower(str_replace(' ', '.', $fullName));
                $email = $emailName . rand(100, 999) . '@gmail.com';
                
                // Random major
                $major = $majors[array_rand($majors)];
                
                // Note: payment_status is now computed from Payment relation
                // Will be set when Payment is created in PaymentSeeder
                
                // Registered datetime dalam range gelombang
                $startTime = strtotime($wave->start_datetime);
                $endTime = strtotime($wave->end_datetime);
                $randomTime = rand($startTime, min($endTime, time()));
                $registeredDate = date('Y-m-d H:i:s', $randomTime);

                $applicantData[] = [
                    'registration_number' => $regNumber,
                    'applicant_full_name' => $fullName,
                    'applicant_nisn' => $nisn,
                    'applicant_phone_number' => $phone,
                    'applicant_email_address' => $email,
                    'chosen_major_name' => $major,
                    'wave_id' => $wave->id,
                    // payment_status removed - now computed from Payment relation
                    'registered_datetime' => $registeredDate,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Bulk insert untuk performance
        foreach (array_chunk($applicantData, 50) as $chunk) {
            Applicant::insert($chunk);
        }

        $this->command->info('Berhasil membuat ' . count($applicantData) . ' data calon siswa.');
    }
}
