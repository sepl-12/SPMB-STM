<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Midtrans\Config;

class MidtransServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Binding interface untuk Midtrans bisa disiapkan di fase lanjutan
    }

    public function boot(): void
    {
        $config = config('payment.midtrans');

        Config::$serverKey = $config['server_key'];
        Config::$isProduction = (bool) $config['is_production'];
        Config::$isSanitized = (bool) $config['is_sanitized'];
        Config::$is3ds = (bool) $config['is_3ds'];
    }
}
