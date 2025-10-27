<?php

namespace Tests\Unit\Services\Applicant;

use App\Models\Applicant;
use App\Services\Applicant\ApplicantUrlGenerator;
use Tests\TestCase;

class ApplicantUrlGeneratorTest extends TestCase
{
    private ApplicantUrlGenerator $urlGenerator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->urlGenerator = new ApplicantUrlGenerator();
    }

    public function test_get_payment_url_generates_signed_route(): void
    {
        $applicant = new Applicant();
        $applicant->registration_number = 'REG123';

        $url = $this->urlGenerator->getPaymentUrl($applicant);

        $this->assertIsString($url);
        $this->assertStringContainsString('REG123', $url);
        $this->assertStringContainsString('signature', $url);
    }

    public function test_get_payment_url_uses_custom_expiry(): void
    {
        $applicant = new Applicant();
        $applicant->registration_number = 'REG123';

        $url = $this->urlGenerator->getPaymentUrl($applicant, 14);

        $this->assertIsString($url);
        $this->assertStringContainsString('expires', $url);
    }

    public function test_get_status_url_generates_signed_route(): void
    {
        $applicant = new Applicant();
        $applicant->registration_number = 'REG123';

        $url = $this->urlGenerator->getStatusUrl($applicant);

        $this->assertIsString($url);
        $this->assertStringContainsString('REG123', $url);
        $this->assertStringContainsString('signature', $url);
    }

    public function test_get_exam_card_url_generates_signed_route(): void
    {
        $applicant = new Applicant();
        $applicant->registration_number = 'REG123';

        $url = $this->urlGenerator->getExamCardUrl($applicant);

        $this->assertIsString($url);
        $this->assertStringContainsString('REG123', $url);
        $this->assertStringContainsString('signature', $url);
    }

    public function test_get_payment_success_url_generates_signed_route(): void
    {
        $applicant = new Applicant();
        $applicant->registration_number = 'REG123';

        $url = $this->urlGenerator->getPaymentSuccessUrl($applicant);

        $this->assertIsString($url);
        $this->assertStringContainsString('REG123', $url);
        $this->assertStringContainsString('signature', $url);
    }

    public function test_get_all_urls_returns_array_with_all_url_types(): void
    {
        $applicant = new Applicant();
        $applicant->registration_number = 'REG123';

        $urls = $this->urlGenerator->getAllUrls($applicant);

        $this->assertIsArray($urls);
        $this->assertArrayHasKey('payment', $urls);
        $this->assertArrayHasKey('status', $urls);
        $this->assertArrayHasKey('exam_card', $urls);
        $this->assertArrayHasKey('payment_success', $urls);

        foreach ($urls as $url) {
            $this->assertIsString($url);
            $this->assertStringContainsString('REG123', $url);
            $this->assertStringContainsString('signature', $url);
        }
    }
}
