<?php

namespace App\Models;

use App\Enums\StatusProduk;
use App\Enums\StatusToko;
use Database\Factories\ProdukFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Produk extends Model
{
    /** @use HasFactory<ProdukFactory> */
    use HasFactory;

    protected $table = 'produk';

    protected $fillable = [
        'toko_id',
        'kategori_id',
        'nama',
        'slug',
        'deskripsi',
        'harga_rupiah',
        'stok',
        'berat_gram',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusProduk::class,
            'harga_rupiah' => 'integer',
            'stok' => 'integer',
            'berat_gram' => 'integer',
        ];
    }

    public function scopeTampil(Builder $query): void
    {
        $query->where('status', StatusProduk::Aktif->value)
            ->whereHas('toko', fn (Builder $toko) => $toko
                ->where('status', StatusToko::Aktif->value));
    }

    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }
}
