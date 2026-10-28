<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Keranjang extends Model
{
    protected $table = 'keranjang';

    // Daftarkan 'pengguna_id' di sini agar diizinkan masuk ke basis data
    protected $fillable = ['pengguna_id', 'barang_id', 'kuantitas'];

    public function pembeli(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }
}
