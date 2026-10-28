<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // LP-13: Saldo digital untuk setiap pengguna terdaftar
        Schema::create('dompet', function (Blueprint $tabel) {
            $tabel->id();
            $tabel->foreignId('pengguna_id')->unique()->constrained('pengguna')->cascadeOnDelete();
            $tabel->unsignedInteger('saldo_rupiah')->default(0); // Memakai format integer murni (LP-1)
            $tabel->timestamps();
        });

        // LP-14: Catatan riwayat mutasi keuangan (Audit Trail)
        Schema::create('catatan_saldo', function (Blueprint $tabel) {
            $tabel->id();
            $tabel->foreignId('dompet_id')->constrained('dompet')->cascadeOnDelete();
            $tabel->string('jenis', 20)->index(); // masuk, keluar
            $tabel->unsignedInteger('jumlah_rupiah');
            $tabel->string('keterangan', 255);
            $tabel->foreignId('pesanan_id')->nullable()->constrained('pesanan')->nullOnDelete(); // Terikat jika mutasi berasal dari transaksi
            $tabel->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catatan_saldo');
        Schema::dropIfExists('dompet');
    }
};
