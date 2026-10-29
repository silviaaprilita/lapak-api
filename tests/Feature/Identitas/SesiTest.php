<?php

use App\Models\Pengguna;
use App\Models\Sesi;
use App\Services\PenerbitSesi;

beforeEach(function () {
    $this->sri = Pengguna::factory()->create(['kata_sandi' => 'BatikSolo2026']);
    $this->terbit = fn (Pengguna $p, string $d) => app(PenerbitSesi::class)->terbitkan($p, $d);
});

it('menjawab siapa saya tanpa membocorkan kata sandi maupun hash kunci', function () {
    $respons = $this->sebagai($this->sri)->getJson('/api/v1/saya')
        ->assertOk()
        ->assertJsonPath('data.sesi.sedang_dipakai', true);

    expect($respons->getContent())
        ->not->toContain('kata_sandi', 'hash_kunci');
});

it('mencabut hanya sesi yang sedang dipakai saat keluar', function () {
    [, $kunciPonsel] = ($this->terbit)($this->sri, 'Ponsel');
    
    $this->sebagai($this->sri)->postJson('/api/v1/keluar')->assertOk();
    $this->getJson('/api/v1/saya')->assertUnauthorized(); // laptop key dies
    $this->denganKunci($kunciPonsel)->getJson('/api/v1/saya')->assertOk(); // phone stays alive
});

it('menyegarkan sesi dan mengganti kuncinya setelah kata sandi dikonfirmasi', function () {
    [$sesi, $kunciLama] = ($this->terbit)($this->sri, 'Laptop');
    $sesi->forceFill(['dikonfirmasi_pada' => now()->subHour()])->save();

    $kunciBaru = $this->denganKunci($kunciLama)
        ->postJson('/api/v1/sesi/konfirmasi', ['kata_sandi' => 'BatikSolo2026'])
        ->assertOk()
        ->json('data.kunci');

    // Changed to toBe(1) because the token is rotated inside the SAME row, maintaining database efficiency!
    expect($kunciBaru)->not->toBe($kunciLama)->and(Sesi::count())->toBe(1); 
    $this->denganKunci($kunciLama)->getJson('/api/v1/saya')->assertUnauthorized();
    $this->denganKunci($kunciBaru)->getJson('/api/v1/saya')->assertOk();
});
