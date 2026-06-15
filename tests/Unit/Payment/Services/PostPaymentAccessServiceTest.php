<?php

namespace Tests\Unit\Payment\Services;

use App\Enum\PaymentMethod;
use App\Enum\PaymentStatus;
use App\Models\Payment;
use App\Payment\Services\PostPaymentAccessService;
use App\Settings\SettingsRepositoryInterface;
use Mockery;
use Tests\TestCase;

class PostPaymentAccessServiceTest extends TestCase
{
    public function test_gateway_payment_with_settlement_status_gets_whatsapp_group_access(): void
    {
        $service = $this->makeServiceWithUrl('https://chat.whatsapp.com/InviteCode123');
        $payment = $this->makePayment(PaymentMethod::QRIS, PaymentStatus::SETTLEMENT);

        $this->assertTrue($service->canAccessWhatsappGroup($payment));
        $this->assertSame('https://chat.whatsapp.com/InviteCode123', $service->getWhatsappGroupUrl($payment));
    }

    public function test_gateway_payment_with_capture_status_gets_whatsapp_group_access(): void
    {
        $service = $this->makeServiceWithUrl('https://chat.whatsapp.com/CaptureCode123');
        $payment = $this->makePayment(PaymentMethod::CREDIT_CARD, PaymentStatus::CAPTURE);

        $this->assertTrue($service->canAccessWhatsappGroup($payment));
        $this->assertSame('https://chat.whatsapp.com/CaptureCode123', $service->getWhatsappGroupUrl($payment));
    }

    public function test_manual_payment_requires_admin_approval_before_getting_whatsapp_group_access(): void
    {
        $service = $this->makeServiceWithUrl('https://chat.whatsapp.com/ManualCode123');
        $payment = $this->makePayment(PaymentMethod::MANUAL_TRANSFER, PaymentStatus::PENDING_VERIFICATION);

        $this->assertFalse($service->canAccessWhatsappGroup($payment));
        $this->assertNull($service->getWhatsappGroupUrl($payment));
    }

    public function test_invalid_whatsapp_group_setting_is_ignored(): void
    {
        $service = $this->makeServiceWithUrl('https://example.com/not-whatsapp');
        $payment = $this->makePayment(PaymentMethod::QRIS, PaymentStatus::SETTLEMENT);

        $this->assertTrue($service->canAccessWhatsappGroup($payment));
        $this->assertNull($service->getWhatsappGroupUrl($payment));
    }

    private function makeServiceWithUrl(string $url): PostPaymentAccessService
    {
        $settingsRepository = Mockery::mock(SettingsRepositoryInterface::class);
        $settingsRepository
            ->shouldReceive('get')
            ->zeroOrMoreTimes()
            ->with('post_payment_whatsapp_group_url', '')
            ->andReturn($url);

        return new PostPaymentAccessService($settingsRepository);
    }

    private function makePayment(PaymentMethod $method, PaymentStatus $status): Payment
    {
        $payment = new Payment;
        $payment->payment_method_name = $method;
        $payment->payment_status_name = $status;

        return $payment;
    }
}
