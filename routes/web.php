<?php

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
