<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Uang;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CatatanSaldo extends Model
{
    protected $table = 'catatan_saldo';

    protected $fillable = ['jenis', 'jumlah_rupiah', 'keterangan', 'pesanan_id'];

    protected function jumlah(): Attribute
    {
        return Attribute::make(
            get: fn (int $nilai) => Uang::dari($nilai),
            set: fn (Uang $uang) => $uang->rupiah,
        );
    }

    public function dompet(): BelongsTo
    {
        return $this->belongsTo(Dompet::class, 'dompet_id');
    }

    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }
}
