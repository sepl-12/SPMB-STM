<?php

use App\Http\Controllers\FileDownloadController;
use App\Http\Controllers\GoogleOauthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('app');
})->name('home');

// Registration routes
Route::get('/daftar', [RegistrationController::class, 'index'])->name('registration.index');
Route::post('/daftar/save-step', [RegistrationController::class, 'saveStep'])->name('registration.save-step');
Route::post('/daftar/jump-to-step', [RegistrationController::class, 'jumpToStep'])->name('registration.jump-to-step');
Route::get('/daftar/success/{registration_number}', [RegistrationController::class, 'success'])->name('registration.success');

// Payment routes
// Legacy unsecured routes - kept for backward compatibility but should be phased out
// Route::get('/pembayaran/{registration_number}', [PaymentController::class, 'show'])->name('payment.show');
Route::get('/pembayaran/status/{registration_number}', [PaymentController::class, 'status'])->name('payment.status');

// Secured payment routes with signed URLs
Route::middleware('signed')->group(function () {
    Route::get('/secure/pembayaran/{registration_number}', [PaymentController::class, 'showSecure'])->name('payment.show-secure');
    Route::get('/secure/pembayaran/success/{registration_number}', [PaymentController::class, 'successSecure'])->name('payment.success-secure');
    Route::get('/secure/status/{registration_number}', [PaymentController::class, 'statusSecure'])->name('applicant.status-secure');
    Route::get('/secure/kartu-ujian/{registration_number}', [PaymentController::class, 'examCard'])->name('exam-card.show');
});

// Payment notification and callbacks (unsecured - required for Midtrans)
Route::post('/pembayaran/notification', [PaymentController::class, 'notification'])->name('payment.notification');
Route::get('/pembayaran/finish', [PaymentController::class, 'finish'])->name('payment.finish');
Route::post('/pembayaran/check-status', [PaymentController::class, 'checkStatus'])->name('payment.check-status');

// DEPRECATED: Legacy payment success route - redirect to secure version
// Route::get('/pembayaran/success/{registration_number}', function (string $registration_number) {
//     $applicant = \App\Models\Applicant::where('registration_number', $registration_number)->firstOrFail();
//     return redirect($applicant->getPaymentSuccessUrl());
// })->name('payment.success');

// Payment Recovery routes
Route::get('/cek-pembayaran', [PaymentController::class, 'checkPaymentForm'])->name('payment.check-form');
Route::post('/cek-pembayaran', [PaymentController::class, 'findPayment'])->name('payment.find');
Route::post('/kirim-ulang-link', [PaymentController::class, 'resendPaymentLink'])->name('payment.resend-link');

// File Download routes - secured with signed URLs and rate limiting
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/files/{file}/download', [FileDownloadController::class, 'download'])->name('file.download');
    Route::get('/files/{file}/preview', [FileDownloadController::class, 'preview'])->name('file.preview');
});

Route::get('/google/oauth/redirect', [GoogleOauthController::class, 'redirect'])->name('google.oauth.redirect');
Route::get('/google/oauth/callback', [GoogleOauthController::class, 'callback'])->name('google.oauth.callback');
