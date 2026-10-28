<?php

use App\Http\Controllers\DompetController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/pesanan/{id}/selesai', [DompetController::class, 'selesaikanPesanan'])->name('pesanan.selesai');
});
