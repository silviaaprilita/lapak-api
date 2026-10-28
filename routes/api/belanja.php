<?php

use App\Http\Controllers\KeranjangController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/keranjang', [KeranjangController::class, 'tambah'])->name('keranjang.tambah');
});
