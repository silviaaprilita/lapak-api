<?php

namespace Database\Seeders;

use App\Enums\StatusProduk;
use App\Enums\StatusToko;
use App\Models\Kategori;
use App\Models\Pengguna;
use App\Models\Produk;
use App\Models\Toko;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KatalogSeeder extends Seeder
{
    private const DATA_KATALOG = [
        'sri@lapak.test' => [
            'nama_toko' => 'Batik Sri Solo',
            'deskripsi_toko' => 'Kerajinan Batik Tulis asli Laweyan kualitas premium.',
            'barang' => [
                ['Kemeja Batik Tulis Solo', 'Kemeja katun primissima lapis furing sutra.', 450000, 15],
                ['Kain Jarik Klasik Sogan', 'Kain jarik katun primissima motif parang rusak.', 250000, 20],
                ['Blouse Batik Modern', 'Pakaian batik wanita gaya modern cutting reguler.', 185000, 30],
            ],
        ],
        'wulan@lapak.test' => [
            'nama_toko' => 'Dapur Wulan Serengan',
            'deskripsi_toko' => 'Kuliner dan oleh-oleh khas Solo dari resep legendaris.',
            'barang' => [
                ['Serabi Solo Notosuman Isi 10', 'Serabi santan asli tekstur lembut kemasan kotak.', 35000, 50],
                ['Abon Sapi Spesial 250g', 'Abon sapi murni kualitas premium manis gurih.', 75000, 40],
                ['Kremesan Ayam Kampung', 'Kremesan renyah gurih tanpa pengawet botol besar.', 25000, 100],
            ],
        ],
        'eko@lapak.test' => [
            'nama_toko' => 'Kerajinan Rotan Eko',
            'deskripsi_toko' => 'Furnitur dan pernak-pernik anyaman rotan estetik pasar ekspor.',
            'barang' => [
                ['Keranjang Rotan Estetik', 'Keranjang serbaguna anyaman tangan untuk dekorasi.', 65000, 25],
                ['Tatakan Gelas Rotan Set isi 6', 'Tatakan gelas anyaman rotan halus plus wadah.', 45000, 15],
            ],
        ],
    ];

    public function run(): void
    {
        $kategori = Kategori::firstOrCreate(
            ['slug' => 'umum'],
            ['nama' => 'Umum']
        );

        foreach (self::DATA_KATALOG as $surel => $info) {
            $pemilik = Pengguna::where('surel', $surel)->first();

            if (! $pemilik) {
                continue;
            }

            // Daftarkan toko UMKM
            $toko = Toko::firstOrNew(['slug' => Str::slug($info['nama_toko'])]);
            $toko->forceFill([
                'pemilik_id' => $pemilik->id,
                'nama' => $info['nama_toko'],
                'kota' => 'Surakarta',
                'ongkir_rupiah' => 12000,
                'status' => StatusToko::Aktif,
                'deskripsi' => $info['deskripsi_toko'],
            ])->save();

            foreach ($info['barang'] as [$nama, $deskripsi, $harga, $stok]) {
                $produk = Produk::firstOrNew(['slug' => Str::slug($nama)]);
                $produk->forceFill([
                    'toko_id' => $toko->id,
                    'kategori_id' => $kategori->id,
                    'nama' => $nama,
                    'deskripsi' => $deskripsi,
                    'harga_rupiah' => $harga,
                    'stok' => $stok,
                    'berat_gram' => 500,
                    'status' => StatusProduk::Aktif,
                ])->save();
            }
        }
    }
}
