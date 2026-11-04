<?php

use App\Enums\StatusToko;
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
        Schema::table('toko', function (Blueprint $table) {
            $table->renameColumn('pengguna_id', 'pemilik_id');
        });

        Schema::table('toko', function (Blueprint $table) {
            $table->string('kota', 60)->default('Surakarta');
            $table->unsignedBigInteger('ongkir_rupiah')->default(0);
            $table->string('status', 20)->default(StatusToko::Menunggu->value)->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('toko', function (Blueprint $table) {
            $table->dropColumn(['kota', 'ongkir_rupiah', 'status']);
        });

        Schema::table('toko', function (Blueprint $table) {
            $table->renameColumn('pemilik_id', 'pengguna_id');
        });
    }
};
