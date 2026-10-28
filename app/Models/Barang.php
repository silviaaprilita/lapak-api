<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Uang;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Barang extends Model
{
    protected $table = 'barang';

    // Tambahkan 'toko_id' ke dalam mass assignment fillable agar bisa disimpan
    protected $fillable = ['toko_id', 'nama', 'slug', 'deskripsi', 'harga_rupiah', 'stok'];

    /**
     * LP-1: Integrasikan kolom basis data harga_rupiah langsung dengan Value Object Uang.
     */
    protected function harga(): Attribute
    {
        return Attribute::make(
            get: fn (int $nilai) => Uang::dari($nilai),
            set: fn (Uang $uang) => $uang->rupiah,
        );
    }

    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }
}
