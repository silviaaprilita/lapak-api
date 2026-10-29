<?php

use App\Http\Controllers\MasukController;
use App\Http\Controllers\SesiController;
use Illuminate\Support\Facades\Route;

// Public Gateways
Route::post('/masuk', MasukController::class)->middleware('throttle:5,1')->name('masuk');

// Protected Identity Management Suite
Route::middleware('auth:sesi')->group(function (): void {
    Route::get('/saya', [SesiController::class, 'saya'])->name('saya');
    Route::post('/keluar', [SesiController::class, 'keluar'])->name('keluar');
    Route::get('/sesi', [SesiController::class, 'index'])->name('sesi.index');
    Route::post('/sesi/konfirmasi', [SesiController::class, 'konfirmasi'])->middleware('throttle:5,1')->name('sesi.konfirmasi');
    
    // Revoking an external device demands an elevated fresh session state
    Route::delete('/sesi/{pengenal}', [SesiController::class, 'destroy'])->middleware('sesi.segar')->name('sesi.destroy');
});
