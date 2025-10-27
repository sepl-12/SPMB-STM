<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google OAuth Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Google OAuth authentication and Gmail API integration.
    | These values are used for OAuth authentication and sending emails via Gmail API.
    |
    */

    'client_id' => env('GOOGLE_CLIENT_ID'),

    'client_secret' => env('GOOGLE_CLIENT_SECRET'),

    'refresh_token' => env('GOOGLE_REFRESH_TOKEN'),

    'redirect_uri' => env('GOOGLE_REDIRECT_URI'),

    'sender_email' => env('GOOGLE_SENDER'),

    /*
    |--------------------------------------------------------------------------
    | Gmail API Scopes
    |--------------------------------------------------------------------------
    |
    | The OAuth scopes required for Gmail API access
    |
    */

    'scopes' => [
        'https://www.googleapis.com/auth/gmail.send',
    ],
];
