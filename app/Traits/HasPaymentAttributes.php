<?php

namespace App\Traits;

use App\Enum\PaymentStatus;
use App\Enum\PaymentMethod;
use App\Helpers\PaymentHelper;

trait HasPaymentAttributes
{
    /**
     * Get formatted amount with currency symbol (IDR only)
     */
    public function getFormattedAmountAttribute(): string
    {
        return PaymentHelper::formatIDR($this->paid_amount_total ?? 0);
    }

    /**
     * Get payment status badge information
     */
    public function getStatusBadgeAttribute(): array
    {
        $status = $this->payment_status_name ?? PaymentStatus::PENDING;
        return [
            'label' => $status->label(),
            'color' => $status->color(),
            'icon' => $status->icon(),
            'css_class' => PaymentHelper::getStatusCssClass($status),
        ];
    }

    /**
     * Get payment method display label
     */
    public function getMethodLabelAttribute(): string
    {
        $method = $this->payment_method_name ?? PaymentMethod::ECHANNEL;
        return $method->label();
    }

    /**
     * Get payment gateway display label (always Midtrans)
     */
    public function getGatewayLabelAttribute(): string
    {
        return 'Midtrans Payment Gateway';
    }

    /**
     * Check if payment is successful
     */
    public function isPaymentSuccessful(): bool
    {
        $status = $this->payment_status_name ?? PaymentStatus::PENDING;
        return $status->isSuccess();
    }

    /**
     * Check if payment is failed
     */
    public function isPaymentFailed(): bool
    {
        $status = $this->payment_status_name ?? PaymentStatus::PENDING;
        return $status->isFailed();
    }

    /**
     * Check if payment is pending
     */
    public function isPaymentPending(): bool
    {
        $status = $this->payment_status_name ?? PaymentStatus::PENDING;
        return $status->isPending();
    }

    /**
     * Get payment processing time
     */
    public function getProcessingTimeAttribute(): string
    {
        $method = $this->payment_method_name ?? PaymentMethod::ECHANNEL;
        return $method->processingTime();
    }

    /**
     * Get payment instructions
     */
    public function getPaymentInstructionsAttribute(): array
    {
        $method = $this->payment_method_name ?? PaymentMethod::ECHANNEL;
        return PaymentHelper::getPaymentInstructions($method);
    }

    /**
     * Get simplified status for applicant model
     */
    public function getSimplifiedPaymentStatusAttribute(): string
    {
        $status = $this->payment_status_name ?? PaymentStatus::PENDING;
        return $status->getSimplifiedStatus();
    }
}
