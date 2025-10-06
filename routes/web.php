<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('app');
});

Route::get('/daftar', function () {
    return redirect('/admin'); // Redirect ke admin panel atau form pendaftaran
});
