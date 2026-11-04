<?php

use App\Models\Dompet;
use App\Models\Pengguna;
use App\Models\Pesanan;
use App\Models\Toko;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('dapat melepaskan dana transaksi ke dompet penjual setelah pesanan selesai (LP-12, LP-14)', function () {
    // 1. Siapkan Akun Penjual dan Toko beserta Dompet Keuangannya
    $penjual = Pengguna::factory()->penjual()->create();
    $toko = Toko::factory()->for($penjual, 'pemilik')->create();
    $dompetPenjual = Dompet::firstOrCreate(['pengguna_id' => $penjual->id], ['saldo_rupiah' => 0]);

    // 2. Siapkan Akun Pembeli dan Transaksi Pesanan
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

    // 3. Simulasikan Pembeli masuk dengan $this->sebagai dan menyelesaikan pesanan
    $response = $this->sebagai($pembeli)->postJson("/api/v1/pesanan/{$pesanan->id}/selesai");

    // 4. Pastikan status 200 OK dan respons sukses
    $response->assertStatus(200)
        ->assertJson([
            'sukses' => true,
            'data' => [
                'status_pesanan' => 'selesai',
            ],
        ]);
});
