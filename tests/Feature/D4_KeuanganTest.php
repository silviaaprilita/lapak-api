<?php

use App\Enums\JenisDompet;
use App\Models\Dompet;
use App\Models\Pengguna;
use App\Models\Pesanan;
use App\Models\Toko;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('dapat melepaskan dana transaksi ke dompet penjual setelah pesanan selesai (LP-12, LP-14)', function () {
    $penjual = Pengguna::factory()->penjual()->create();
    $dompetPenjual = Dompet::create([
        'kode' => 'penjual:'.$penjual->id,
        'jenis' => JenisDompet::Penjual,
        'pengguna_id' => $penjual->id,
        'saldo_rupiah' => 0,
    ]);
    $toko = Toko::factory()->for($penjual, 'pemilik')->create(['nama' => 'Kerajinan Solo']);

    // 2. Siapkan Akun Pembeli dan Transaksi Pesanan yang sudah dibayar
    $pembeli = Pengguna::factory()->pembeli()->create();
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
    $response = $this->sebagai($pembeli)->postJson("/api/v1/pesanan/{$pesanan->id}/selesai");

    // 4. Pastikan status 200 OK
    $response->assertStatus(200)
        ->assertJson([
            'sukses' => true,
            'data' => [
                'status_pesanan' => 'selesai',
            ],
        ]);
    // 5. Pastikan saldo dompet penjual bertambah Rp 190.000 (Rp 200.000 - Rp 10.000)
    expect($dompetPenjual->fresh()->saldo_rupiah)->toBe(190000);

    // 6. LP-14: Pastikan mutasi keuangan (Audit Trail) tercatat
    $this->assertDatabaseHas('catatan_saldo', [
        'dompet_id' => $dompetPenjual->id,
        'jenis' => 'masuk',
        'jumlah_rupiah' => 190000,
        'pesanan_id' => $pesanan->id,
    ]);
});
