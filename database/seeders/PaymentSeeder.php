<?php

namespace Database\Seeders;

use App\Models\Applicant;
use App\Models\Payment;
use App\Enum\PaymentStatus;
use App\Enum\PaymentMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat payment untuk semua applicant (payment_status sekarang computed dari Payment)
        // 85% akan diberi payment successful, 15% pending/failed
        $applicants = Applicant::all();
        
        if ($applicants->isEmpty()) {
            $this->command->warn('Tidak ada calon siswa. Jalankan ApplicantSeeder terlebih dahulu.');
            return;
        }

        $paymentGateways = [
            'Midtrans',
        ];

        // Gunakan enum untuk payment methods
        $paymentMethods = [
            PaymentMethod::BCA_VA,
            PaymentMethod::BNI_VA,
            PaymentMethod::BRI_VA,
            PaymentMethod::MANDIRI_VA,
            PaymentMethod::GOPAY,
            PaymentMethod::QRIS,
            PaymentMethod::CREDIT_CARD,
        ];

        $paymentData = [];

        foreach ($applicants as $applicant) {
            // Random untuk menentukan apakah ada payment atau tidak
            $rand = rand(1, 100);
            
            // 85% akan punya payment settlement, 10% pending, 5% failed
            if ($rand <= 85) {
                $paymentStatus = PaymentStatus::SETTLEMENT;
            } elseif ($rand <= 95) {
                $paymentStatus = PaymentStatus::PENDING;
            } else {
                $paymentStatus = PaymentStatus::FAILURE;
            }
            
            $gateway = $paymentGateways[array_rand($paymentGateways)];
            $method = $paymentMethods[array_rand($paymentMethods)];
            
            // Generate merchant order code
            $orderCode = 'ORD-' . strtoupper(substr(md5($applicant->registration_number), 0, 10));
            
            // Get registration fee from wave
            $amount = $applicant->wave->registration_fee_amount;
            
            // Status updated datetime (1-3 hari setelah registrasi)
            $registeredTime = strtotime($applicant->registered_datetime);
            $daysAfter = rand(1, 3);
            $hoursAfter = rand(0, 23);
            $statusUpdatedTime = strtotime("+$daysAfter days +$hoursAfter hours", $registeredTime);
            $statusUpdatedDate = date('Y-m-d H:i:s', $statusUpdatedTime);
            
            // Generate gateway payload (sample)
            $gatewayPayload = [
                'transaction_id' => 'TRX-' . strtoupper(substr(md5(uniqid()), 0, 16)),
                'order_id' => $orderCode,
                'gross_amount' => $amount,
                'payment_type' => $method->value,
                'transaction_time' => $statusUpdatedDate,
                'transaction_status' => $paymentStatus->value,
                'fraud_status' => 'accept',
                'status_code' => $paymentStatus === PaymentStatus::SETTLEMENT ? '200' : '201',
                'status_message' => $paymentStatus === PaymentStatus::SETTLEMENT 
                    ? 'Success, transaction is found' 
                    : 'Pending, waiting for payment',
            ];
            
            // Jika settlement, tambah info bank dan settlement time
            if ($paymentStatus === PaymentStatus::SETTLEMENT) {
                if ($method->isVirtualAccount()) {
                    $gatewayPayload['va_numbers'] = [
                        [
                            'bank' => strtoupper(str_replace('_va', '', $method->value)),
                            'va_number' => '8888' . rand(100000000000, 999999999999),
                        ]
                    ];
                }
                $gatewayPayload['settlement_time'] = $statusUpdatedDate;
            }

            $paymentData[] = [
                'applicant_id' => $applicant->id,
                'payment_gateway_name' => $gateway,
                'merchant_order_code' => $orderCode,
                'paid_amount_total' => $amount,
                'currency_code' => 'IDR',
                'payment_method_name' => $method->value,
                'payment_status_name' => $paymentStatus->value,
                'status_updated_datetime' => $statusUpdatedDate,
                'gateway_payload_json' => json_encode($gatewayPayload),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Bulk insert
        foreach (array_chunk($paymentData, 50) as $chunk) {
            Payment::insert($chunk);
        }

        $this->command->info('Berhasil membuat ' . count($paymentData) . ' data pembayaran.');
    }
}
