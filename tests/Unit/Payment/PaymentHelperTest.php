<?php

namespace Tests\Unit\Payment;

use App\Enum\PaymentMethod;
use App\Enum\PaymentStatus;
use App\Helpers\PaymentHelper;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class PaymentHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('payment.status_mapping', [
            'settlement' => PaymentStatus::SETTLEMENT,
            'capture' => [
                'accept' => PaymentStatus::SETTLEMENT,
                'challenge' => PaymentStatus::CAPTURE,
            ],
        ]);

        Config::set('payment.payment_methods', [
            PaymentMethod::CREDIT_CARD->value => [
                'label' => 'Kartu Kredit',
                'category' => 'card',
                'fee' => ['percentage' => 0.029, 'flat' => 2000],
                'expiry' => 60,
            ],
            PaymentMethod::BCA_VA->value => [
                'label' => 'BCA VA',
                'category' => 'virtual_account',
                'fee' => ['flat' => 4000],
                'expiry' => 1440,
            ],
        ]);

        Config::set('payment.instructions', [
            'card' => [
                'title' => 'Instruksi Kartu',
                'steps' => ['Langkah 1', 'Langkah 2'],
            ],
            'default' => [
                'title' => 'Instruksi Default',
                'steps' => ['Langkah umum'],
            ],
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_maps_midtrans_status_via_config(): void
    {
        $result = PaymentHelper::mapMidtransStatus('settlement');
        $this->assertEquals(PaymentStatus::SETTLEMENT, $result);

        $capture = PaymentHelper::mapMidtransStatus('capture', 'challenge');
        $this->assertEquals(PaymentStatus::CAPTURE, $capture);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_maps_midtrans_payment_type_via_config(): void
    {
        $result = PaymentHelper::mapMidtransPaymentType('credit_card');
        $this->assertEquals(PaymentMethod::CREDIT_CARD, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_method_options_from_config(): void
    {
        $options = PaymentHelper::getMethodOptions();

        $this->assertArrayHasKey(PaymentMethod::CREDIT_CARD->value, $options);
        $this->assertEquals('Kartu Kredit', $options[PaymentMethod::CREDIT_CARD->value]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_calculates_fee_based_on_config(): void
    {
        $fee = PaymentHelper::calculateFees(100_000, PaymentMethod::CREDIT_CARD);

        $expected = round(100_000 * 0.029 + 2000);
        $this->assertEquals($expected, $fee);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_instructions_from_config(): void
    {
        $instructions = PaymentHelper::getPaymentInstructions(PaymentMethod::CREDIT_CARD);

        $this->assertEquals('Instruksi Kartu', $instructions['title']);
        $this->assertCount(2, $instructions['steps']);
    }
}
