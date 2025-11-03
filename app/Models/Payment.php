<?php

namespace App\Models;

use App\Enum\PaymentStatus;
use App\Enum\PaymentMethod;
use App\Helpers\PaymentHelper;
use App\Traits\HasPaymentAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, HasPaymentAttributes, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'paid_amount_total' => 'decimal:2',
        'status_updated_datetime' => 'datetime',
        'gateway_payload_json' => 'array',
        'payment_status_name' => PaymentStatus::class,
        'payment_method_name' => PaymentMethod::class,
    ];

    protected $fillable = [
        'applicant_id',
        'payment_gateway_name',
        'merchant_order_code',
        'paid_amount_total',
        'currency_code',
        'payment_method_name',
        'payment_status_name',
        'status_updated_datetime',
        'gateway_payload_json',
        'deleted_by',
        'deletion_reason',
    ];

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Boot method to add business rules for deletion
     */
    protected static function booted(): void
    {
        static::deleting(function (Payment $payment) {
            // Business Rule: Settlement payment tidak boleh dihapus
            if ($payment->payment_status_name === PaymentStatus::SETTLEMENT) {
                throw new \Exception('Payment yang sudah settlement tidak dapat dihapus. Hubungi supervisor untuk proses lebih lanjut.');
            }
        });
    }

    /**
     * Check if payment is successful (alias for trait method)
     */
    public function isSuccess(): bool
    {
        return $this->isPaymentSuccessful();
    }

    /**
     * Check if payment is failed (alias for trait method)
     */
    public function isFailed(): bool
    {
        return $this->isPaymentFailed();
    }

    /**
     * Check if payment is pending (alias for trait method)
     */
    public function isPending(): bool
    {
        return $this->isPaymentPending();
    }

    /**
     * Scope: filter by status
     */
    public function scopeWithStatus($query, PaymentStatus $status)
    {
        return $query->where('payment_status_name', $status->value);
    }

    /**
     * Scope: filter by method
     */
    public function scopeWithMethod($query, PaymentMethod $method)
    {
        return $query->where('payment_method_name', $method->value);
    }

    /**
     * Scope: successful payments only
     */
    public function scopeSuccessful($query)
    {
        return $query->where('payment_status_name', PaymentStatus::SETTLEMENT->value);
    }

    /**
     * Scope: failed payments only
     */
    public function scopeFailed($query)
    {
        return $query->whereIn('payment_status_name', [
            PaymentStatus::FAILURE->value,
            PaymentStatus::CANCEL->value,
            PaymentStatus::DENY->value,
            PaymentStatus::EXPIRE->value,
        ]);
    }

    /**
     * Scope: pending payments only
     */
    public function scopePending($query)
    {
        return $query->whereIn('payment_status_name', [
            PaymentStatus::PENDING->value,
            PaymentStatus::CAPTURE->value,
        ]);
    }

    /**
     * Scope: today's payments
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope: this month's payments
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    /**
     * Scope: filter by amount range
     */
    public function scopeAmountBetween($query, float $min, float $max)
    {
        return $query->whereBetween('paid_amount_total', [$min, $max]);
    }
}
