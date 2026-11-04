<?php

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
        Schema::table('dompet', function (Blueprint $table) {
            $table->string('kode')->nullable()->unique();
            $table->string('jenis')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dompet', function (Blueprint $table) {
            $table->dropUnique(['kode']);
            $table->dropColumn(['kode', 'jenis']);
        });
    }
};
