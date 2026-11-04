<?php

use App\Http\Controllers\KeranjangController;
use App\Http\Controllers\PesananController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sesi')->group(function () {
    Route::post('keranjang', [KeranjangController::class, 'tambah'])
        ->middleware('izin:keranjang.kelola')
        ->name('keranjang.tambah');
    Route::post('pesanan', [PesananController::class, 'checkout'])
        ->middleware('izin:pesanan.buat')
        ->name('pesanan.checkout');
});
