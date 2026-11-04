<?php

namespace Database\Seeders;

use App\Enums\NamaIzin;
use App\Enums\NamaPeran;
use App\Models\Izin;
use App\Models\Peran;
use Illuminate\Database\Seeder;

class PeranIzinSeeder extends Seeder
{
    public function run(): void
    {
        $izinMap = [];
        foreach (NamaIzin::cases() as $nama) {
            $izinMap[$nama->value] = Izin::updateOrCreate(
                ['nama' => $nama->value],
                ['keterangan' => $nama->keterangan()]
            )->id;
        }

        foreach (NamaPeran::cases() as $nama) {
            $peran = Peran::updateOrCreate(
                ['nama' => $nama->value],
                ['keterangan' => $nama->keterangan()]
            );

            $peran->izin()->sync(
                array_map(fn (NamaIzin $i) => $izinMap[$i->value], $nama->izinBawaan())
            );
        }
    }
}
