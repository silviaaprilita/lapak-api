<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // LP-4: Toko UMKM Solo Raya
        Schema::create('toko', function (Blueprint $tabel) {
            $tabel->id();
            // foreignId merujuk ke tabel pengguna yang kita buat di Langkah 9
            $tabel->foreignId('pengguna_id')->unique()->constrained('pengguna')->cascadeOnDelete();
            $tabel->string('nama', 100)->unique();
            $tabel->string('slug', 120)->unique();
            $tabel->text('deskripsi')->nullable();
            $tabel->timestamps();
        });

        // LP-5: Barang atau produk dalam katalog toko
        Schema::create('barang', function (Blueprint $tabel) {
            $tabel->id();
            $tabel->foreignId('toko_id')->constrained('toko')->cascadeOnDelete();
            $tabel->string('nama', 150);
            $tabel->string('slug', 180)->index();
            $tabel->text('deskripsi')->nullable();
            $tabel->unsignedInteger('harga_rupiah'); // Menggunakan format nilai integer murni (LP-1)
            $tabel->unsignedInteger('stok')->default(0);
            $tabel->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang');
        Schema::dropIfExists('toko');
    }
};
