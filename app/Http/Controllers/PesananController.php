<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Uang;
use App\Models\Keranjang;
use App\Models\Pesanan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class PesananController extends Controller
{
    public function checkout(Request $request): JsonResponse
    {
        $data = $request->validate([
            'toko_id' => ['required', 'exists:toko,id'],
        ]);

        $pembeli = $request->user();

        // 1. Ambil butir keranjang belanja milik pembeli dari toko yang dipilih
        $butirKeranjang = Keranjang::where('pengguna_id', $pembeli->id)
            ->whereHas('barang', function ($query) use ($data) {
                $query->where('toko_id', $data['toko_id']);
            })
            ->with('barang')
            ->get();

        if ($butirKeranjang->isEmpty()) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Keranjang belanja Anda untuk toko ini kosong.',
            ], 422);
        }

        // Gunakan Database Transaction agar jika salah satu proses gagal, database otomatis rollback kembali utuh
        $pesanan = DB::transaction(function () use ($pembeli, $data, $butirKeranjang) {
            $totalBarangRupiah = 0;
            $itemData = [];

            // 2. Hitung total belanjaan dan siapkan arsip butir pesanan
            foreach ($butirKeranjang as $item) {
                $subTotal = $item->barang->harga_rupiah * $item->kuantitas;
                $totalBarangRupiah += $subTotal;

                $itemData[] = [
                    'barang_id' => $item->barang_id,
                    'nama_barang_arsip' => $item->barang->nama,
                    'harga_satuan_rupiah' => $item->barang->harga_rupiah,
                    'kuantitas' => $item->kuantitas,
                ];
            }

            // 3. LP-8: Hitung potongan komisi 5% menggunakan Value Object Uang
            $uangTotal = Uang::dari($totalBarangRupiah);
            $uangKomisi = $uangTotal->persen(config('lapak.komisi_persen', 5));

            // 4. LP-9: Buat dokumen nomor invoice unik dan simpan pesanan utama
            $nomorInvoice = 'INV-'.now()->format('YmdHis').'-'.$pembeli->id;
            
            $pesananBaru = Pesanan::create([
                'nomor_invoice' => $nomorInvoice,
                'pembeli_id' => $pembeli->id,
                'toko_id' => $data['toko_id'],
                'total_barang_rupiah' => $totalBarangRupiah,
                'komisi_marketplace_rupiah' => $uangKomisi->rupiah,
                'total_transfer_rupiah' => $totalBarangRupiah,
                'status_pesanan' => 'dibuat',
                'batas_bayar_pada' => now()->addHours(config('lapak.batas_bayar_jam', 24)), // LP-10
            ]);

            // 5. Simpan detail barang yang dibeli ke dalam arsip item pesanan
            foreach ($itemData as $item) {
                $pesananBaru->items()->create($item);
            }

            // 6. Kosongkan keranjang belanja pembeli untuk toko tersebut
            Keranjang::where('pengguna_id', $pembeli->id)
                ->whereIn('barang_id', $butirKeranjang->pluck('barang_id'))
                ->delete();

            return $pesananBaru;
        });

        return $this->sukses([
            'id' => $pesanan->id,
            'nomor_invoice' => $pesanan->nomor_invoice,
            'total_barang_rupiah' => $pesanan->total_barang_rupiah,
            'komisi_marketplace_rupiah' => $pesanan->komisi_marketplace_rupiah,
            'total_transfer_rupiah' => $pesanan->total_transfer_rupiah,
            'status_pesanan' => $pesanan->status_pesanan,
            'batas_bayar_pada' => $pesanan->batas_bayar_pada->toIso8601String(),
        ], 201);
    }
}
