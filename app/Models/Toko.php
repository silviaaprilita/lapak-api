<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StatusToko;
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

    protected $fillable = ['nama', 'slug', 'kota', 'deskripsi', 'ongkir_rupiah', 'status'];

    protected function casts(): array
    {
        return [
            'status' => StatusToko::class,
            'ongkir_rupiah' => 'integer',
        ];
    }

    public function pemilik(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pemilik_id');
    }

    public function daftarBarang(): HasMany
    {
        return $this->hasMany(Barang::class, 'toko_id');
    }

    public function produk(): HasMany
    {
        return $this->hasMany(Produk::class, 'toko_id');
    }
}
