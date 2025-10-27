<?php

namespace App\Services;

use Google\Client;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;
use Google\Service\Exception as GoogleServiceException;
use Illuminate\Support\Facades\Log;

class GmailApiService
{
    /**
     * Google OAuth configuration
     */
    public function __construct(
        private readonly array $config
    ) {}

    private function clean(string $s): string
    {
        // hilangkan whitespace tersembunyi (spasi/newline/kutip)
        $s = trim($s);
        // buang semua whitespace di tengah (sering ada \n dari copy)
        return preg_replace('/\s+/', '', $s);
    }

    private function googleClient(): Client
    {
        $clientId     = $this->config['client_id'];
        $clientSecret = $this->config['client_secret'];
        $refreshToken = $this->clean((string) ($this->config['refresh_token'] ?? ''));

        if ($refreshToken === '') {
            throw new \RuntimeException('GOOGLE_REFRESH_TOKEN kosong/tidak di-set.');
        }

        $client = new Client();
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setAccessType('offline');

        // Use scopes from config
        foreach ($this->config['scopes'] ?? ['https://www.googleapis.com/auth/gmail.send'] as $scope) {
            $client->addScope($scope);
        }

        // mint access_token baru dari refresh_token
        $token = $client->fetchAccessTokenWithRefreshToken($refreshToken);

        if (isset($token['error'])) {
            // log seluruh payload biar ketahuan jelasnya
            Log::error('Gmail OAuth refresh error', $token);
            $desc = $token['error_description'] ?? $token['error'];
            throw new \RuntimeException("OAuth refresh gagal: {$desc}");
        }

        // simpan access token yang baru ke client
        $client->setAccessToken($token);

        return $client;
    }

    /** Kirim RAW base64url (RFC 2822) */
    public function sendRaw(string $rawBase64Url): string
    {
        try {
            $service = new Gmail($this->googleClient());
            $msg = new Message();
            $msg->setRaw($rawBase64Url);
            $sent = $service->users_messages->send('me', $msg);
            return $sent->getId();
        } catch (GoogleServiceException $e) {
            Log::error('Gmail API send error', [
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Gagal Mengirim Email: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /** Base64URL helper */
    public static function b64url(string $input): string
    {
        return rtrim(strtr(base64_encode($input), '+/', '-_'), '=');
    }
}
