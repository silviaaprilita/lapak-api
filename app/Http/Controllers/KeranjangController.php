<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Keranjang;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class KeranjangController extends Controller
{
    public function tambah(Request $request): JsonResponse
    {
        $data = $request->validate([
            'produk_id' => ['required', 'exists:produk,id'],
            'kuantitas' => ['required', 'integer', 'min:1', 'max:99'], // Batas kuantitas maksimal 99 unit
        ]);

        $pengguna = $request->user();

        // LP-7: Hitung jumlah butir barang unik yang saat ini ada di keranjang pembeli
        $jumlahButirUnik = Keranjang::where('pengguna_id', $pengguna->id)->count();
        $sudahAdaDiKeranjang = Keranjang::where('pengguna_id', $pengguna->id)
            ->where('produk_id', $data['produk_id'])
            ->exists();

        // Jika barang baru dan butir unik di keranjang sudah mencapai batas maksimal 20 macam
        if (! $sudahAdaDiKeranjang && $jumlahButirUnik >= config('lapak.belanja.butir_maksimum', 20)) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Keranjang Anda penuh. Maksimal butir unik dalam keranjang adalah 20 macam barang.',
            ], 422);
        }

        // Simpan atau perbarui kuantitas barang di keranjang pembeli
        $keranjang = Keranjang::updateOrCreate(
            [
                'pengguna_id' => $pengguna->id,
                'produk_id' => $data['produk_id'],
            ],
            [
                'kuantitas' => $sudahAdaDiKeranjang
                    ? min(99, Keranjang::where('pengguna_id', $pengguna->id)->where('produk_id', $data['produk_id'])->value('kuantitas') + $data['kuantitas'])
                    : $data['kuantitas'],
            ]
        );

        return $this->sukses([
            'id' => $keranjang->id,
            'produk_id' => $keranjang->produk_id,
            'kuantitas' => $keranjang->kuantitas,
        ], 200);
    }
}
