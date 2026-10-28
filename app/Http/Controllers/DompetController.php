<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Dompet;
use App\Models\Pesanan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class DompetController extends Controller
{
    public function selesaikanPesanan(Request $request, string $id): JsonResponse
    {
        $pembeli = $request->user();

        // 1. Cari pesanan yang berstatus 'dikirim' milik pembeli ini
        $pesanan = Pesanan::where('id', $id)
            ->where('pembeli_id', $pembeli->id)
            ->where('status_pesanan', 'dikirim')
            ->first();

        if (! $pesanan) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Pesanan tidak ditemukan atau tidak dapat diselesaikan.',
            ], 422);
        }

        // Jalankan transaksi database agar alokasi dana dan status pesanan tersimpan serentak
        DB::transaction(function () use ($pesanan) {
            // 2. Ubah status pesanan menjadi selesai (LP-9)
            $pesanan->update([
                'status_pesanan' => 'selesai',
            ]);

            // 3. LP-12: Hitung bersih hak penjual (Total Belanja - Komisi Marketplace)
            $hakPenjual = $pesanan->total_barang_rupiah - $pesanan->komisi_marketplace_rupiah;

            // 4. Cari atau buat dompet milik toko/penjual tujuan
            $dompetPenjual = Dompet::firstOrCreate(
                ['pengguna_id' => $pesanan->toko->pengguna_id],
                ['saldo_rupiah' => 0]
            );

            // 5. Tambahkan saldo bersih ke dompet penjual
            $dompetPenjual->increment('saldo_rupiah', $hakPenjual);

            // 6. LP-14: Catat mutasi keuangan masuk ke dalam Audit Trail catatan_saldo
            $dompetPenjual->riwayatMutasi()->create([
                'jenis' => 'masuk',
                'jumlah_rupiah' => $hakPenjual,
                'keterangan' => "Pelepasan dana pesanan dengan nomor invoice {$pesanan->nomor_invoice}",
                'pesanan_id' => $pesanan->id,
            ]);
        });

        return $this->sukses([
            'id' => $pesanan->id,
            'nomor_invoice' => $pesanan->nomor_invoice,
            'status_pesanan' => 'selesai',
        ], 200);
    }
}
