<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Uang;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Dompet extends Model
{
    protected $table = 'dompet';

    // Daftarkan pengguna_id di sini agar diizinkan masuk ke basis data saat testing
    protected $fillable = ['pengguna_id', 'saldo_rupiah'];

    protected function saldo(): Attribute
    {
        return Attribute::make(
            get: fn (int $nilai) => Uang::dari($nilai),
            set: fn (Uang $uang) => $uang->rupiah,
        );
    }

    public function pemilik(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }

    public function riwayatMutasi(): HasMany
    {
        return $this->hasMany(CatatanSaldo::class, 'dompet_id');
    }
}
