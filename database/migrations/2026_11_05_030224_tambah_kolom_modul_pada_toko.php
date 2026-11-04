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
            $table->string('status', 20)->default(StatusToko::Menunggu->value)->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('toko', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('toko', function (Blueprint $table) {
            $table->renameColumn('pemilik_id', 'pengguna_id');
        });
    }
};
