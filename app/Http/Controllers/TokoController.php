<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\StatusProduk;
use App\Enums\StatusToko;
use App\Models\Toko;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class TokoController extends Controller
{
    public function buatToko(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:80', 'unique:toko,nama'],
            'kota' => ['required', 'string', 'max:60'],
            'ongkir_rupiah' => ['required', 'integer', 'min:0'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $toko = new Toko([
            'nama' => $data['nama'],
            'slug' => Str::slug($data['nama']).'-'.Str::lower(Str::random(5)),
            'kota' => $data['kota'],
            'ongkir_rupiah' => $data['ongkir_rupiah'],
            'deskripsi' => $data['deskripsi'] ?? null,
        ]);
        $toko->pemilik()->associate($request->user());
        $toko->status = StatusToko::Menunggu;
        $toko->save();

        return $this->sukses([
            'id' => $toko->id,
            'nama' => $toko->nama,
            'slug' => $toko->slug,
            'kota' => $toko->kota,
            'ongkir_rupiah' => $toko->ongkir_rupiah,
            'status' => $toko->status->value,
        ], 201);
    }

    public function tambahProduk(Request $request): JsonResponse
    {
        $data = $request->validate([
            'kategori_id' => ['required', 'integer', 'exists:kategori,id'],
            'nama' => ['required', 'string', 'max:120'],
            'deskripsi' => ['nullable', 'string'],
            'harga_rupiah' => ['required', 'integer', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
            'berat_gram' => ['required', 'integer', 'min:0'],
        ]);

        $toko = $request->user()->toko;

        if (! $toko) {
            abort(404);
        }

        $produk = $toko->produk()->make([
            'kategori_id' => $data['kategori_id'],
            'nama' => $data['nama'],
            'slug' => Str::slug($data['nama']).'-'.Str::lower(Str::random(5)),
            'deskripsi' => $data['deskripsi'] ?? null,
            'harga_rupiah' => $data['harga_rupiah'],
            'stok' => $data['stok'],
            'berat_gram' => $data['berat_gram'],
        ]);
        $produk->status = StatusProduk::Draf;
        $produk->save();

        return $this->sukses([
            'id' => $produk->id,
            'nama' => $produk->nama,
            'slug' => $produk->slug,
            'harga' => [
                'rupiah' => $produk->harga_rupiah,
            ],
            'stok' => $produk->stok,
            'status' => $produk->status->value,
        ], 201);
    }
}
