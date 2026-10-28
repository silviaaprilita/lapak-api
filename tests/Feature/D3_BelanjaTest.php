<?php

use App\Models\Barang;
use App\Models\Pengguna;
use App\Models\Toko;
use Illuminate\Foundation\Testing\RefreshDatabase; // <-- Impor pustaka pengaman ini
use Laravel\Sanctum\Sanctum;

// Mengunci agar Pest wajib memuat seluruh migrasi (termasuk keranjang) sebelum tes dimulai
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
