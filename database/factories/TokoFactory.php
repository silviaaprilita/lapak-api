<?php

namespace Database\Factories;

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
            'pengguna_id' => Pengguna::factory(),
            'nama' => $nama,
            'slug' => Str::slug($nama),
            'deskripsi' => fake()->sentence(),
        ];
    }
}
