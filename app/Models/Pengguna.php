<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StatusAkun;
use Database\Factories\PenggunaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens; // <-- Impor pustaka baru ini

final class Pengguna extends Authenticatable
{
    /** @use HasFactory<PenggunaFactory> */
    use HasFactory, HasApiTokens; // <-- Tambahkan HasApiTokens di sini

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
}
