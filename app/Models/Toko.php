<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TokoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Toko extends Model
{
    /** @use HasFactory<TokoFactory> */
    use HasFactory;

    protected $table = 'toko';
    
    protected $fillable = ['pengguna_id', 'nama', 'slug', 'deskripsi'];

    public function pemilik(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }

    public function daftarBarang(): HasMany
    {
        return $this->hasMany(Barang::class, 'toko_id');
    }
}
