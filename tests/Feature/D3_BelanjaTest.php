<?php

use App\Models\Barang;
use App\Models\Pengguna;
use App\Models\Toko;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('pembeli dapat memasukkan produk katalog ke dalam keranjang (LP-7)', function () {
    // 1. Siapkan data penjual, toko, dan produk
    $penjual = Pengguna::factory()->penjual()->create();
    $toko = Toko::factory()->for($penjual, 'pemilik')->create([
        'nama' => 'Oleh-oleh Solo Asli',
        'slug' => 'oleh-oleh-solo-asli',
    ]);

    $barang = Barang::create([
        'toko_id' => $toko->id,
        'nama' => 'Serabi Lezat',
        'slug' => 'serabi-lezat',
        'deskripsi' => 'Serabi Solo.',
        'harga_rupiah' => 35000,
        'stok' => 50,
    ]);

    // 2. Masuk sebagai akun pembeli
    $pembeli = Pengguna::factory()->pembeli()->create();

    // 3. Masukkan barang ke keranjang
    $response = $this->sebagai($pembeli)->postJson('/api/v1/keranjang', [
        'barang_id' => $barang->id,
        'kuantitas' => 2,
    ]);

    // 4. Pastikan status 200 OK
    $response->assertStatus(200)
        ->assertJson([
            'sukses' => true,
        ]);

    $this->assertDatabaseHas('keranjang', [
        'pengguna_id' => $pembeli->id,
        'barang_id' => $barang->id,
        'kuantitas' => 2,
    ]);
});

it('menolak penambahan kuantitas keranjang jika melebihi batas maksimum 99', function () {
    $penjual = Pengguna::factory()->penjual()->create();
    $toko = Toko::factory()->for($penjual, 'pemilik')->create();
    $barang = Barang::create([
        'toko_id' => $toko->id,
        'nama' => 'Snack',
        'slug' => 'snack',
        'harga_rupiah' => 5000,
        'stok' => 200,
    ]);

    $pembeli = Pengguna::factory()->pembeli()->create();

    $response = $this->sebagai($pembeli)->postJson('/api/v1/keranjang', [
        'barang_id' => $barang->id,
        'kuantitas' => 100,
    ]);

    $response->assertStatus(422);
});

it('pembeli dapat membuat pesanan resmi dari barang di keranjang (LP-8, LP-9, LP-10)', function () {
    // 1. Siapkan toko, produk, dan pembeli yang memiliki item di keranjang
    $penjual = Pengguna::factory()->penjual()->create();
    $toko = Toko::factory()->for($penjual, 'pemilik')->create();
    $barang = Barang::create([
        'toko_id' => $toko->id,
        'nama' => 'Batik',
        'slug' => 'batik',
        'harga_rupiah' => 200000,
        'stok' => 10,
    ]);

    $pembeli = Pengguna::factory()->pembeli()->create();

    // Masukkan barang ke keranjang
    $this->sebagai($pembeli)->postJson('/api/v1/keranjang', [
        'barang_id' => $barang->id,
        'kuantitas' => 2,
    ]);

    // 2. Kirim request checkout pembuatan pesanan
    $response = $this->sebagai($pembeli)->postJson('/api/v1/pesanan', [
        'toko_id' => $toko->id,
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'sukses' => true,
            'data' => [
                'total_barang_rupiah' => 400000,
                'komisi_marketplace_rupiah' => 20000,
                'total_transfer_rupiah' => 400000,
                'status_pesanan' => 'dibuat',
            ],
        ]);

    $this->assertDatabaseHas('pesanan', [
        'pembeli_id' => $pembeli->id,
        'toko_id' => $toko->id,
        'status_pesanan' => 'dibuat',
    ]);
});
