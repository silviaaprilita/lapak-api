<?php

use App\Models\Barang;
use App\Models\Pengguna;
use App\Models\Toko;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('pembeli dapat memasukkan produk katalog ke dalam keranjang (LP-7)', function () {
    // 1. Siapkan data toko dan barang
    $penjual = Pengguna::factory()->create();
    $toko = Toko::create([
        'pengguna_id' => $penjual->id,
        'nama' => 'Oleh-oleh Solo Asli',
        'slug' => 'oleh-oleh-solo-asli',
    ]);
    
    $barang = Barang::create([
        'toko_id' => $toko->id,
        'nama' => 'Serabi Lezat',
        'slug' => 'serabi-lezat',
        'harga_rupiah' => 35000,
        'stok' => 50,
    ]);

    // 2. Masuk sebagai akun pembeli
    $pembeli = Pengguna::factory()->create();
    Sanctum::actingAs($pembeli);

    // 3. Masukkan ke keranjang
    $response = $this->postJson('/api/v1/keranjang', [
        'barang_id' => $barang->id,
        'kuantitas' => 2,
    ]);

    // 4. Pastikan status 200 dan tercatat di database
    $response->assertStatus(200)
        ->assertJson([
            'sukses' => true,
            'data' => [
                'barang_id' => $barang->id,
                'kuantitas' => 2,
            ]
        ]);

    $this->assertDatabaseHas('keranjang', [
        'pengguna_id' => $pembeli->id,
        'barang_id' => $barang->id,
        'kuantitas' => 2,
    ]);
});

it('menolak penambahan kuantitas keranjang jika melebihi batas batas maksimum 99', function () {
    $penjual = Pengguna::factory()->create();
    $toko = Toko::create(['pengguna_id' => $penjual->id, 'nama' => 'Toko Serba', 'slug' => 'toko-serba']);
    $barang = Barang::create(['toko_id' => $toko->id, 'nama' => 'Snack', 'slug' => 'snack', 'harga_rupiah' => 5000, 'stok' => 200]);

    $pembeli = Pengguna::factory()->create();
    Sanctum::actingAs($pembeli);

    $response = $this->postJson('/api/v1/keranjang', [
        'barang_id' => $barang->id,
        'kuantitas' => 100,
    ]);

    $response->assertStatus(422);
});

it('pembeli dapat membuat pesanan resmi dari barang di keranjang (LP-8, LP-9, LP-10)', function () {
    // 1. Siapkan toko, barang, dan pembeli yang memiliki item di keranjang
    $penjual = Pengguna::factory()->create();
    $toko = Toko::create(['pengguna_id' => $penjual->id, 'nama' => 'Batik Solo Indah', 'slug' => 'batik-solo-indah']);
    $barang = Barang::create(['toko_id' => $toko->id, 'nama' => 'Kain Sutra', 'slug' => 'kain-sutra', 'harga_rupiah' => 200000, 'stok' => 10]);

    $pembeli = Pengguna::factory()->create();
    // Masukkan barang ke keranjang pembeli terlebih dahulu
    \App\Models\Keranjang::create(['pengguna_id' => $pembeli->id, 'barang_id' => $barang->id, 'kuantitas' => 2]);

    Sanctum::actingAs($pembeli);

    // 2. Kirim request checkout pembuatan pesanan
    $response = $this->postJson('/api/v1/pesanan', [
        'toko_id' => $toko->id,
    ]);

    // Total barang: 2 x Rp 200.000 = Rp 400.000
    // Komisi 5%: 5% x Rp 400.000 = Rp 20.000 (Aturan LP-8 menggunakan Value Object Uang)
    $response->assertStatus(201)
        ->assertJson([
            'sukses' => true,
            'data' => [
                'total_barang_rupiah' => 400000,
                'komisi_marketplace_rupiah' => 20000,
                'total_transfer_rupiah' => 400000,
                'status_pesanan' => 'dibuat',
            ]
        ]);

    // 3. Pastikan data tercatat di database pesanan utama dan keranjang dikosongkan
    $this->assertDatabaseHas('pesanan', [
        'pembeli_id' => $pembeli->id,
        'toko_id' => $toko->id,
        'status_pesanan' => 'dibuat',
    ]);

    $this->assertDatabaseMissing('keranjang', [
        'pengguna_id' => $pembeli->id,
    ]);
});
