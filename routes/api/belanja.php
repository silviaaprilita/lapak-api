<?php

use App\Http\Controllers\KeranjangController;
use App\Http\Controllers\PesananController; // <-- Impor Controller baru yang akan dibuat
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/keranjang', [KeranjangController::class, 'tambah'])->name('keranjang.tambah');
    Route::post('/pesanan', [PesananController::class, 'checkout'])->name('pesanan.checkout'); // <-- Tambahkan baris ini
});
