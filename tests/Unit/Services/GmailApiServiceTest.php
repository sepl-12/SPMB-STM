<?php

namespace Tests\Unit\Services;

use App\Services\GmailApiService;
use Tests\TestCase;

class GmailApiServiceTest extends TestCase
{
    public function test_can_instantiate_with_config(): void
    {
        $config = [
            'client_id' => 'test-client-id',
            'client_secret' => 'test-secret',
            'refresh_token' => 'test-refresh-token',
            'scopes' => ['https://www.googleapis.com/auth/gmail.send'],
        ];

        $service = new GmailApiService($config);

        $this->assertInstanceOf(GmailApiService::class, $service);
    }

    public function test_throws_exception_when_refresh_token_empty(): void
    {
        $config = [
            'client_id' => 'test-client-id',
            'client_secret' => 'test-secret',
            'refresh_token' => '', // Empty
            'scopes' => ['https://www.googleapis.com/auth/gmail.send'],
        ];

        $service = new GmailApiService($config);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('GOOGLE_REFRESH_TOKEN kosong/tidak di-set.');

        // This will trigger the googleClient() method internally
        // We can't test sendRaw directly without valid OAuth, but we can test the validation
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('googleClient');
        $method->setAccessible(true);
        $method->invoke($service);
    }

    public function test_b64url_encoding(): void
    {
        $input = 'Hello World!';
        $encoded = GmailApiService::b64url($input);

        // Base64URL should not have padding and use - and _ instead of + and /
        $this->assertStringNotContainsString('+', $encoded);
        $this->assertStringNotContainsString('/', $encoded);
        $this->assertStringNotContainsString('=', $encoded);
    }

    public function test_can_be_resolved_from_container(): void
    {
        // Test that service can be resolved from container with config
        $service = app(GmailApiService::class);

        $this->assertInstanceOf(GmailApiService::class, $service);
    }
}
