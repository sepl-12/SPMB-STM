<?php

namespace App\Models;

use App\Enum\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\URL;

class Applicant extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'registered_datetime' => 'datetime',
    ];

    protected ?array $latestSubmissionAnswersCache = null;

    /**
     * Appends - REMOVED untuk avoid N+1 queries
     * Use services explicitly in controllers/views instead
     * 
     * @deprecated Use ApplicantPaymentStatusResolver service instead
     */
    // protected $appends = ['payment_status_computed', 'payment_status_badge'];

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
     * ============================================================
     * DEPRECATED METHODS
     * ============================================================
     * 
     * Use dedicated services instead:
     * - ApplicantPaymentStatusResolver for payment status logic
     * - ApplicantUrlGenerator for URL generation
     * 
     * These methods are kept for backward compatibility but will
     * be removed in future versions.
     */

    /**
     * @deprecated Use ApplicantPaymentStatusResolver::getLatestStatus() instead
     */
    public function getPaymentStatusComputedAttribute(): ?PaymentStatus
    {
        return app(\App\Services\Applicant\ApplicantPaymentStatusResolver::class)
            ->getLatestStatus($this);
    }

    /**
     * @deprecated Use ApplicantPaymentStatusResolver::getStatusValue() instead
     */
    public function getPaymentStatusAttribute(): ?string
    {
        return app(\App\Services\Applicant\ApplicantPaymentStatusResolver::class)
            ->getStatusValue($this);
    }

    /**
     * @deprecated Use ApplicantPaymentStatusResolver::getStatusBadge() instead
     */
    public function getPaymentStatusBadgeAttribute(): array
    {
        return app(\App\Services\Applicant\ApplicantPaymentStatusResolver::class)
            ->getStatusBadge($this);
    }

    /**
     * @deprecated Use ApplicantPaymentStatusResolver::hasSuccessfulPayment() instead
     */
    public function hasSuccessfulPayment(): bool
    {
        return app(\App\Services\Applicant\ApplicantPaymentStatusResolver::class)
            ->hasSuccessfulPayment($this);
    }

    /**
     * @deprecated Use ApplicantPaymentStatusResolver::hasPendingPayment() instead
     */
    public function hasPendingPayment(): bool
    {
        return app(\App\Services\Applicant\ApplicantPaymentStatusResolver::class)
            ->hasPendingPayment($this);
    }

    /**
     * @deprecated Use ApplicantPaymentStatusResolver::hasFailedPayment() instead
     */
    public function hasFailedPayment(): bool
    {
        return app(\App\Services\Applicant\ApplicantPaymentStatusResolver::class)
            ->hasFailedPayment($this);
    }

    /**
     * @deprecated Use ApplicantUrlGenerator::getPaymentUrl() instead
     * 
     * @param int|null $expiresInDays Jumlah hari sebelum URL expired (default: 7)
     * @return string Signed URL dengan signature dan expiration
     */
    public function getPaymentUrl(?int $expiresInDays = 7): string
    {
        return app(\App\Services\Applicant\ApplicantUrlGenerator::class)
            ->getPaymentUrl($this, $expiresInDays);
    }

    /**
     * @deprecated Use ApplicantUrlGenerator::getStatusUrl() instead
     * 
     * @param int|null $expiresInDays Jumlah hari sebelum URL expired (default: 30)
     * @return string Signed URL dengan signature dan expiration
     */
    public function getStatusUrl(?int $expiresInDays = 30): string
    {
        return app(\App\Services\Applicant\ApplicantUrlGenerator::class)
            ->getStatusUrl($this, $expiresInDays);
    }

    /**
     * @deprecated Use ApplicantUrlGenerator::getExamCardUrl() instead
     * 
     * @param int|null $expiresInDays Jumlah hari sebelum URL expired (default: 60)
     * @return string Signed URL dengan signature dan expiration
     */
    public function getExamCardUrl(?int $expiresInDays = 60): string
    {
        return app(\App\Services\Applicant\ApplicantUrlGenerator::class)
            ->getExamCardUrl($this, $expiresInDays);
    }

    /**
     * @deprecated Use ApplicantUrlGenerator::getPaymentSuccessUrl() instead
     * 
     * @param int|null $expiresInDays Jumlah hari sebelum URL expired (default: 7)
     * @return string Signed URL dengan signature dan expiration
     */
    public function getPaymentSuccessUrl(?int $expiresInDays = 7): string
    {
        return app(\App\Services\Applicant\ApplicantUrlGenerator::class)
            ->getPaymentSuccessUrl($this, $expiresInDays);
    }

    /**
     * Accessor untuk payment_url attribute
     * Bisa dipanggil dengan: $applicant->payment_url
     */
    protected function paymentUrl(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn() => $this->getPaymentUrl(),
        );
    }

    /**
     * Accessor untuk status_url attribute
     * Bisa dipanggil dengan: $applicant->status_url
     */
    protected function statusUrl(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn() => $this->getStatusUrl(),
        );
    }

    /**
     * Accessor untuk exam_card_url attribute
     * Bisa dipanggil dengan: $applicant->exam_card_url
     */
    protected function examCardUrl(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn() => $this->getExamCardUrl(),
        );
    }

    /**
     * Accessor untuk payment_success_url attribute
     * Bisa dipanggil dengan: $applicant->payment_success_url
     */
    protected function paymentSuccessUrl(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn() => $this->getPaymentSuccessUrl(),
        );
    }
}
