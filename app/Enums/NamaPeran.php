<?php

declare(strict_types=1);

namespace App\Enums;

enum NamaPeran: string
{
    case Pembeli = 'pembeli';
    case Penjual = 'penjual';
    case Admin = 'admin';
    case Keuangan = 'keuangan';
    case Auditor = 'auditor';

    public function keterangan(): string
    {
        return match ($this) {
            self::Pembeli => 'Pengguna umum yang dapat membeli produk',
            self::Penjual => 'Pemilik toko yang dapat menjual produk',
            self::Admin => 'Pengelola platform dan moderasi',
            self::Keuangan => 'Pengelola transaksi dan buku besar',
            self::Auditor => 'Pemeriksa catatan dan laporan keuangan',
        };
    }

    /**
     * @return list<NamaIzin>
     */
    public function izinBawaan(): array
    {
        return match ($this) {
            self::Pembeli => [
                NamaIzin::TokoBuat,
                NamaIzin::KeranjangKelola,
                NamaIzin::PesananBuat,
                NamaIzin::DompetLihat,
            ],
            self::Penjual => [
                NamaIzin::TokoBuat,
                NamaIzin::ProdukTambah,
                NamaIzin::KeranjangKelola,
                NamaIzin::PesananBuat,
                NamaIzin::DompetLihat,
            ],
            self::Admin => [
                NamaIzin::TokoModerasi,
                NamaIzin::PeranKelola,
            ],
            self::Keuangan, self::Auditor => [
                NamaIzin::BukuLihat,
            ],
        };
    }
}
