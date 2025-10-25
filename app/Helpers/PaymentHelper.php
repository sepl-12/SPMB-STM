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
        return collect(config('payment.payment_methods', []))
            ->mapWithKeys(fn($meta, $key) => [$key => $meta['label'] ?? $key])
            ->toArray();
    }

    /**
     * Get payment methods grouped by category
     */
    public static function getMethodsByCategory(): array
    {
        $methods = config('payment.payment_methods', []);

        $categories = [];
        foreach ($methods as $method => $meta) {
            $category = $meta['category'] ?? 'other';
            $categories[$category][$method] = $meta['label'] ?? $method;
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
     * @see https://docs.midtrans.com/en/after-payment/get-status
     */
    public static function mapMidtransStatus(string $transactionStatus, ?string $fraudStatus = null): PaymentStatus
    {
        $mapping = config('payment.status_mapping', []);
        $statusKey = strtolower($transactionStatus);

        $value = $mapping[$statusKey] ?? PaymentStatus::PENDING;

        if (is_array($value)) {
            $fraudKey = strtolower((string) $fraudStatus);
            $value = $value[$fraudKey] ?? PaymentStatus::CAPTURE;
        }

        if ($value instanceof PaymentStatus) {
            return $value;
        }

        return PaymentStatus::from($value);
    }
    
    /**
     * Map Midtrans payment type to PaymentMethod enum
     */
    public static function mapMidtransPaymentType(string $paymentType): PaymentMethod
    {
        $type = strtolower($paymentType);
        $methods = config('payment.payment_methods', []);

        if (array_key_exists($type, $methods)) {
            return PaymentMethod::from($type);
        }

        return match ($type) {
            'credit_card' => PaymentMethod::CREDIT_CARD,
            'bank_transfer' => PaymentMethod::BANK_TRANSFER,
            'bca_va', 'bca_klikbca', 'bca_klikpay' => PaymentMethod::BCA_VA,
            'bni_va' => PaymentMethod::BNI_VA,
            'bri_va' => PaymentMethod::BRI_VA,
            'echannel', 'mandiri_clickpay' => PaymentMethod::MANDIRI_VA,
            'permata_va' => PaymentMethod::PERMATA_VA,
            'other_va' => PaymentMethod::OTHER_VA,
            'gopay' => PaymentMethod::GOPAY,
            'shopeepay' => PaymentMethod::SHOPEEPAY,
            'qris' => PaymentMethod::QRIS,
            'cstore', 'alfamart' => PaymentMethod::ALFAMART,
            'indomaret' => PaymentMethod::INDOMARET,
            default => PaymentMethod::ECHANNEL,
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
        $meta = config('payment.payment_methods.' . $method->value, []);
        $configured = $meta['expiry'] ?? null;

        if ($configured !== null) {
            return (int) $configured;
        }

        return match ($meta['category'] ?? $method->category()) {
            'virtual_account' => 24 * 60,
            'ewallet' => 15,
            'qr_code' => 30,
            'convenience_store' => 3 * 24 * 60,
            default => 60,
        };
    }

    /**
     * Calculate payment fees (if any)
     */
    public static function calculateFees(float $amount, PaymentMethod $method): float
    {
        // Define fee structure - this should ideally come from config or database
        $meta = config('payment.payment_methods.' . $method->value, []);
        $fee = 0;

        if ($percentage = $meta['fee']['percentage'] ?? null) {
            $fee += $amount * (float) $percentage;
        }

        if ($flat = $meta['fee']['flat'] ?? null) {
            $fee += (float) $flat;
        }

        return round($fee);
    }

    /**
     * Get payment instructions for method
     */
    public static function getPaymentInstructions(PaymentMethod $method): array
    {
        $methods = config('payment.payment_methods', []);
        $meta = $methods[$method->value] ?? [];

        $instructions = $meta['instructions'] ?? null;

        if ($instructions) {
            return $instructions;
        }

        $category = $meta['category'] ?? 'default';
        $categoryInstructions = config('payment.instructions.' . $category)
            ?? config('payment.instructions.default');

        return $categoryInstructions ?? [
            'title' => 'Instruksi Pembayaran',
            'steps' => ['Ikuti instruksi yang ditampilkan pada halaman pembayaran.'],
        ];
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
