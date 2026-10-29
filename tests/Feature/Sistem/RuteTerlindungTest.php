<?php

use Illuminate\Routing\Route as Rute;
use Illuminate\Support\Facades\Route;

/*
| Tolak secara bawaan.
|
| Setiap rute di bawah /api/v1 WAJIB berada di belakang auth:sesi, kecuali yang terdaftar di sini.
| Membuka sebuah rute untuk umum berarti menambah satu baris pada daftar ini.
*/
const RUTE_PUBLIK = [
    'POST api/v1/registrasi',
    'POST api/v1/login',
];

/** @return list<array{0: string, 1: Rute}> pasangan "METODE alamat" dan rutenya */
function ruteApi(): array
{
    $daftar = [];
    foreach (Route::getRoutes() as $rute) {
        if (! str_starts_with($rute->uri(), 'api/v1')) {
            continue;
        }
        foreach (array_diff($rute->methods(), ['HEAD']) as $metode) {
            $daftar[] = ["{$metode} {$rute->uri()}", $rute];
        }
    }
    return $daftar;
}

it('memasang auth:sesi pada setiap rute yang tidak terdaftar publik', function () {
    $terbuka = [];
    foreach (ruteApi() as [$nama, $rute]) {
        // Checking if either auth:sanctum or our auth:sesi is applied
        $middleware = $rute->gatherMiddleware();
        $terlindung = in_array('auth:sesi', $middleware, true) || in_array('auth:sanctum', $middleware, true);
        
        if (! $terlindung && ! in_array($nama, RUTE_PUBLIK, true)) {
            $terbuka[] = $nama;
        }
    }
    expect($terbuka)->toBeEmpty('Rute tanpa perlindungan autentikasi: '.implode(', ', $terbuka));
});

it('tidak menyimpan rute usang pada daftar publik', function () {
    $ada = array_column(ruteApi(), 0);
    $usang = array_values(array_diff(RUTE_PUBLIK, $ada));
    expect($usang)->toBeEmpty('Rute publik yang sudah tidak ada: '.implode(', ', $usang));
});
