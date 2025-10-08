<?php

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
