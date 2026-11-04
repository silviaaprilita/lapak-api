<?php

use App\Http\Controllers\TokoController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sesi')->group(function (): void {
    Route::post('toko-saya', [TokoController::class, 'buatToko'])->name('toko-saya.buat');
    Route::post('toko-saya/barang', [TokoController::class, 'tambahBarang'])->name('toko-saya.barang.tambah');

    Route::post('toko', [TokoController::class, 'buatToko'])->name('toko.buat');
    Route::post('toko/barang', [TokoController::class, 'tambahBarang'])->name('toko.barang.tambah');
});
