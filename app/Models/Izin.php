<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Izin extends Model
{
    protected $table = 'izin';

    protected $fillable = ['nama', 'keterangan'];

    public function peran(): BelongsToMany
    {
        return $this->belongsToMany(Peran::class, 'peran_izin');
    }
}
