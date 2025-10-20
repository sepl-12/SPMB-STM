<?php

namespace App\Http\Controllers;

use Google\Client;
use Illuminate\Http\Request;

class GoogleOauthController extends Controller
{
    private function client(): Client
    {
        $client = new Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
        $client->setAccessType('offline');      // penting untuk refresh_token
        $client->setPrompt('consent');          // pastikan user memberi consent
        $client->setIncludeGrantedScopes(true);
        $client->addScope('https://www.googleapis.com/auth/gmail.send'); // kirim email saja
        return $client;
    }

    public function redirect()
    {
        return redirect($this->client()->createAuthUrl());
    }

    public function callback(Request $request)
    {
        $client = $this->client();

        if (!$request->has('code')) {
            abort(400, 'Authorization code not provided');
        }

        $token = $client->fetchAccessTokenWithAuthCode($request->get('code'));
        // $token memuat access_token + refresh_token (sekali ini)
        // Simpan refresh_token ke storage aman (env/secret manager/DB terenkripsi)
        $refreshToken = $token['refresh_token'] ?? null;

        if (!$refreshToken) {
            return 'Tidak ada refresh_token. Coba ulangi, pastikan prompt consent tampil.';
        }

        // Untuk demo cepat: tampilkan; PRODUKSI: simpan ke DB / secret manager
        return 'Refresh Token: ' . $refreshToken;
    }
}
