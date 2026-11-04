<?php

use App\Http\Controllers\TokoController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sesi')->group(function (): void {
    Route::post('toko-saya', [TokoController::class, 'buatToko'])
        ->middleware('izin:toko.buat')
        ->name('toko-saya.buat');
    Route::post('toko-saya/produk', [TokoController::class, 'tambahProduk'])
        ->middleware('izin:produk.tambah')
        ->name('toko-saya.produk.tambah');
});
