<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Uang;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Pesanan extends Model
{
    protected $table = 'pesanan';

    protected $fillable = [
        'nomor_invoice', 'toko_id', 'total_barang_rupiah', 
        'komisi_marketplace_rupiah', 'total_transfer_rupiah', 
        'status_pesanan', 'nomor_resi', 'batas_bayar_pada', 'dilepas_otomatis_pada'
    ];

    protected function casts(): array
    {
        return [
            'batas_bayar_pada' => 'datetime',
            'dilepas_otomatis_pada' => 'datetime',
        ];
    }

    protected function totalTransfer(): Attribute
    {
        return Attribute::make(
            get: fn (int $nilai) => Uang::dari($nilai),
            set: fn (Uang $uang) => $uang->rupiah,
        );
    }

    public function pembeli(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pembeli_id');
    }

    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PesananItem::class, 'pesanan_id');
    }
}
