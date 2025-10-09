<?php

return [
    'server_key' => env('MIDTRANS_SERVER_KEY', 'your-server-key-here'),
    'client_key' => env('MIDTRANS_CLIENT_KEY', 'your-client-key-here'),
    'is_production' => env('IS_PRODUCTION', false),
    'is_sanitized' => true,
    'is_3ds' => true,
];