<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Exceptions\InvalidSignatureException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Exclude Midtrans notification endpoint from CSRF verification
        $middleware->validateCsrfTokens(except: [
            'pembayaran/notification',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle expired or invalid signed URLs
        $exceptions->render(function (InvalidSignatureException $e, $request) {
            return response()->view('errors.expired-link', [], 403);
        });
    })
    ->withProviders([
        App\Providers\MidtransServiceProvider::class,
    ])->create();
