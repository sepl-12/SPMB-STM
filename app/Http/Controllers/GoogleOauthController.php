<?php

namespace App\Http\Controllers;

use App\Services\GoogleTokenManager;
use Google\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GoogleOauthController extends Controller
{
    private readonly array $googleConfig;
    private const STATE_KEY = 'google_oauth_state';

    public function __construct(
        private readonly GoogleTokenManager $tokenManager,
        array $config = []
    ) {
        // Use injected config or fallback to config helper
        $this->googleConfig = !empty($config) ? $config : config('google', []);
    }

    private function client(?string $state = null): Client
    {
        $client = new Client();
        $client->setClientId($this->googleConfig['client_id']);
        $client->setClientSecret($this->googleConfig['client_secret']);
        $client->setRedirectUri($this->googleConfig['redirect_uri']);
        $client->setAccessType('offline');      // penting untuk refresh_token
        $client->setPrompt('consent');          // pastikan user memberi consent
        $client->setIncludeGrantedScopes(true);

        // Use scopes from config
        foreach ($this->googleConfig['scopes'] ?? ['https://www.googleapis.com/auth/gmail.send'] as $scope) {
            $client->addScope($scope);
        }

        if ($state) {
            $client->setState($state);
        }

        return $client;
    }

    public function redirect()
    {
        $state = Str::random(40);
        session([self::STATE_KEY => $state]);

        return redirect($this->client($state)->createAuthUrl());
    }

    public function callback(Request $request)
    {
        $expectedState = session(self::STATE_KEY);
        $receivedState = $request->input('state');

        if (!$expectedState || !$receivedState || !hash_equals($expectedState, $receivedState)) {
            abort(403, 'Invalid OAuth state');
        }

        session()->forget(self::STATE_KEY);

        $client = $this->client();

        if (!$request->has('code')) {
            abort(400, 'Authorization code not provided');
        }

        $token = $client->fetchAccessTokenWithAuthCode($request->get('code'));
        // $token memuat access_token + refresh_token (sekali ini)
        // Simpan refresh_token ke storage aman (env/secret manager/DB terenkripsi)
        $refreshToken = $token['refresh_token'] ?? null;

        if (!$refreshToken) {
            return response('Tidak ada refresh_token. Coba ulangi, pastikan prompt consent tampil.', 400);
        }

        $this->tokenManager->storeRefreshToken($refreshToken);

        return response('Refresh token berhasil disimpan secara aman.');
    }
}
