<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StatusAkun;
use Database\Factories\PenggunaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

final class Pengguna extends Authenticatable
{
    /** @use HasFactory<PenggunaFactory> */
    use HasFactory, HasApiTokens;

    protected $table = 'pengguna';
    
    protected $fillable = ['nama', 'surel', 'telepon', 'kata_sandi'];
    protected $hidden = ['kata_sandi'];
    protected $authPasswordName = 'kata_sandi';

    protected function casts(): array
    {
        return [
            'kata_sandi' => 'hashed',
            'status' => StatusAkun::class,
        ];
    }

    /**
     * LP-4: Hubungan satu pengguna dapat memiliki maksimal satu toko UMKM.
     */
    public function toko(): HasOne
    {
        return $this->hasOne(Toko::class, 'pengguna_id');
    }
}
