<?php

use App\Enums\StatusProduk;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('produk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('toko')->restrictOnDelete();
            $table->foreignId('kategori_id')->constrained('kategori')->restrictOnDelete();
            $table->string('nama', 120);
            $table->string('slug', 140)->unique();
            $table->text('deskripsi')->nullable();
            $table->unsignedBigInteger('harga_rupiah');
            $table->unsignedInteger('stok')->default(0);
            $table->unsignedInteger('berat_gram')->default(0);
            $table->string('status', 20)->default(StatusProduk::Draf->value);
            $table->timestamps();

            $table->index(['status', 'kategori_id']);
            $table->index('harga_rupiah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
