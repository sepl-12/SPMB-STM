<?php

namespace Database\Seeders;

use App\Models\Applicant;
use App\Models\Payment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hanya buat payment untuk applicant yang statusnya 'paid' atau 'verified'
        $applicants = Applicant::whereIn('payment_status', ['paid', 'verified'])->get();
        
        if ($applicants->isEmpty()) {
            $this->command->warn('Tidak ada calon siswa dengan status paid/verified. Jalankan ApplicantSeeder terlebih dahulu.');
            return;
        }

        $paymentGateways = [
            'Midtrans',
            'Xendit',
            'DOKU',
            'Manual Transfer',
        ];

        $paymentMethods = [
            'Bank Transfer - BCA',
            'Bank Transfer - BNI',
            'Bank Transfer - BRI',
            'Bank Transfer - Mandiri',
            'E-Wallet - GoPay',
            'E-Wallet - OVO',
            'E-Wallet - DANA',
            'Virtual Account',
            'QRIS',
        ];

        $paymentData = [];

        foreach ($applicants as $applicant) {
            $gateway = $paymentGateways[array_rand($paymentGateways)];
            $method = $paymentMethods[array_rand($paymentMethods)];
            
            // Generate merchant order code
            $orderCode = 'ORD-' . strtoupper(substr(md5($applicant->registration_number), 0, 10));
            
            // Get registration fee from wave
            $amount = $applicant->wave->registration_fee_amount;
            
            // Payment status berdasarkan applicant status
            $paymentStatusName = $applicant->payment_status === 'verified' ? 'settlement' : 'pending';
            
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
                'payment_type' => strtolower(str_replace(' ', '_', $method)),
                'transaction_time' => $statusUpdatedDate,
                'transaction_status' => $paymentStatusName,
                'fraud_status' => 'accept',
                'status_code' => $paymentStatusName === 'settlement' ? '200' : '201',
                'status_message' => $paymentStatusName === 'settlement' ? 'Success, transaction is found' : 'Pending, waiting for payment',
            ];
            
            // Jika verified, tambah info bank
            if ($applicant->payment_status === 'verified') {
                $gatewayPayload['va_numbers'] = [
                    [
                        'bank' => explode(' - ', $method)[1] ?? 'BCA',
                        'va_number' => '8888' . rand(100000000000, 999999999999),
                    ]
                ];
                $gatewayPayload['settlement_time'] = $statusUpdatedDate;
            }

            $paymentData[] = [
                'applicant_id' => $applicant->id,
                'payment_gateway_name' => $gateway,
                'merchant_order_code' => $orderCode,
                'paid_amount_total' => $amount,
                'currency_code' => 'IDR',
                'payment_method_name' => $method,
                'payment_status_name' => $paymentStatusName,
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
