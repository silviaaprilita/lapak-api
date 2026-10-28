<?php

use App\Http\Controllers\TokoController;
use Illuminate\Support\Facades\Route;

// Seluruh rute toko membutuhkan login aktif (auth:sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/toko', [TokoController::class, 'buatToko'])->name('toko.buat');
    Route::post('/toko/barang', [TokoController::class, 'tambahBarang'])->name('toko.barang.tambah');
});
