<?php

namespace Database\Factories;

use App\Enums\StatusProduk;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Toko;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Produk>
 */
class ProdukFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nama = fake()->unique()->words(3, true);

        return [
            'toko_id' => Toko::factory(),
            'kategori_id' => Kategori::factory(),
            'nama' => $nama,
            'slug' => Str::slug($nama),
            'deskripsi' => fake()->sentence(),
            'harga_rupiah' => fake()->numberBetween(1000, 500000),
            'stok' => fake()->numberBetween(0, 100),
            'berat_gram' => fake()->numberBetween(50, 5000),
            'status' => StatusProduk::Aktif,
        ];
    }

    public function draf(): static
    {
        return $this->state(['status' => StatusProduk::Draf]);
    }

    public function arsip(): static
    {
        return $this->state(['status' => StatusProduk::Arsip]);
    }
}
