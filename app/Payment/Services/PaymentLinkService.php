<?php

namespace App\Payment\Services;

use App\Enum\PaymentStatus;
use App\Models\Applicant;
use App\Models\Payment;
use App\Payment\Actions\CreatePaymentLinkAction;
use App\Payment\DTO\PaymentLinkResult;
use App\Payment\DTO\SnapTransaction;
use App\Payment\Exceptions\PaymentEmailMismatchException;
use App\Payment\Exceptions\PaymentNotFoundException;
use App\Services\Applicant\ApplicantUrlGenerator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;

class PaymentLinkService
{
    public function __construct(
        private readonly CreatePaymentLinkAction $createPaymentLinkAction,
        private readonly ApplicantUrlGenerator $applicantUrlGenerator,
    ) {}

    public function showForm(string $registrationNumber): PaymentLinkResult
    {
        $applicant = $this->findApplicant($registrationNumber, ['wave', 'payments', 'latestPayment']);

        if ($applicant->hasSuccessfulPayment()) {
            $latestSuccess = $applicant->payments()->successful()->latest()->first();

            return new PaymentLinkResult(
                applicant: $applicant,
                payment: $latestSuccess,
                snapToken: null,
                redirectUrl: $this->applicantUrlGenerator->getPaymentSuccessUrl($applicant),
                flash: ['message' => 'Pembayaran Anda sudah berhasil.']
            );
        }

        $snapTransaction = $this->resolveSnapTransaction($applicant);

        return new PaymentLinkResult(
            applicant: $applicant,
            payment: $snapTransaction->payment,
            snapToken: $snapTransaction->snapToken,
        );
    }

    public function findPayment(string $registrationNumber, string $email): PaymentLinkResult
    {
        $applicant = $this->findApplicant($registrationNumber, ['wave', 'payments']);

        $this->assertEmailMatches($applicant, $email);

        $latestPayment = $applicant->payments()->latest()->first();

        if (!$latestPayment) {
            return new PaymentLinkResult(
                applicant: $applicant,
                payment: null,
                snapToken: null,
                redirectUrl: $this->applicantUrlGenerator->getPaymentUrl($applicant),
                flash: ['info' => 'Silakan lanjutkan pembayaran Anda.']
            );
        }

        if ($latestPayment->payment_status_name === PaymentStatus::SETTLEMENT) {
            return new PaymentLinkResult(
                applicant: $applicant,
                payment: $latestPayment,
                snapToken: null,
                redirectUrl: $this->applicantUrlGenerator->getPaymentSuccessUrl($applicant),
                flash: ['success' => 'Pembayaran Anda sudah berhasil!']
            );
        }

        // Check if token is expired (older than 23 hours)
        // If expired, redirect to payment page which will generate new token
        $isTokenFresh = $latestPayment->created_at->diffInHours(now()) < 23;

        if (!$isTokenFresh) {
            // Token expired, redirect to payment page to get fresh token
            return new PaymentLinkResult(
                applicant: $applicant,
                payment: $latestPayment,
                snapToken: null,
                redirectUrl: $this->applicantUrlGenerator->getPaymentUrl($applicant),
                flash: ['warning' => 'Token pembayaran sudah kadaluarsa. Silakan lakukan pembayaran ulang.']
            );
        }

        return new PaymentLinkResult(
            applicant: $applicant,
            payment: $latestPayment,
            snapToken: Arr::get($latestPayment->gateway_payload_json, 'snap_token'),
            redirectUrl: $this->applicantUrlGenerator->getPaymentUrl($applicant),
            flash: ['info' => 'Lanjutkan pembayaran Anda.']
        );
    }

    public function resendLink(string $registrationNumber, string $email): PaymentLinkResult
    {
        $applicant = $this->findApplicant($registrationNumber, ['wave', 'payments']);

        $this->assertEmailMatches($applicant, $email);

        $snapTransaction = $this->resolveSnapTransaction($applicant);

        return new PaymentLinkResult(
            applicant: $applicant,
            payment: $snapTransaction->payment,
            snapToken: $snapTransaction->snapToken,
        );
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

    protected function assertEmailMatches(Applicant $applicant, string $email): void
    {
        $recordedEmail = $applicant->applicant_email_address ?? '';

        if (strtolower($recordedEmail) !== strtolower($email)) {
            throw new PaymentEmailMismatchException('Email tidak sesuai dengan data pendaftaran.');
        }
    }

    protected function resolveSnapTransaction(Applicant $applicant): SnapTransaction
    {
        $existingPayment = $applicant->payments()
            ->withStatus(PaymentStatus::PENDING)
            ->latest()
            ->first();

        if ($existingPayment) {
            $snapToken = Arr::get($existingPayment->gateway_payload_json, 'snap_token');

            // Check if token exists and payment is still fresh (less than 23 hours old)
            // Midtrans Snap Token expires after 24 hours, we use 23 hours for safety margin
            $isTokenFresh = $existingPayment->created_at->diffInHours(now()) < 23;

            if ($snapToken && $isTokenFresh) {
                return new SnapTransaction(
                    orderId: $existingPayment->merchant_order_code,
                    snapToken: $snapToken,
                    payment: $existingPayment,
                );
            }

            // Token expired or not found, mark old payment as expired and create new one
            if (!$isTokenFresh) {
                $existingPayment->update([
                    'payment_status_name' => PaymentStatus::EXPIRE,
                    'status_updated_datetime' => now(),
                ]);
            }
        }

        return $this->createPaymentLinkAction->execute($applicant);
    }
}
