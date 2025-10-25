<?php

return [
    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY', env('MIDTRANS_SERVERKEY')), // fallback env lama
        'client_key' => env('MIDTRANS_CLIENT_KEY', env('MIDTRANS_CLIENTKEY')),
        'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),
        'is_sanitized' => (bool) env('MIDTRANS_IS_SANITIZED', true),
        'is_3ds' => (bool) env('MIDTRANS_IS_3DS', true),
    ],

    'status_mapping' => [
        'capture' => [
            'accept' => App\Enum\PaymentStatus::SETTLEMENT,
            'challenge' => App\Enum\PaymentStatus::CAPTURE,
        ],
        'settlement' => App\Enum\PaymentStatus::SETTLEMENT,
        'pending' => App\Enum\PaymentStatus::PENDING,
        'cancel' => App\Enum\PaymentStatus::CANCEL,
        'deny' => App\Enum\PaymentStatus::DENY,
        'expire' => App\Enum\PaymentStatus::EXPIRE,
        'failure' => App\Enum\PaymentStatus::FAILURE,
        'failed' => App\Enum\PaymentStatus::FAILURE,
    ],

    'payment_methods' => [
        // method => [label, category, fee, expiry_minutes]
        App\Enum\PaymentMethod::CREDIT_CARD->value => [
            'label' => 'Kartu Kredit',
            'category' => 'card',
            'fee' => ['percentage' => 0.029, 'flat' => 2000],
            'expiry' => 60,
        ],
        App\Enum\PaymentMethod::BCA_VA->value => [
            'label' => 'BCA Virtual Account',
            'category' => 'virtual_account',
            'fee' => ['flat' => 4000],
            'expiry' => 24 * 60,
        ],
        App\Enum\PaymentMethod::BNI_VA->value => [
            'label' => 'BNI Virtual Account',
            'category' => 'virtual_account',
            'fee' => ['flat' => 4000],
            'expiry' => 24 * 60,
        ],
        App\Enum\PaymentMethod::BRI_VA->value => [
            'label' => 'BRI Virtual Account',
            'category' => 'virtual_account',
            'fee' => ['flat' => 4000],
            'expiry' => 24 * 60,
        ],
        App\Enum\PaymentMethod::MANDIRI_VA->value => [
            'label' => 'Mandiri Virtual Account',
            'category' => 'virtual_account',
            'fee' => ['flat' => 4000],
            'expiry' => 24 * 60,
        ],
        App\Enum\PaymentMethod::GOPAY->value => [
            'label' => 'GoPay',
            'category' => 'ewallet',
            'fee' => ['percentage' => 0.02],
            'expiry' => 15,
        ],
        App\Enum\PaymentMethod::SHOPEEPAY->value => [
            'label' => 'ShopeePay',
            'category' => 'ewallet',
            'fee' => ['percentage' => 0.02],
            'expiry' => 15,
        ],
        App\Enum\PaymentMethod::QRIS->value => [
            'label' => 'QRIS',
            'category' => 'qr_code',
            'fee' => ['percentage' => 0.007],
            'expiry' => 30,
        ],
        App\Enum\PaymentMethod::ALFAMART->value => [
            'label' => 'Alfamart',
            'category' => 'convenience_store',
            'fee' => ['flat' => 5000],
            'expiry' => 3 * 24 * 60,
        ],
        App\Enum\PaymentMethod::INDOMARET->value => [
            'label' => 'Indomaret',
            'category' => 'convenience_store',
            'fee' => ['flat' => 5000],
            'expiry' => 3 * 24 * 60,
        ],
        App\Enum\PaymentMethod::ECHANNEL->value => [
            'label' => 'Channel Lain',
            'category' => 'other',
            'fee' => ['flat' => 0],
            'expiry' => 60,
        ],
    ],

    'instructions' => [
        'virtual_account' => [
            'title' => 'Panduan Pembayaran Virtual Account',
            'steps' => [
                'Buka aplikasi atau ATM bank sesuai virtual account yang dipilih.',
                'Pilih menu pembayaran/transfer virtual account.',
                'Masukkan nomor virtual account yang tertera.',
                'Konfirmasi jumlah tagihan dan selesaikan transaksi.',
            ],
        ],
        'ewallet' => [
            'title' => 'Panduan Pembayaran E-Wallet',
            'steps' => [
                'Buka aplikasi e-wallet (GoPay/ShopeePay).',
                'Pilih menu bayar/scan dan masukkan kode pembayaran.',
                'Konfirmasi jumlah pembayaran dan masukkan PIN.',
            ],
        ],
        'qr_code' => [
            'title' => 'Panduan Pembayaran QRIS',
            'steps' => [
                'Buka aplikasi mobile banking atau e-wallet.',
                'Pilih menu scan QR dan arahkan ke QR yang tersedia.',
                'Konfirmasi nominal dan selesaikan pembayaran.',
            ],
        ],
        'convenience_store' => [
            'title' => 'Panduan Pembayaran Gerai Retail',
            'steps' => [
                'Kunjungi gerai yang dipilih (Alfamart/Indomaret).',
                'Tunjukkan kode pembayaran kepada kasir.',
                'Lakukan pembayaran sesuai tagihan dan simpan struk.',
            ],
        ],
        'default' => [
            'title' => 'Panduan Pembayaran',
            'steps' => [
                'Ikuti instruksi yang ditampilkan pada halaman pembayaran.',
                'Pastikan nominal sesuai tagihan.',
                'Simpan bukti pembayaran untuk referensi.',
            ],
        ],
    ],
];
