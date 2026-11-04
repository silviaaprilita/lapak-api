<?php

namespace Database\Factories;

use App\Enums\NamaPeran;
use App\Enums\StatusAkun;
use App\Models\Pengguna;
use App\Models\Peran;
use Illuminate\Database\Eloquent\Factories\Factory;
use LogicException;

/**
 * @extends Factory<Pengguna>
 */
class PenggunaFactory extends Factory
{
    protected $model = Pengguna::class;

    public function definition(): array
    {
        return [
            'nama' => fake()->name(),
            'surel' => fake()->unique()->safeEmail(),
            'telepon' => '08'.fake()->numerify('##########'),
            'kata_sandi' => 'KataSandi#2026',
            'status' => StatusAkun::Aktif,
        ];
    }

    public function ditangguhkan(): static
    {
        return $this->state(['status' => StatusAkun::Ditangguhkan]);
    }

    public function denganPeran(NamaPeran ...$peran): static
    {
        return $this->afterCreating(function (Pengguna $pengguna) use ($peran): void {
            $namaPeran = array_map(
                static fn (NamaPeran $peran): string => $peran->value,
                $peran
            );
            $idPeran = Peran::query()->whereIn('nama', $namaPeran)->pluck('id');

            if ($idPeran->count() !== count(array_unique($namaPeran))) {
                throw new LogicException('Seed peran dan izin sebelum membuat pengguna dengan peran.');
            }

            $pengguna->peran()->syncWithoutDetaching($idPeran);
        });
    }

    public function pembeli(): static
    {
        return $this->denganPeran(NamaPeran::Pembeli);
    }

    public function penjual(): static
    {
        return $this->denganPeran(NamaPeran::Pembeli, NamaPeran::Penjual);
    }

    public function admin(): static
    {
        return $this->denganPeran(NamaPeran::Admin);
    }

    public function keuangan(): static
    {
        return $this->denganPeran(NamaPeran::Keuangan);
    }

    public function auditor(): static
    {
        return $this->denganPeran(NamaPeran::Auditor);
    }
}
