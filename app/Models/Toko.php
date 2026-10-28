<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Toko extends Model
{
    protected $table = 'toko';
    
    protected $fillable = ['nama', 'slug', 'deskripsi'];

    /**
     * LP-4: Setiap toko dimiliki oleh satu pengguna terdaftar.
     */
    public function pemilik(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }

    /**
     * Hubungan satu toko memiliki banyak barang dagangan.
     */
    public function daftarBarang(): HasMany
    {
        return $this->hasMany(Barang::class, 'toko_id');
    }
}
