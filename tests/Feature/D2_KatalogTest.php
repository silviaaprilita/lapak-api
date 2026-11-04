<?php

use App\Models\Barang;
use App\Models\Pengguna;
use App\Models\Toko;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('pembeli dapat membuka toko baru (LP-4)', function () {
    // 1. Siapkan pengguna pembeli terdaftar yang belum punya toko
    $pembeli = Pengguna::factory()->pembeli()->create();

    // 2. Simulasikan pengguna masuk menggunakan helper autentikasi sesi
    $response = $this->sebagai($pembeli)->postJson('/api/v1/toko-saya', [
        'nama' => 'Keripik Solo Makmur',
        'kota' => 'Surakarta',
        'ongkir_rupiah' => 12000,
        'deskripsi' => 'Sentra keripik renyah rasa gurih asli Surakarta.',
    ]);

    // 3. Pastikan statusnya 201 Created dan data masuk database
    $response->assertStatus(201)
        ->assertJson([
            'sukses' => true,
            'data' => [
                'nama' => 'Keripik Solo Makmur',
            ],
        ]);

    $this->assertDatabaseHas('toko', [
        'pengguna_id' => $pembeli->id,
        'nama' => 'Keripik Solo Makmur',
        'kota' => 'Surakarta',
        'ongkir_rupiah' => 12000,
    ]);
});

it('menolak pembuatan toko jika nama sudah digunakan (LP-4)', function () {
    Toko::factory()->create(['nama' => 'Toko Unik']);
    $userBaru = Pengguna::factory()->pembeli()->create();

    $response = $this->sebagai($userBaru)->postJson('/api/v1/toko-saya', [
        'nama' => 'Toko Unik',
        'kota' => 'Surakarta',
        'ongkir_rupiah' => 10000,
        'deskripsi' => 'Mencoba menduplikat nama toko.',
    ]);

    $response->assertStatus(422);
});

it('pemilik toko dapat menambahkan barang baru ke katalognya (LP-5)', function () {
    // 1. Siapkan penjual dan toko miliknya
    $penjual = Pengguna::factory()->penjual()->create();
    $toko = Toko::factory()->for($penjual, 'pemilik')->create([
        'nama' => 'Batik Silvia Keren',
        'slug' => 'batik-silvia-keren',
    ]);

    // 2. Tambah barang baru lewat rute toko milik pengguna
    $response = $this->sebagai($penjual)->postJson('/api/v1/toko-saya/barang', [
        'nama' => 'Kemeja Solo Estetik',
        'deskripsi' => 'Kemeja kain premium cap solo.',
        'harga_rupiah' => 150000,
        'stok' => 25,
    ]);

    // 3. Pastikan tersimpan dengan format amplop respons yang benar
    $response->assertStatus(201)
        ->assertJson([
            'sukses' => true,
            'data' => [
                'nama' => 'Kemeja Solo Estetik',
                'harga_rupiah' => 150000,
            ],
        ]);

    $this->assertDatabaseHas('barang', [
        'toko_id' => $toko->id,
        'nama' => 'Kemeja Solo Estetik',
        'harga_rupiah' => 150000,
    ]);
});
