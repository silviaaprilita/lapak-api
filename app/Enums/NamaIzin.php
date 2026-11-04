<?php

declare(strict_types=1);

namespace App\Enums;

enum NamaIzin: string
{
    case TokoBuat = 'toko.buat';
    case ProdukTambah = 'produk.tambah';
    case TokoModerasi = 'toko.moderasi';
    case PeranKelola = 'peran.kelola';
    case KeranjangKelola = 'keranjang.kelola';
    case PesananBuat = 'pesanan.buat';
    case DompetLihat = 'dompet.lihat';
    case BukuLihat = 'buku.lihat';

    public function keterangan(): string
    {
        return match ($this) {
            self::TokoBuat => 'Bisa membuka toko baru',
            self::ProdukTambah => 'Bisa menambah produk ke toko',
            self::TokoModerasi => 'Bisa menyetujui atau menangguhkan toko',
            self::PeranKelola => 'Bisa mengubah peran pengguna',
            self::KeranjangKelola => 'Bisa mengelola isi keranjang',
            self::PesananBuat => 'Bisa membuat pesanan',
            self::DompetLihat => 'Bisa melihat saldo dompet',
            self::BukuLihat => 'Bisa melihat catatan buku besar',
        };
    }
}
