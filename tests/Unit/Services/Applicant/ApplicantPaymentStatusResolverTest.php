<?php

namespace Tests\Unit\Services\Applicant;

use App\Enum\PaymentStatus;
use App\Models\Applicant;
use App\Models\Payment;
use App\Services\Applicant\ApplicantPaymentStatusResolver;
use Tests\TestCase;

class ApplicantPaymentStatusResolverTest extends TestCase
{
    private ApplicantPaymentStatusResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new ApplicantPaymentStatusResolver();
    }

    public function test_get_latest_status_returns_null_when_no_payment(): void
    {
        $applicant = new Applicant();
        $applicant->setRelation('latestPayment', null);

        $status = $this->resolver->getLatestStatus($applicant);

        $this->assertNull($status);
    }

    public function test_get_latest_status_returns_payment_status(): void
    {
        $payment = new Payment();
        $payment->payment_status_name = PaymentStatus::SETTLEMENT;

        $applicant = new Applicant();
        $applicant->setRelation('latestPayment', $payment);

        $status = $this->resolver->getLatestStatus($applicant);

        $this->assertEquals(PaymentStatus::SETTLEMENT, $status);
    }

    public function test_has_successful_payment_returns_true_for_settlement(): void
    {
        $payment = new Payment();
        $payment->payment_status_name = PaymentStatus::SETTLEMENT;

        $applicant = new Applicant();
        $applicant->setRelation('latestPayment', $payment);

        $this->assertTrue($this->resolver->hasSuccessfulPayment($applicant));
    }

    public function test_has_successful_payment_returns_false_for_pending(): void
    {
        $payment = new Payment();
        $payment->payment_status_name = PaymentStatus::PENDING;

        $applicant = new Applicant();
        $applicant->setRelation('latestPayment', $payment);

        $this->assertFalse($this->resolver->hasSuccessfulPayment($applicant));
    }

    public function test_has_pending_payment_returns_true_for_pending(): void
    {
        $payment = new Payment();
        $payment->payment_status_name = PaymentStatus::PENDING;

        $applicant = new Applicant();
        $applicant->setRelation('latestPayment', $payment);

        $this->assertTrue($this->resolver->hasPendingPayment($applicant));
    }

    public function test_has_pending_payment_returns_true_when_no_payment(): void
    {
        $applicant = new Applicant();
        $applicant->setRelation('latestPayment', null);

        $this->assertTrue($this->resolver->hasPendingPayment($applicant));
    }

    public function test_has_failed_payment_returns_true_for_failure(): void
    {
        $payment = new Payment();
        $payment->payment_status_name = PaymentStatus::FAILURE;

        $applicant = new Applicant();
        $applicant->setRelation('latestPayment', $payment);

        $this->assertTrue($this->resolver->hasFailedPayment($applicant));
    }

    public function test_get_status_value_returns_string(): void
    {
        $payment = new Payment();
        $payment->payment_status_name = PaymentStatus::SETTLEMENT;

        $applicant = new Applicant();
        $applicant->setRelation('latestPayment', $payment);

        $value = $this->resolver->getStatusValue($applicant);

        $this->assertEquals('settlement', $value);
    }

    public function test_get_status_badge_returns_array_for_settlement(): void
    {
        $payment = new Payment();
        $payment->payment_status_name = PaymentStatus::SETTLEMENT;

        $applicant = new Applicant();
        $applicant->setRelation('latestPayment', $payment);

        $badge = $this->resolver->getStatusBadge($applicant);

        $this->assertIsArray($badge);
        $this->assertArrayHasKey('label', $badge);
        $this->assertArrayHasKey('color', $badge);
        $this->assertArrayHasKey('value', $badge);
        $this->assertEquals('settlement', $badge['value']);
    }

    public function test_get_status_badge_returns_default_when_no_payment(): void
    {
        $applicant = new Applicant();
        $applicant->setRelation('latestPayment', null);

        $badge = $this->resolver->getStatusBadge($applicant);

        $this->assertEquals('Belum Bayar', $badge['label']);
        $this->assertEquals('warning', $badge['color']);
        $this->assertEquals('unpaid', $badge['value']);
    }

    public function test_batch_get_statuses_returns_array_keyed_by_id(): void
    {
        $payment1 = new Payment();
        $payment1->payment_status_name = PaymentStatus::SETTLEMENT;

        $payment2 = new Payment();
        $payment2->payment_status_name = PaymentStatus::PENDING;

        $applicant1 = new Applicant();
        $applicant1->id = 1;
        $applicant1->setRelation('latestPayment', $payment1);

        $applicant2 = new Applicant();
        $applicant2->id = 2;
        $applicant2->setRelation('latestPayment', $payment2);

        $applicants = collect([$applicant1, $applicant2]);

        $statuses = $this->resolver->batchGetStatuses($applicants);

        $this->assertIsArray($statuses);
        $this->assertCount(2, $statuses);
        $this->assertEquals(PaymentStatus::SETTLEMENT, $statuses[1]);
        $this->assertEquals(PaymentStatus::PENDING, $statuses[2]);
    }
}
