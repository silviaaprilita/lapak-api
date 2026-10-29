<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Pengguna;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class PemeriksaKataSandi
{
    private static ?string $hashUmpan = null;

    /**
     * Bila akunnya tidak ada, perbandingan hash tetap dijalankan terhadap hash umpan.
     * Jalur menempuh waktu pengerjaan yang sama agar surel tidak terdaftar tidak terdeteksi.
     */
    public function cocok(?Pengguna $pengguna, string $kataSandi): bool
    {
        $hash = $pengguna->kata_sandi ?? (self::$hashUmpan ??= Hash::make(Str::random(40)));
        return Hash::check($kataSandi, $hash) && $pengguna !== null;
    }
}
