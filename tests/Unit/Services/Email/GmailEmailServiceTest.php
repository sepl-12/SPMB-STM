<?php

namespace Tests\Unit\Services\Email;

use App\Services\Email\GmailEmailService;
use App\Services\GmailMailableSender;
use Tests\TestCase;
use Mockery;

class GmailEmailServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_can_instantiate_with_config(): void
    {
        $mockSender = Mockery::mock(GmailMailableSender::class);

        $config = [
            'client_id' => 'test-id',
            'client_secret' => 'test-secret',
            'refresh_token' => 'test-token',
            'sender_email' => 'test@example.com',
        ];

        $service = new GmailEmailService($mockSender, $config);

        $this->assertInstanceOf(GmailEmailService::class, $service);
    }

    public function test_is_healthy_returns_true_with_valid_config(): void
    {
        $mockSender = Mockery::mock(GmailMailableSender::class);

        $config = [
            'client_id' => 'test-id',
            'client_secret' => 'test-secret',
            'refresh_token' => 'test-token',
        ];

        $service = new GmailEmailService($mockSender, $config);

        $this->assertTrue($service->isHealthy());
    }

    public function test_is_healthy_returns_false_with_missing_credentials(): void
    {
        $mockSender = Mockery::mock(GmailMailableSender::class);

        $config = [
            'client_id' => 'test-id',
            'client_secret' => '', // Empty
            'refresh_token' => 'test-token',
        ];

        $service = new GmailEmailService($mockSender, $config);

        $this->assertFalse($service->isHealthy());
    }

    public function test_is_healthy_returns_false_with_empty_config(): void
    {
        $mockSender = Mockery::mock(GmailMailableSender::class);

        // Pass explicit empty values instead of empty array
        $config = [
            'client_id' => '',
            'client_secret' => '',
            'refresh_token' => '',
        ];

        $service = new GmailEmailService($mockSender, $config);

        $this->assertFalse($service->isHealthy());
    }

    public function test_get_service_name_returns_gmail_api(): void
    {
        $mockSender = Mockery::mock(GmailMailableSender::class);
        $config = ['client_id' => 'test'];

        $service = new GmailEmailService($mockSender, $config);

        $this->assertEquals('gmail_api', $service->getServiceName());
    }

    public function test_can_be_resolved_from_container(): void
    {
        $service = app(GmailEmailService::class);

        $this->assertInstanceOf(GmailEmailService::class, $service);
    }
}
