<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // LP-7: Keranjang belanja pembeli
        Schema::create('keranjang', function (Blueprint $tabel) {
            $tabel->id();
            $tabel->foreignId('pengguna_id')->constrained('pengguna')->cascadeOnDelete();
            $tabel->foreignId('barang_id')->constrained('barang')->cascadeOnDelete();
            $tabel->unsignedInteger('kuantitas');
            $tabel->timestamps();
            
            $tabel->unique(['pengguna_id', 'barang_id']);
        });

        // LP-9: Pesanan utama
        Schema::create('pesanan', function (Blueprint $tabel) {
            $tabel->id();
            $tabel->string('nomor_invoice', 30)->unique();
            $tabel->foreignId('pembeli_id')->constrained('pengguna');
            $tabel->foreignId('toko_id')->constrained('toko');
            $tabel->unsignedInteger('total_barang_rupiah');
            $tabel->unsignedInteger('komisi_marketplace_rupiah'); // LP-8
            $tabel->unsignedInteger('total_transfer_rupiah');
            $tabel->string('status_pesanan', 30)->index();
            $tabel->string('nomor_resi', 50)->nullable();
            $tabel->timestamp('batas_bayar_pada')->nullable(); // LP-10
            $tabel->timestamp('dilepas_otomatis_pada')->nullable(); // LP-12
            $tabel->timestamps();
        });

        // Butir detail barang yang dibeli di dalam pesanan
        Schema::create('pesanan_item', function (Blueprint $tabel) {
            $tabel->id();
            $tabel->foreignId('pesanan_id')->constrained('pesanan')->cascadeOnDelete();
            $tabel->foreignId('barang_id')->constrained('barang');
            $tabel->string('nama_barang_arsip', 150);
            $tabel->unsignedInteger('harga_satuan_rupiah');
            $tabel->unsignedInteger('kuantitas');
            $tabel->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan_item');
        Schema::dropIfExists('pesanan');
        Schema::dropIfExists('keranjang');
    }
};
