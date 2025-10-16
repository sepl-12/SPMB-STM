<?php

use App\Http\Controllers\FileDownloadController;
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
Route::get('/pembayaran/{registration_number}', [PaymentController::class, 'show'])->name('payment.show');
Route::post('/pembayaran/notification', [PaymentController::class, 'notification'])->name('payment.notification');
Route::get('/pembayaran/finish', [PaymentController::class, 'finish'])->name('payment.finish');
Route::get('/pembayaran/status/{registration_number}', [PaymentController::class, 'status'])->name('payment.status');
Route::get('/pembayaran/success/{registration_number}', [PaymentController::class, 'success'])->name('payment.success');
Route::post('/pembayaran/check-status', [PaymentController::class, 'checkStatus'])->name('payment.check-status');

// Payment Recovery routes
Route::get('/cek-pembayaran', [PaymentController::class, 'checkPaymentForm'])->name('payment.check-form');
Route::post('/cek-pembayaran', [PaymentController::class, 'findPayment'])->name('payment.find');
Route::post('/kirim-ulang-link', [PaymentController::class, 'resendPaymentLink'])->name('payment.resend-link');

// File Download routes - secured with signed URLs and rate limiting
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/files/{file}/download', [FileDownloadController::class, 'download'])->name('file.download');
    Route::get('/files/{file}/preview', [FileDownloadController::class, 'preview'])->name('file.preview');
});
