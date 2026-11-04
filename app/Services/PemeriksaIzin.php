<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\NamaIzin;
use App\Models\Pengguna;

class PemeriksaIzin
{
    public function punya(?Pengguna $pengguna, NamaIzin $izin): bool
    {
        if ($pengguna === null) {
            return false;
        }

        return $pengguna->peran()
            ->whereHas('izin', fn ($q) => $q->where('nama', $izin->value))
            ->exists();
    }

    public function lupakan(Pengguna $pengguna): void
    {
        // Metode ini akan diisi pada langkah ingatan izin (B2)
    }
}
