<?php

use App\Models\Pengguna;
use App\Models\Sesi;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->sri = Pengguna::factory()->create([
        'surel' => 'sri@lapak.test', 'kata_sandi' => 'BatikSolo2026',
    ]);
    $this->masuk = fn (array $timpa = []) => $this->postJson('/api/v1/masuk', array_merge([
        'surel' => 'sri@lapak.test', 'kata_sandi' => 'BatikSolo2026', 'nama_perangkat' => 'Laptop Sri',
    ], $timpa));
});

it('menerbitkan kunci sesi yang langsung dapat dipakai', function () {
    $respons = ($this->masuk)()
        ->assertCreated()
        ->assertJsonPath('data.pengguna.surel', 'sri@lapak.test');
        
    $kunci = $respons->json('data.kunci');
    expect($kunci)->toMatch('/^lpk_[0-9A-Z]{26}\.[0-9a-f]{64}$/');
});

it('tidak menyimpan kunci utuh di basis data (LP-14)', function () {
    $kunci = ($this->masuk)()->json('data.kunci');
    $rahasia = explode('.', $kunci)[1];
    expect(json_encode(DB::table('sesi')->get()))->not->toContain($rahasia);
});

it('menjawab persis sama untuk surel tak terdaftar dan kata sandi salah', function () {
    $sandiSalah = ($this->masuk)(['kata_sandi' => 'BatikSolo2027'])->assertUnauthorized();
    $surelAsing = ($this->masuk)(['surel' => 'tidak-ada@lapak.test'])->assertUnauthorized();
    expect($sandiSalah->json())->toBe($surelAsing->json())
        ->and($sandiSalah->json('galat.kode'))->toBe('KREDENSIAL_SALAH');
});

it('menerima surel dengan huruf besar dan spasi di ujung', function () {
    ($this->masuk)(['surel' => ' Sri@Lapak.TEST '])->assertCreated();
});
