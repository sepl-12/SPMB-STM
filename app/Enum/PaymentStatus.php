<?php

namespace App\Enum;

enum PaymentStatus: string
{
    case PENDING = 'PENDING';
    case PAID = 'PAID';
    case FAILED = 'FAILED';
    case CANCEL = 'CANCEL';
    case DENY = 'DENY';
    case EXPIRE = 'EXPIRE';
    case SETTLEMENT = 'SETTLEMENT';
    case CAPTURE = 'CAPTURE';

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
            self::PAID => 'Lunas',
            self::FAILED => 'Gagal',
            self::CANCEL => 'Dibatalkan',
            self::DENY => 'Ditolak',
            self::EXPIRE => 'Kedaluwarsa',
            self::SETTLEMENT => 'Selesai',
            self::CAPTURE => 'Tertangkap',
        };
    }

    /**
     * Get status color for UI (badge/badge color)
     */
    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::PAID, self::SETTLEMENT => 'success',
            self::FAILED, self::CANCEL, self::DENY, self::EXPIRE => 'danger',
            self::CAPTURE => 'info',
        };
    }

    /**
     * Get status icon
     */
    public function icon(): string
    {
        return match ($this) {
            self::PENDING => 'heroicon-o-clock',
            self::PAID, self::SETTLEMENT => 'heroicon-o-check-circle',
            self::FAILED, self::CANCEL, self::DENY, self::EXPIRE => 'heroicon-o-x-circle',
            self::CAPTURE => 'heroicon-o-information-circle',
        };
    }

    /**
     * Check if payment is successful
     */
    public function isSuccess(): bool
    {
        return in_array($this, [self::PAID, self::SETTLEMENT]);
    }

    /**
     * Check if payment is failed
     */
    public function isFailed(): bool
    {
        return in_array($this, [self::FAILED, self::CANCEL, self::DENY, self::EXPIRE]);
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return in_array($this, [self::PENDING, self::CAPTURE]);
    }

    /**
     * Get simplified status for applicant payment_status field
     */
    public function getSimplifiedStatus(): string
    {
        return match ($this) {
            self::PAID, self::SETTLEMENT => 'paid',
            self::FAILED, self::CANCEL, self::DENY, self::EXPIRE => 'unpaid',
            default => 'unpaid',
        };
    }
}
