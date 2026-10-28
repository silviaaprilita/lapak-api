<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        // Mendaftarkan rute API secara eksplisit agar pasti terbaca saat testing
        then: function () {
            Route::middleware('api')
                ->prefix('api/v1')
                ->name('v1.')
                ->group(function () {
                    require base_path('routes/api/identitas.php');
                    require base_path('routes/api/katalog.php');
                    require base_path('routes/api/belanja.php');
                });
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
