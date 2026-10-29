<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Auth\AuthenticationException;
use App\Exceptions\GalatDomain;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('api')
                ->prefix('api/v1')
                ->name('v1.')
                ->group(function () {
                    if (file_exists(base_path('routes/api/identitas.php'))) {
                        require base_path('routes/api/identitas.php');
                    }
                    if (file_exists(base_path('routes/api/katalog.php'))) {
                        require base_path('routes/api/katalog.php');
                    }
                    if (file_exists(base_path('routes/api/belanja.php'))) {
                        require base_path('routes/api/belanja.php');
                    }
                    if (file_exists(base_path('routes/api/keuangan.php'))) {
                        require base_path('routes/api/keuangan.php');
                    }
                });
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'sesi.segar' => \App\Http\Middleware\SesiSegar::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // 1. D5: Tangkap semua pelanggaran aturan bisnis (Termasuk Sesi Tidak Segar)
        $exceptions->render(function (GalatDomain $e, Request $request) {
            return response()->json([
                'sukses' => false,
                'galat' => [
                    'kode' => $e->kode,
                    'pesan' => $e->getMessage(),
                    'rincian' => $e->rincian,
                ],
            ], $e->statusHttp);
        });

        // 2. LP-14 & D5: Cegah keluarnya JSON "Unauthenticated." bawaan Laravel
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                return response()->json([
                    'sukses' => false,
                    'galat' => [
                        'kode' => 'BELUM_MASUK',
                        'pesan' => 'Anda belum masuk atau sesi tidak berlaku.',
                        'rincian' => null,
                    ],
                ], 401);
            }
        });
    })->create();
