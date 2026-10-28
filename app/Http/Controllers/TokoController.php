<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Toko;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class TokoController extends Controller
{
    public function buatToko(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100', 'unique:toko,nama'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        // LP-4: Hubungkan toko baru dengan ID pengguna yang sedang login
        $toko = Toko::create([
            'pengguna_id' => $request->user()->id,
            'nama' => $data['nama'],
            'slug' => Str::slug($data['nama']),
            'deskripsi' => $data['deskripsi'] ?? null,
        ]);

        return $this->sukses([
            'id' => $toko->id,
            'nama' => $toko->nama,
            'slug' => $toko->slug,
        ], 201);
    }

    public function tambahBarang(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string'],
            'harga_rupiah' => ['required', 'integer', 'min:0'], // LP-1: Integer rupiah murni
            'stok' => ['required', 'integer', 'min:0'],
        ]);

        // Cari toko milik pengguna yang sedang login
        $toko = $request->user()->toko;

        if (! $toko) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Anda harus membuka toko terlebih dahulu.',
            ], 400);
        }

        // LP-5: Daftarkan barang ke dalam toko tersebut
        $barang = Barang::create([
            'toko_id' => $toko->id,
            'nama' => $data['nama'],
            'slug' => Str::slug($data['nama']),
            'deskripsi' => $data['deskripsi'] ?? null,
            'harga_rupiah' => $data['harga_rupiah'],
            'stok' => $data['stok'],
        ]);

        return $this->sukses([
            'id' => $barang->id,
            'nama' => $barang->nama,
            'slug' => $barang->slug,
            'harga_rupiah' => $barang->harga_rupiah,
            'stok' => $barang->stok,
        ], 201);
    }
}
