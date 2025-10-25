<?php

namespace Tests\Unit\Settings;

use App\Settings\SettingsRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CacheSettingsRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected SettingsRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(SettingsRepositoryInterface::class);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_sets_and_gets_values(): void
    {
        $this->repository->set('contact_email', 'test@example.com');

        $this->assertSame('test@example.com', $this->repository->get('contact_email'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_default_when_key_missing(): void
    {
        $this->assertSame('default', $this->repository->get('missing_key', 'default'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_forget_keys(): void
    {
        $this->repository->set('contact_phone', '08123456789');
        $this->repository->forget('contact_phone');

        $this->assertNull($this->repository->get('contact_phone'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_group_values(): void
    {
        $this->repository->set('social_facebook_url', 'fb.com');
        $this->repository->set('social_instagram_handle', '@insta');

        $group = $this->repository->getGroup('social');

        $this->assertArrayHasKey('social_facebook_url', $group);
        $this->assertEquals('fb.com', $group['social_facebook_url']);
    }
}
