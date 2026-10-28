<?php

use App\Http\Controllers\OtentikasiController;
use Illuminate\Support\Facades\Route;

Route::post('/registrasi', [OtentikasiController::class, 'registrasi'])->name('registrasi');
Route::post('/login', [OtentikasiController::class, 'login'])->name('login'); // <-- Tambahkan baris ini
