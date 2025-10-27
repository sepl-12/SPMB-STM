<?php

namespace App\Services\Applicant;

use App\Models\Applicant;
use Illuminate\Support\Facades\URL;

/**
 * Service untuk generate signed URLs untuk applicant
 * 
 * Memisahkan URL generation dari Model untuk:
 * - Easier testing (can mock URL facade)
 * - Centralized URL configuration
 * - Single Responsibility
 */
class ApplicantUrlGenerator
{
    /**
     * Default expiration days untuk berbagai URL types
     */
    protected const DEFAULT_PAYMENT_EXPIRY = 7;
    protected const DEFAULT_STATUS_EXPIRY = 30;
    protected const DEFAULT_EXAM_CARD_EXPIRY = 60;
    protected const DEFAULT_PAYMENT_SUCCESS_EXPIRY = 7;

    /**
     * Generate secure signed URL untuk payment page
     * 
     * @param Applicant $applicant
     * @param int|null $expiresInDays Jumlah hari sebelum URL expired
     * @return string Signed URL dengan signature dan expiration
     */
    public function getPaymentUrl(Applicant $applicant, ?int $expiresInDays = null): string
    {
        $expiresInDays ??= self::DEFAULT_PAYMENT_EXPIRY;

        return URL::temporarySignedRoute(
            'payment.show-secure',
            now()->addDays($expiresInDays),
            ['registration_number' => $applicant->registration_number]
        );
    }

    /**
     * Generate secure signed URL untuk status page
     * 
     * @param Applicant $applicant
     * @param int|null $expiresInDays Jumlah hari sebelum URL expired
     * @return string Signed URL dengan signature dan expiration
     */
    public function getStatusUrl(Applicant $applicant, ?int $expiresInDays = null): string
    {
        $expiresInDays ??= self::DEFAULT_STATUS_EXPIRY;

        return URL::temporarySignedRoute(
            'applicant.status-secure',
            now()->addDays($expiresInDays),
            ['registration_number' => $applicant->registration_number]
        );
    }

    /**
     * Generate secure signed URL untuk exam card
     * 
     * @param Applicant $applicant
     * @param int|null $expiresInDays Jumlah hari sebelum URL expired
     * @return string Signed URL dengan signature dan expiration
     */
    public function getExamCardUrl(Applicant $applicant, ?int $expiresInDays = null): string
    {
        $expiresInDays ??= self::DEFAULT_EXAM_CARD_EXPIRY;

        return URL::temporarySignedRoute(
            'exam-card.show',
            now()->addDays($expiresInDays),
            ['registration_number' => $applicant->registration_number]
        );
    }

    /**
     * Generate secure signed URL untuk payment success page
     * 
     * @param Applicant $applicant
     * @param int|null $expiresInDays Jumlah hari sebelum URL expired
     * @return string Signed URL dengan signature dan expiration
     */
    public function getPaymentSuccessUrl(Applicant $applicant, ?int $expiresInDays = null): string
    {
        $expiresInDays ??= self::DEFAULT_PAYMENT_SUCCESS_EXPIRY;

        return URL::temporarySignedRoute(
            'payment.success-secure',
            now()->addDays($expiresInDays),
            ['registration_number' => $applicant->registration_number]
        );
    }

    /**
     * Generate all applicant URLs sekaligus
     * 
     * @param Applicant $applicant
     * @return array{payment: string, status: string, exam_card: string, payment_success: string}
     */
    public function getAllUrls(Applicant $applicant): array
    {
        return [
            'payment' => $this->getPaymentUrl($applicant),
            'status' => $this->getStatusUrl($applicant),
            'exam_card' => $this->getExamCardUrl($applicant),
            'payment_success' => $this->getPaymentSuccessUrl($applicant),
        ];
    }
}
