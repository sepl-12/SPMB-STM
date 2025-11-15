<?php

namespace App\Services;

use App\Settings\SettingsRepositoryInterface;
use Illuminate\Support\Facades\Log;

class GoogleTokenManager
{
    private const TOKEN_KEY = 'google_refresh_token_encrypted';

    public function __construct(private readonly SettingsRepositoryInterface $settings)
    {
    }

    public function storeRefreshToken(string $token): void
    {
        try {
            $this->settings->set(self::TOKEN_KEY, encrypt($token));
        } catch (\Throwable $e) {
            Log::error('Failed to persist Google refresh token', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function getRefreshToken(): ?string
    {
        try {
            $encrypted = $this->settings->get(self::TOKEN_KEY);
        } catch (\Throwable $e) {
            Log::warning('Unable to read Google refresh token from settings', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if (!$encrypted) {
            return null;
        }

        try {
            return decrypt($encrypted);
        } catch (\Throwable $e) {
            Log::warning('Failed to decrypt stored Google refresh token', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
