<?php

namespace Tests\Feature\Payment;

use App\Enum\PaymentMethod;
use App\Enum\PaymentStatus;
use App\Mail\ManualPaymentApproved;
use App\Mail\PaymentConfirmed;
use App\Models\Applicant;
use App\Models\AppSetting;
use App\Models\ManualPayment;
use App\Models\Payment;
use App\Models\Wave;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsappGroupAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_gateway_payment_with_capture_status_shows_whatsapp_group_link_on_success_and_status_pages(): void
    {
        AppSetting::set('post_payment_whatsapp_group_url', 'https://chat.whatsapp.com/CaptureInvite123');

        [$applicant, $payment] = $this->createApplicantWithPayment(
            PaymentMethod::QRIS,
            PaymentStatus::CAPTURE,
        );

        $this->get($applicant->getPaymentSuccessUrl())
            ->assertOk()
            ->assertSee('Gabung Grup WhatsApp')
            ->assertSee('https://chat.whatsapp.com/CaptureInvite123', false);

        $this->get($applicant->getStatusUrl())
            ->assertOk()
            ->assertSee('Gabung Grup WhatsApp')
            ->assertSee('https://chat.whatsapp.com/CaptureInvite123', false);

        $html = (new PaymentConfirmed($payment))->render();

        $this->assertStringContainsString('Gabung Grup WhatsApp', $html);
        $this->assertStringContainsString('https://chat.whatsapp.com/CaptureInvite123', $html);
    }

    public function test_manual_payment_pending_verification_does_not_show_whatsapp_group_link(): void
    {
        AppSetting::set('post_payment_whatsapp_group_url', 'https://chat.whatsapp.com/ManualInvite123');

        [$applicant, $payment] = $this->createApplicantWithPayment(
            PaymentMethod::MANUAL_TRANSFER,
            PaymentStatus::PENDING_VERIFICATION,
        );

        ManualPayment::create([
            'payment_id' => $payment->id,
            'applicant_id' => $applicant->id,
            'proof_image_path' => 'payment-proofs/test.png',
            'upload_datetime' => now(),
            'approval_status' => 'pending',
            'paid_amount' => 300000,
        ]);

        $this->get($applicant->getStatusUrl())
            ->assertOk()
            ->assertDontSee('Gabung Grup WhatsApp');
    }

    public function test_manual_payment_approved_shows_whatsapp_group_link_on_status_page_and_email(): void
    {
        AppSetting::set('post_payment_whatsapp_group_url', 'https://chat.whatsapp.com/ApprovedInvite123');

        [$applicant, $payment] = $this->createApplicantWithPayment(
            PaymentMethod::MANUAL_TRANSFER,
            PaymentStatus::SETTLEMENT,
        );

        $manualPayment = ManualPayment::create([
            'payment_id' => $payment->id,
            'applicant_id' => $applicant->id,
            'proof_image_path' => 'payment-proofs/test.png',
            'upload_datetime' => now()->subHour(),
            'approval_status' => 'approved',
            'approved_at' => now(),
            'paid_amount' => 300000,
        ]);

        $this->get($applicant->getStatusUrl())
            ->assertOk()
            ->assertSee('Gabung Grup WhatsApp')
            ->assertSee('https://chat.whatsapp.com/ApprovedInvite123', false);

        $html = (new ManualPaymentApproved($manualPayment))->render();

        $this->assertStringContainsString('Gabung Grup WhatsApp', $html);
        $this->assertStringContainsString('https://chat.whatsapp.com/ApprovedInvite123', $html);
    }

    /**
     * @return array{Applicant, Payment}
     */
    private function createApplicantWithPayment(PaymentMethod $method, PaymentStatus $status): array
    {
        $wave = Wave::create([
            'wave_name' => 'Gelombang 1',
            'wave_code' => 'G1',
            'start_datetime' => now()->subDay(),
            'end_datetime' => now()->addDay(),
            'quota_limit' => 100,
            'registration_fee_amount' => 300000,
            'is_active' => true,
        ]);

        $applicant = Applicant::create([
            'registration_number' => 'REG-'.strtoupper(fake()->bothify('#####')),
            'applicant_full_name' => fake()->name(),
            'applicant_nisn' => fake()->numerify('##########'),
            'applicant_phone_number' => fake()->phoneNumber(),
            'applicant_email_address' => fake()->safeEmail(),
            'chosen_major_name' => 'TKJ',
            'wave_id' => $wave->id,
            'registered_datetime' => now(),
        ]);

        $payment = Payment::create([
            'applicant_id' => $applicant->id,
            'payment_gateway_name' => $method === PaymentMethod::MANUAL_TRANSFER ? 'manual' : 'midtrans',
            'merchant_order_code' => 'ORD-'.strtoupper(fake()->bothify('#####')),
            'paid_amount_total' => 300000,
            'currency_code' => 'IDR',
            'payment_method_name' => $method->value,
            'payment_status_name' => $status->value,
            'status_updated_datetime' => now(),
            'gateway_payload_json' => [],
        ]);

        return [$applicant->fresh(['latestPayment', 'payments', 'wave']), $payment->fresh()];
    }
}
