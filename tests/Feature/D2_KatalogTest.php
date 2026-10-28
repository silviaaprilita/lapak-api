<?php

use App\Models\Pengguna;
use App\Models\Toko;
use Laravel\Sanctum\Sanctum;

it('pembeli dapat membuka toko baru (LP-4)', function () {
    // 1. Siapkan pengguna terdaftar yang belum punya toko
    $pembeli = Pengguna::factory()->create();

    // 2. Simulasikan pengguna masuk menggunakan token Sanctum
    Sanctum::actingAs($pembeli);

    // 3. Kirim request untuk membuat toko
    $response = $this->postJson('/api/v1/toko', [
        'nama' => 'Keripik Solo Makmur',
        'deskripsi' => 'Sentra keripik renyah rasa gurih asli Surakarta.',
    ]);

    // 4. Pastikan statusnya 201 Created dan data masuk database
    $response->assertStatus(201)
        ->assertJson([
            'sukses' => true,
            'data' => [
                'nama' => 'Keripik Solo Makmur',
                'slug' => 'keripik-solo-makmur',
            ]
        ]);

    $this->assertDatabaseHas('toko', [
        'pengguna_id' => $pembeli->id,
        'nama' => 'Keripik Solo Makmur',
    ]);
});

it('menolak pembuatan toko jika nama sudah digunakan (LP-4)', function () {
    $tokoAda = Toko::factory()->create(['nama' => 'Toko Unik']);
    $userBaru = Pengguna::factory()->create();
    Sanctum::actingAs($userBaru);

    $response = $this->postJson('/api/v1/toko', [
        'nama' => 'Toko Unik',
        'deskripsi' => 'Mencoba menduplikat nama toko.',
    ]);

    $response->assertStatus(422);
});

it('pemilik toko dapat menambahkan barang baru ke katalognya (LP-5)', function () {
    // 1. Siapkan pemilik toko terdaftar
    $pemilik = Pengguna::factory()->create();
    $toko = Toko::create([
        'pengguna_id' => $pemilik->id,
        'nama' => 'Batik Silvia Keren',
        'slug' => 'batik-silvia-keren',
    ]);

    Sanctum::actingAs($pemilik);

    // 2. Tambah produk baru dengan harga integer murni (LP-1)
    $response = $this->postJson("/api/v1/toko/barang", [
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
                'slug' => 'kemeja-solo-estetik',
                'harga_rupiah' => 150000,
            ]
        ]);

    $this->assertDatabaseHas('barang', [
        'toko_id' => $toko->id,
        'nama' => 'Kemeja Solo Estetik',
        'harga_rupiah' => 150000,
    ]);
});
