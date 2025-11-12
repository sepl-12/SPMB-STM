<?php

namespace App\Payment\Services;

use App\Enum\PaymentMethod;
use App\Enum\PaymentStatus;
use App\Models\Applicant;
use App\Models\Payment;
use App\Payment\DTO\PaymentStatusResult;
use App\Payment\Exceptions\PaymentNotFoundException;
use App\Services\MidtransService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PaymentStatusService
{
    public function __construct(private readonly MidtransService $midtransService)
    {
    }

    public function getStatusPage(string $registrationNumber): PaymentStatusResult
    {
        $applicant = $this->findApplicant($registrationNumber, ['wave', 'payments']);
        $latestPayment = $applicant->payments()->latest()->first();

        return new PaymentStatusResult($applicant, $latestPayment);
    }

    public function getSuccessPage(string $registrationNumber): PaymentStatusResult
    {
        $applicant = $this->findApplicant($registrationNumber, ['wave', 'payments']);
        $latestSuccess = $applicant->payments()->successful()->latest()->first();

        return new PaymentStatusResult($applicant, $latestSuccess);
    }

    /**
     * @return array<string, mixed>
     */
    public function checkAjaxStatus(string $orderId): array
    {
        // Find payment by order ID
        $payment = Payment::where('merchant_order_code', $orderId)->first();

        if (!$payment) {
            return [
                'success' => false,
                'message' => 'Payment not found',
            ];
        }

        // Skip Midtrans check for manual payments
        if ($payment->payment_method_name === PaymentMethod::MANUAL_TRANSFER ||
            $payment->payment_status_name === PaymentStatus::PENDING_VERIFICATION) {
            return [
                'success' => true,
                'message' => 'Manual payment - awaiting admin verification',
                'status' => $payment->payment_status_name->value,
                'is_manual' => true,
            ];
        }

        // Check status from Midtrans for automated payments
        return $this->midtransService->checkTransactionStatus($orderId);
    }

    protected function findApplicant(string $registrationNumber, array $with = []): Applicant
    {
        try {
            return Applicant::with($with)
                ->where('registration_number', $registrationNumber)
                ->firstOrFail();
        } catch (ModelNotFoundException) {
            throw new PaymentNotFoundException('Data pendaftaran tidak ditemukan.');
        }
    }
}
