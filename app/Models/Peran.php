<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Peran extends Model
{
    protected $table = 'peran';

    protected $fillable = ['nama', 'keterangan'];

    public function izin(): BelongsToMany
    {
        return $this->belongsToMany(Izin::class, 'peran_izin');
    }

    public function pengguna(): BelongsToMany
    {
        return $this->belongsToMany(Pengguna::class, 'pengguna_peran');
    }
}
