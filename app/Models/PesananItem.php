<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Uang;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PesananItem extends Model
{
    protected $table = 'pesanan_item';

    protected $fillable = ['barang_id', 'nama_barang_arsip', 'harga_satuan_rupiah', 'kuantitas'];

    protected function hargaSatuan(): Attribute
    {
        return Attribute::make(
            get: fn (int $nilai) => Uang::dari($nilai),
            set: fn (Uang $uang) => $uang->rupiah,
        );
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }
}
