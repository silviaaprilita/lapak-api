<?php

use App\Models\Pengguna;
use App\Models\Sesi;
use App\Services\PenerbitSesi;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('memangkas sesi yang sudah lama mati dan membiarkan sisanya', function () {
    $penerbit = app(PenerbitSesi::class);
    $pengguna = Pengguna::factory()->create();

    // 1. Session that was revoked more than 30 days ago (Should be deleted)
    [$dicabutLama] = $penerbit->terbitkan($pengguna, 'Dicabut lama');
    $penerbit->cabut($dicabutLama);

    // 2. Session that naturally expired 40 days ago (Should be deleted)
    [$kedaluwarsaLama] = $penerbit->terbitkan($pengguna, 'Kedaluwarsa lama');
    
    $this->travel(40)->days();

    // 3. Session that was revoked yesterday (Should be kept as logs)
    [$dicabutKemarin] = $penerbit->terbitkan($pengguna, 'Dicabut kemarin');
    $penerbit->cabut($dicabutKemarin);

    // 4. Session that is still active (Should be kept)
    [$berlaku] = $penerbit->terbitkan($pengguna, 'Masih berlaku');

    // Execute the Artisan command
    $this->artisan('lapak:pangkas-sesi')
        ->expectsOutput('2 sesi lama dipangkas.')
        ->assertSuccessful();

    expect(Sesi::pluck('pengenal')->all())
        ->toEqualCanonicalizing([$dicabutKemarin->pengenal, $berlaku->pengenal]);
});
