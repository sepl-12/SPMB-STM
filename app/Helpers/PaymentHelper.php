<?php

namespace App\Helpers;

use App\Enum\PaymentStatus;
use App\Enum\PaymentMethod;

class PaymentHelper
{
    /**
     * Format amount in Indonesian Rupiah
     */
    public static function formatIDR(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    /**
     * Format currency amount (alias for formatIDR since we only use IDR)
     */
    public static function formatCurrency(float $amount): string
    {
        return self::formatIDR($amount);
    }

    /**
     * Get payment status badge HTML (for Filament or Blade)
     */
    public static function getStatusBadge(PaymentStatus $status): array
    {
        return [
            'label' => $status->label(),
            'color' => $status->color(),
            'icon' => $status->icon(),
        ];
    }

    /**
     * Get all payment statuses for forms/filters
     */
    public static function getStatusOptions(): array
    {
        $options = [];
        foreach (PaymentStatus::cases() as $status) {
            $options[$status->value] = $status->label();
        }
        return $options;
    }

    /**
     * Get all payment methods for forms/filters
     */
    public static function getMethodOptions(): array
    {
        $options = [];
        foreach (PaymentMethod::cases() as $method) {
            $options[$method->value] = $method->label();
        }
        return $options;
    }

    /**
     * Get payment methods grouped by category
     */
    public static function getMethodsByCategory(): array
    {
        $categories = [];
        foreach (PaymentMethod::cases() as $method) {
            $category = $method->category();
            if (!isset($categories[$category])) {
                $categories[$category] = [];
            }
            $categories[$category][$method->value] = $method->label();
        }
        return $categories;
    }

    /**
     * Generate unique order ID
     */
    public static function generateOrderId(string $prefix = 'ORD', string $identifier = null): string
    {
        $timestamp = time();
        $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

        if ($identifier) {
            return "{$prefix}-{$identifier}-{$timestamp}-{$random}";
        }

        return "{$prefix}-{$timestamp}-{$random}";
    }

    /**
     * Validate payment amount
     */
    public static function validateAmount(float $amount, float $minAmount = 1000, float $maxAmount = 50000000): bool
    {
        return $amount >= $minAmount && $amount <= $maxAmount;
    }

    /**
     * Get payment status from Midtrans transaction status
     */
    public static function mapMidtransStatus(string $transactionStatus, ?string $fraudStatus = null): PaymentStatus
    {
        return match ($transactionStatus) {
            'capture' => $fraudStatus === 'accept' ? PaymentStatus::PAID : PaymentStatus::PENDING,
            'settlement' => PaymentStatus::SETTLEMENT,
            'pending' => PaymentStatus::PENDING,
            'cancel' => PaymentStatus::CANCEL,
            'deny' => PaymentStatus::DENY,
            'expire' => PaymentStatus::EXPIRE,
            'failure' => PaymentStatus::FAILED,
            default => PaymentStatus::PENDING,
        };
    }

    /**
     * Check if payment status requires action
     */
    public static function requiresAction(PaymentStatus $status): bool
    {
        return $status->isPending() || $status->isFailed();
    }

    /**
     * Get payment expiry time in minutes
     */
    public static function getExpiryTime(PaymentMethod $method): int
    {
        return match ($method->category()) {
            'virtual_account' => 24 * 60, // 24 hours
            'ewallet' => 15, // 15 minutes
            'qr_code' => 30, // 30 minutes
            'convenience_store' => 3 * 24 * 60, // 3 days
            default => 60, // 1 hour
        };
    }

    /**
     * Calculate payment fees (if any)
     */
    public static function calculateFees(float $amount, PaymentMethod $method): float
    {
        // Define fee structure - this should ideally come from config or database
        $fees = match ($method) {
            PaymentMethod::CREDIT_CARD => $amount * 0.029 + 2000, // 2.9% + Rp 2,000
            PaymentMethod::BCA_VA, PaymentMethod::BNI_VA, PaymentMethod::BRI_VA => 4000, // Flat fee
            PaymentMethod::GOPAY, PaymentMethod::OVO, PaymentMethod::DANA => $amount * 0.02, // 2%
            PaymentMethod::QRIS => $amount * 0.007, // 0.7%
            PaymentMethod::ALFAMART, PaymentMethod::INDOMARET => 5000, // Flat fee
            default => 0,
        };

        return round($fees);
    }

    /**
     * Get payment instructions for method
     */
    public static function getPaymentInstructions(PaymentMethod $method): array
    {
        return match ($method) {
            PaymentMethod::BCA_VA => [
                'title' => 'Cara Pembayaran BCA Virtual Account',
                'steps' => [
                    'Buka aplikasi BCA Mobile atau datang ke ATM BCA',
                    'Pilih menu "Transfer" atau "m-Transfer"',
                    'Pilih "ke BCA Virtual Account"',
                    'Masukkan nomor Virtual Account yang tertera',
                    'Masukkan jumlah pembayaran sesuai tagihan',
                    'Ikuti instruksi hingga transaksi selesai',
                ]
            ],
            PaymentMethod::GOPAY => [
                'title' => 'Cara Pembayaran dengan GoPay',
                'steps' => [
                    'Buka aplikasi Gojek atau GoPay',
                    'Scan QR Code yang ditampilkan',
                    'Atau klik tombol "Bayar dengan GoPay"',
                    'Masukkan PIN GoPay Anda',
                    'Pembayaran akan diproses secara otomatis',
                ]
            ],
            PaymentMethod::QRIS => [
                'title' => 'Cara Pembayaran dengan QRIS',
                'steps' => [
                    'Buka aplikasi e-wallet atau mobile banking',
                    'Pilih menu "Scan QR" atau "QRIS"',
                    'Scan QR Code yang ditampilkan',
                    'Periksa detail pembayaran',
                    'Konfirmasi pembayaran',
                ]
            ],
            default => [
                'title' => 'Instruksi Pembayaran',
                'steps' => [
                    'Ikuti instruksi yang ditampilkan di halaman pembayaran',
                    'Pastikan jumlah pembayaran sesuai dengan tagihan',
                    'Simpan bukti pembayaran untuk referensi',
                ]
            ]
        };
    }

    /**
     * Get CSS class for payment status
     */
    public static function getStatusCssClass(PaymentStatus $status): string
    {
        return match ($status->color()) {
            'success' => 'bg-green-100 text-green-800 border-green-200',
            'warning' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'danger' => 'bg-red-100 text-red-800 border-red-200',
            'info' => 'bg-blue-100 text-blue-800 border-blue-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }

    /**
     * Generate payment reference number
     */
    public static function generatePaymentReference(string $applicantId, string $waveId = null): string
    {
        $timestamp = date('YmdHis');
        $random = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);

        if ($waveId) {
            return "PAY-{$waveId}-{$applicantId}-{$timestamp}-{$random}";
        }

        return "PAY-{$applicantId}-{$timestamp}-{$random}";
    }
}
