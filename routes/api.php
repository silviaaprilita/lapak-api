<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes per Domain
|--------------------------------------------------------------------------
*/

// Memuat berkas rute eksternal secara aman di dalam grup v1
Route::prefix('v1')->name('v1.')->group(function (): void {
    foreach (glob(__DIR__.'/api/*.php') ?: [] as $berkas) {
        require $berkas;
    }
});
