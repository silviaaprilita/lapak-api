<?php

use App\Models\Barang;
use App\Models\Dompet;
use App\Models\Pengguna;
use App\Models\Pesanan;
use App\Models\Toko;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

// Mengunci agar Pest memuat seluruh migrasi finansial dompet & catatan_saldo
uses(RefreshDatabase::class);

it('dapat melepaskan dana transaksi ke dompet penjual setelah pesanan selesai (LP-12, LP-14)', function () {
    // 1. Siapkan Akun Penjual dan Toko beserta Dompet Keuangannya
    $penjual = Pengguna::factory()->create();
    $dompetPenjual = Dompet::create(['pengguna_id' => $penjual->id, 'saldo_rupiah' => 0]);
    $toko = Toko::create(['pengguna_id' => $penjual->id, 'nama' => 'Kerajinan Solo', 'slug' => 'kerajinan-solo']);

    // 2. Siapkan Akun Pembeli dan Transaksi Pesanan yang sudah dibayar
    $pembeli = Pengguna::factory()->create();
    
    // Total Belanja: Rp 200.000, Komisi 5%: Rp 10.000. Bagian Penjual: Rp 190.000
    $pesanan = Pesanan::create([
        'nomor_invoice' => 'INV-TEST-001',
        'pembeli_id' => $pembeli->id,
        'toko_id' => $toko->id,
        'total_barang_rupiah' => 200000,
        'komisi_marketplace_rupiah' => 10000,
        'total_transfer_rupiah' => 200000,
        'status_pesanan' => 'dikirim',
    ]);

    // 3. Simulasikan Pembeli masuk dan menekan tombol 'Selesaikan Pesanan'
    Sanctum::actingAs($pembeli);

    $response = $this->postJson("/api/v1/pesanan/{$pesanan->id}/selesai");

    // 4. Pastikan status 200 OK dan respons sukses sesuai kontrak
    $response->assertStatus(200)
        ->assertJson([
            'sukses' => true,
            'data' => [
                'status_pesanan' => 'selesai',
            ]
        ]);

    // 5. LP-12: Pastikan saldo dompet penjual bertambah Rp 190.000 (Rp 200.000 - Rp 10.000)
    expect($dompetPenjual->fresh()->saldo_rupiah)->toBe(190000);

    // 6. LP-14: Pastikan mutasi keuangan (Audit Trail) tercatat dengan keterangan valid
    $this->assertDatabaseHas('catatan_saldo', [
        'dompet_id' => $dompetPenjual->id,
        'jenis' => 'masuk',
        'jumlah_rupiah' => 190000,
        'pesanan_id' => $pesanan->id,
    ]);
});
