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
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;

class PaymentLinkService
{
    public function __construct(private readonly CreatePaymentLinkAction $createPaymentLinkAction)
    {
    }

    public function showForm(string $registrationNumber): PaymentLinkResult
    {
        $applicant = $this->findApplicant($registrationNumber, ['wave', 'payments', 'latestPayment']);

        if ($applicant->hasSuccessfulPayment()) {
            $latestSuccess = $applicant->payments()->successful()->latest()->first();

            return new PaymentLinkResult(
                applicant: $applicant,
                payment: $latestSuccess,
                snapToken: null,
                redirectRoute: 'payment.success',
                redirectParams: ['registration_number' => $registrationNumber],
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
                redirectRoute: 'payment.show',
                redirectParams: ['registration_number' => $registrationNumber],
                flash: ['info' => 'Silakan lanjutkan pembayaran Anda.']
            );
        }

        if ($latestPayment->payment_status_name === PaymentStatus::SETTLEMENT) {
            return new PaymentLinkResult(
                applicant: $applicant,
                payment: $latestPayment,
                snapToken: null,
                redirectRoute: 'payment.success',
                redirectParams: ['registration_number' => $registrationNumber],
                flash: ['success' => 'Pembayaran Anda sudah berhasil!']
            );
        }

        return new PaymentLinkResult(
            applicant: $applicant,
            payment: $latestPayment,
            snapToken: Arr::get($latestPayment->gateway_payload_json, 'snap_token'),
            redirectRoute: 'payment.show',
            redirectParams: ['registration_number' => $registrationNumber],
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
        $recordedEmail = $applicant->getLatestAnswerForField('email') ?? '';

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

            if ($snapToken) {
                return new SnapTransaction(
                    orderId: $existingPayment->merchant_order_code,
                    snapToken: $snapToken,
                    payment: $existingPayment,
                );
            }
        }

        return $this->createPaymentLinkAction->execute($applicant);
    }
}
