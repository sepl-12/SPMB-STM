<?php

namespace App\Services\ManualPayment;

use App\Enum\PaymentMethod;
use App\Enum\PaymentStatus;
use App\Models\Applicant;
use App\Models\ManualPayment;
use App\Models\Payment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ManualPaymentService
{
    /**
     * Create manual payment and upload proof
     */
    public function createManualPayment(
        Applicant $applicant,
        UploadedFile $proofImage,
        float $paidAmount,
        ?string $notes = null
    ): ManualPayment {
        return DB::transaction(function () use ($applicant, $proofImage, $paidAmount, $notes) {
            // Get or create pending payment
            $payment = $this->getOrCreatePendingPayment($applicant);

            // Store proof image
            $imagePath = $this->storeProofImage($proofImage, $applicant->registration_number);

            // Create manual payment record
            $manualPayment = ManualPayment::create([
                'payment_id' => $payment->id,
                'applicant_id' => $applicant->id,
                'proof_image_path' => $imagePath,
                'upload_datetime' => now(),
                'approval_status' => 'pending',
                'paid_amount' => $paidAmount,
                'payment_notes' => $notes,
            ]);

            // Update payment status to pending_verification
            $payment->update([
                'payment_status_name' => PaymentStatus::PENDING_VERIFICATION,
                'status_updated_datetime' => now(),
            ]);

            Log::info('Manual payment created', [
                'manual_payment_id' => $manualPayment->id,
                'payment_id' => $payment->id,
                'applicant_id' => $applicant->id,
                'amount' => $paidAmount,
            ]);

            return $manualPayment;
        });
    }

    /**
     * Get existing pending payment or create new one
     */
    protected function getOrCreatePendingPayment(Applicant $applicant): Payment
    {
        // Check if there's already a pending_verification payment
        $existingPayment = $applicant->payments()
            ->where('payment_status_name', PaymentStatus::PENDING_VERIFICATION->value)
            ->latest()
            ->first();

        if ($existingPayment) {
            return $existingPayment;
        }

        // Check if there's a pending payment from Midtrans
        $existingPayment = $applicant->payments()
            ->where('payment_status_name', PaymentStatus::PENDING->value)
            ->latest()
            ->first();

        if ($existingPayment) {
            return $existingPayment;
        }

        // Create new payment
        $registrationFee = $applicant->wave->registration_fee_amount;
        $orderId = $this->generateOrderId($applicant->registration_number);

        return Payment::create([
            'applicant_id' => $applicant->id,
            'payment_gateway_name' => 'Manual',
            'merchant_order_code' => $orderId,
            'paid_amount_total' => $registrationFee,
            'currency_code' => 'IDR',
            'payment_method_name' => PaymentMethod::MANUAL_TRANSFER,
            'payment_status_name' => PaymentStatus::PENDING_VERIFICATION,
            'status_updated_datetime' => now(),
            'gateway_payload_json' => [
                'payment_type' => 'manual_qris',
                'created_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Store proof image to private storage
     */
    protected function storeProofImage(UploadedFile $file, string $registrationNumber): string
    {
        $year = now()->year;
        $month = now()->format('m');
        $timestamp = now()->timestamp;
        $extension = $file->getClientOriginalExtension();

        $filename = "{$registrationNumber}_{$timestamp}.{$extension}";
        $directory = "payment-proofs/{$year}/{$month}";

        $path = $file->storeAs($directory, $filename, 'private');

        return $path;
    }

    /**
     * Generate order ID
     */
    protected function generateOrderId(string $registrationNumber): string
    {
        $timestamp = now()->format('ymdHis');
        return "MANUAL-{$registrationNumber}-{$timestamp}";
    }

    /**
     * Validate amount matches registration fee (with tolerance)
     */
    public function validateAmount(Applicant $applicant, float $paidAmount): bool
    {
        $expectedAmount = $applicant->wave->registration_fee_amount;
        $tolerance = $expectedAmount * 0.05; // 5% tolerance

        $minAmount = $expectedAmount - $tolerance;
        $maxAmount = $expectedAmount + $tolerance;

        return $paidAmount >= $minAmount && $paidAmount <= $maxAmount;
    }

    /**
     * Check if applicant can upload manual payment
     */
    public function canUploadManualPayment(Applicant $applicant): bool
    {
        // Check if already has successful payment
        if ($applicant->hasSuccessfulPayment()) {
            return false;
        }

        // Check if has pending verification
        $hasPendingVerification = $applicant->payments()
            ->where('payment_status_name', PaymentStatus::PENDING_VERIFICATION->value)
            ->exists();

        // Allow if no pending verification yet
        return !$hasPendingVerification;
    }
}
