<?php

namespace Database\Factories;

use App\Enums\StatusToko;
use App\Models\Pengguna;
use App\Models\Toko;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Toko>
 */
class TokoFactory extends Factory
{
    protected $model = Toko::class;

    public function definition(): array
    {
        $nama = fake()->unique()->company();

        return [
            'pemilik_id' => Pengguna::factory(),
            'nama' => $nama,
            'slug' => Str::slug($nama),
            'kota' => fake()->randomElement(['Surakarta', 'Sukoharjo', 'Karanganyar', 'Boyolali']),
            'deskripsi' => fake()->sentence(),
            'ongkir_rupiah' => fake()->randomElement([8000, 10000, 12000, 15000]),
            'status' => StatusToko::Aktif,
        ];
    }
}
