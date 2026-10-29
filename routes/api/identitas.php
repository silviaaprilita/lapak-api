<?php

use App\Http\Controllers\MasukController;
use Illuminate\Support\Facades\Route;

Route::post('/registrasi', function() {
    return response()->json(['sukses' => true]); // Placeholder penutup registrasi kontrak
});

Route::post('/masuk', MasukController::class)->middleware('throttle:5,1')->name('masuk');
