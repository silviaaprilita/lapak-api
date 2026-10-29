<?php

declare(strict_types=1);

namespace App\Domain\Sesi;

/**
 * Kunci sesi berbentuk lpk_<pengenal>.<rahasia>
 *
 * - pengenal: 26 karakter ULID. Boleh terlihat; dipakai mencari baris sesi.
 * - rahasia : 64 karakter heksadesimal dari 32 byte acak. Hanya klien yang memegangnya.
 *
 * Server tidak pernah menyimpan rahasia, hanya hash SHA-256-nya (LP-14).
 */
final readonly class KunciSesi
{
    public const AWALAN = 'lpk_';
    
    // Pengubah D penting: tanpa D, tanda $ ikut cocok tepat sebelum baris baru di ujung teks.
    private const POLA = '/^lpk_([0-9A-HJKMNP-TV-Z]{26})\.([0-9a-f]{64})$/D';

    private function __construct(public string $pengenal, private string $rahasia) {}

    public static function baru(string $pengenal): self
    {
        // random_bytes memakai pembangkit acak kriptografis sistem operasi.
        // Jangan pernah menggantinya dengan rand(), mt_rand(), atau uniqid().
        return new self($pengenal, bin2hex(random_bytes(32)));
    }

    /** Mengembalikan null untuk bentuk apa pun yang tidak persis sesuai pola. */
    public static function urai(string $teks): ?self
    {
        if (preg_match(self::POLA, $teks, $bagian) !== 1) {
            return null;
        }
        return new self($bagian[1], $bagian[2]);
    }

    /** Bentuk yang diserahkan ke klien. Hanya dipanggil sekali, saat kunci diterbitkan. */
    public function teks(): string
    {
        return self::AWALAN.$this->pengenal.'.'.$this->rahasia;
    }

    /** Bentuk yang disimpan server. */
    public function hash(): string
    {
        return hash('sha256', $this->rahasia);
    }

    /** hash_equals membandingkan dalam waktu tetap, sehingga lamanya tidak membocorkan isi hash. */
    public function cocok(string $hashTersimpan): bool
    {
        return hash_equals($hashTersimpan, $this->hash());
    }
}
