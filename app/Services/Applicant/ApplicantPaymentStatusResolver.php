<?php

namespace App\Services\Applicant;

use App\Enum\PaymentStatus;
use App\Models\Applicant;

/**
 * Service untuk resolve payment status dari Applicant
 * 
 * Memisahkan business logic dari Model untuk:
 * - Easier testing (no database needed)
 * - Better performance (explicit eager loading)
 * - Single Responsibility Principle
 */
class ApplicantPaymentStatusResolver
{
    /**
     * Get latest payment status dari applicant
     * 
     * @param Applicant $applicant
     * @return PaymentStatus|null
     */
    public function getLatestStatus(Applicant $applicant): ?PaymentStatus
    {
        // Ensure relation is eager loaded untuk avoid N+1
        if (!$applicant->relationLoaded('latestPayment')) {
            $applicant->load('latestPayment');
        }

        return $applicant->latestPayment?->payment_status_name;
    }

    /**
     * Check apakah applicant sudah bayar sukses
     * 
     * @param Applicant $applicant
     * @return bool
     */
    public function hasSuccessfulPayment(Applicant $applicant): bool
    {
        $status = $this->getLatestStatus($applicant);

        return $status?->isSuccess() ?? false;
    }

    /**
     * Check apakah payment masih pending
     * 
     * @param Applicant $applicant
     * @return bool
     */
    public function hasPendingPayment(Applicant $applicant): bool
    {
        $status = $this->getLatestStatus($applicant);

        return $status?->isPending() ?? true;
    }

    /**
     * Check apakah payment failed
     * 
     * @param Applicant $applicant
     * @return bool
     */
    public function hasFailedPayment(Applicant $applicant): bool
    {
        $status = $this->getLatestStatus($applicant);

        return $status?->isFailed() ?? false;
    }

    /**
     * Get payment status as string value
     * 
     * @param Applicant $applicant
     * @return string|null
     */
    public function getStatusValue(Applicant $applicant): ?string
    {
        $status = $this->getLatestStatus($applicant);

        return $status?->value;
    }

    /**
     * Get badge data untuk UI (label, color, value)
     * 
     * @param Applicant $applicant
     * @return array{label: string, color: string, value: string}
     */
    public function getStatusBadge(Applicant $applicant): array
    {
        $status = $this->getLatestStatus($applicant);

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
     * Batch resolve payment status untuk multiple applicants
     * Optimized dengan single query (jika dari database query builder)
     * 
     * @param \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection|array $applicants
     * @return array<int, PaymentStatus|null> Keyed by applicant ID
     */
    public function batchGetStatuses($applicants): array
    {
        $applicantCollection = collect($applicants);

        // Eager load all latest payments in one query (if Eloquent Collection)
        if (
            $applicantCollection->first() instanceof Applicant &&
            $applicantCollection instanceof \Illuminate\Database\Eloquent\Collection
        ) {
            $applicantCollection->load('latestPayment');
        }

        return $applicantCollection->mapWithKeys(function (Applicant $applicant) {
            return [$applicant->id => $this->getLatestStatus($applicant)];
        })->all();
    }
}
