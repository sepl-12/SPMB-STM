<?php

namespace App\Models;

use App\Enum\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Applicant extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'registered_datetime' => 'datetime',
    ];

    protected ?array $latestSubmissionAnswersCache = null;

    /**
     * Appends - automatically include these when converting to array/json
     */
    protected $appends = ['payment_status_computed', 'payment_status_badge'];

    public function wave(): BelongsTo
    {
        return $this->belongsTo(Wave::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestSubmission(): HasOne
    {
        return $this->hasOne(Submission::class)->latestOfMany('submitted_datetime');
    }

    /**
     * Get latest payment relationship
     */
    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)
            ->latestOfMany('status_updated_datetime');
    }

    public function getLatestSubmissionAnswers(): array
    {
        if ($this->latestSubmissionAnswersCache !== null) {
            return $this->latestSubmissionAnswersCache;
        }

        $submission = $this->relationLoaded('latestSubmission')
            ? $this->latestSubmission
            : $this->latestSubmission()->first();

        return $this->latestSubmissionAnswersCache = $submission?->answers_json ?? [];
    }

    public function getLatestAnswerForField(string $fieldKey): mixed
    {
        return $this->getLatestSubmissionAnswers()[$fieldKey] ?? null;
    }

    /**
     * Accessor: Get payment status from latest payment (Single Source of Truth)
     * This replaces the old payment_status column
     */
    public function getPaymentStatusComputedAttribute(): ?PaymentStatus
    {
        // Use eager loaded relation if available
        if ($this->relationLoaded('latestPayment')) {
            return $this->latestPayment?->payment_status_name;
        }

        // Otherwise query it
        $latestPayment = $this->latestPayment;
        return $latestPayment?->payment_status_name;
    }

    /**
     * Accessor: Get payment status as string value (for compatibility)
     */
    public function getPaymentStatusAttribute(): ?string
    {
        $status = $this->payment_status_computed;
        return $status?->value;
    }

    /**
     * Accessor: Get payment status badge data for UI
     */
    public function getPaymentStatusBadgeAttribute(): array
    {
        $status = $this->payment_status_computed;

        if (!$status) {
            return [
                'label' => 'Belum Bayar',
                'color' => 'warning',
                'value' => 'unpaid',
            ];
        }

        return [
            'label' => $status->label(),
            'color' => $status->color(),
            'value' => $status->value,
        ];
    }

    /**
     * Check if payment is successful
     */
    public function hasSuccessfulPayment(): bool
    {
        return $this->payment_status_computed?->isSuccess() ?? false;
    }

    /**
     * Check if payment is pending
     */
    public function hasPendingPayment(): bool
    {
        return $this->payment_status_computed?->isPending() ?? true;
    }

    /**
     * Check if payment is failed
     */
    public function hasFailedPayment(): bool
    {
        return $this->payment_status_computed?->isFailed() ?? false;
    }
}
