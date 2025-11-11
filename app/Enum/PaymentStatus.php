<?php

namespace App\Enum;

/**
 * Payment Status Enum
 * 
 * Standarisasi status pembayaran sesuai dengan Midtrans API
 * @see https://docs.midtrans.com/en/after-payment/get-status
 */
enum PaymentStatus: string
{
    // Midtrans standard statuses (lowercase untuk match dengan Midtrans response)
    case PENDING = 'pending';
    case SETTLEMENT = 'settlement';
    case CAPTURE = 'capture';
    case CANCEL = 'cancel';
    case DENY = 'deny';
    case EXPIRE = 'expire';
    case FAILURE = 'failure';

    // Manual payment status
    case PENDING_VERIFICATION = 'pending_verification';

    /**
     * Get all status values as array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get status label for display
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Pembayaran',
            self::SETTLEMENT => 'Pembayaran Berhasil',
            self::CAPTURE => 'Pembayaran Ditangkap',
            self::CANCEL => 'Dibatalkan',
            self::DENY => 'Ditolak',
            self::EXPIRE => 'Kedaluwarsa',
            self::FAILURE => 'Gagal',
            self::PENDING_VERIFICATION => 'Menunggu Verifikasi',
        };
    }

    /**
     * Get status color for UI (badge/badge color)
     */
    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::SETTLEMENT => 'success',
            self::CAPTURE => 'info',
            self::CANCEL, self::DENY, self::EXPIRE, self::FAILURE => 'danger',
            self::PENDING_VERIFICATION => 'warning',
        };
    }

    /**
     * Get status icon
     */
    public function icon(): string
    {
        return match ($this) {
            self::PENDING => 'heroicon-o-clock',
            self::SETTLEMENT => 'heroicon-o-check-circle',
            self::CAPTURE => 'heroicon-o-information-circle',
            self::CANCEL, self::DENY, self::EXPIRE, self::FAILURE => 'heroicon-o-x-circle',
            self::PENDING_VERIFICATION => 'heroicon-o-document-magnifying-glass',
        };
    }

    /**
     * Check if payment is successful
     */
    public function isSuccess(): bool
    {
        return $this === self::SETTLEMENT;
    }

    /**
     * Check if payment is failed
     */
    public function isFailed(): bool
    {
        return in_array($this, [self::FAILURE, self::CANCEL, self::DENY, self::EXPIRE]);
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return in_array($this, [self::PENDING, self::CAPTURE, self::PENDING_VERIFICATION]);
    }

    /**
     * Get simplified status for applicant payment_status field
     */
    public function getSimplifiedStatus(): string
    {
        return match ($this) {
            self::SETTLEMENT => 'paid',
            self::FAILURE, self::CANCEL, self::DENY, self::EXPIRE => 'unpaid',
            default => 'unpaid',
        };
    }

    /**
     * Create from string (case-insensitive)
     */
    public static function fromString(string $status): ?self
    {
        $status = strtolower($status);

        return match ($status) {
            'pending' => self::PENDING,
            'settlement' => self::SETTLEMENT,
            'capture' => self::CAPTURE,
            'cancel' => self::CANCEL,
            'deny' => self::DENY,
            'expire' => self::EXPIRE,
            'failure', 'failed' => self::FAILURE,
            'pending_verification' => self::PENDING_VERIFICATION,
            default => null,
        };
    }
}
