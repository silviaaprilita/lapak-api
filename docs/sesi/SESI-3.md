# Papan Sesi 3 — RBAC: Peran, Izin, dan Kewenangan atas Objek

- **Integrator**: Silvia
- **Fokus**: Peran dan izin, middleware izin, Policy kepemilikan toko/produk/pesanan/dompet, serta matriks otorisasi.

---

## 1. Sasaran Sesi 3
- Membangun infrastruktur RBAC berbasis Enum `NamaPeran` dan `NamaIzin`.
- Memasang middleware `izin:<nama_izin>` pada seluruh rute API terlindung.
- Memastikan perubahan peran pengguna secara otomatis mencabut seluruh sesi aktif.
- Menjaga otoritas kepemilikan objek menggunakan Laravel Policy.

---

## 2. Jawaban atas Titipan Sesi 2
1. **Siapa pun yang masuk dapat membuka toko**: Diatasi di Sesi 3 dengan pembatasan izin `toko.buat` dan alur moderasi admin (`/admin/toko/{id}/setujui`).
2. **Rotasi kunci saat peran berubah**: Diatasi lewat `PengelolaPeran` yang memanggil pencabutan sesi saat peran diperbarui.
3. **Daftar RUTE_PUBLIK**: Tetap dijaga dan diperbarui melalui tinjauan Fira.

---

## 3. Matriks Otorisasi yang Disepakati
Tabel sembilan baris rute terlindung versus lima peran (`pembeli`, `penjual`, `admin`, `keuangan`, `auditor`):

| Rute Terlindung | Izin | Pembeli | Penjual | Admin | Keuangan | Auditor |
| --- | --- | :---: | :---: | :---: | :---: | :---: |
| `POST /api/v1/toko-saya` | `toko.buat` | ✓ | ✓ | — | — | — |
| `POST /api/v1/toko-saya/produk` | `produk.tambah` | — | ✓ | — | — | — |
| `POST /api/v1/admin/toko/{id}/setujui` | `toko.moderasi` | — | — | ✓ | — | — |
| `POST /api/v1/admin/toko/{id}/tangguhkan` | `toko.moderasi` | — | — | ✓ | — | — |
| `PUT /api/v1/admin/pengguna/{id}/peran/{peran}` | `peran.kelola` | — | — | ✓ | — | — |
| `GET /api/v1/keranjang` | `keranjang.kelola` | ✓ | ✓ | — | — | — |
| `POST /api/v1/pesanan` | `pesanan.buat` | ✓ | ✓ | — | — | — |
| `GET /api/v1/dompet-saya` | `dompet.lihat` | ✓ | ✓ | — | — | — |
| `GET /api/v1/admin/buku-besar` | `buku.lihat` | — | — | — | ✓ | ✓ |

---

## 4. Urutan Merge
- **Pembuka**: PR #25 (`sesi-3/pembuka-silvia`)
- **Jendela A**: PR #26 A1 (`sesi-3/kontrak-izin-fira`), PR #27 A2 (`sesi-3/kontrak-peran-nauval`)
- **Jendela B**: 
  * B1: `sesi-3/peran-pengguna-nauval`
  * B2: `sesi-3/ingatan-izin-fira`
  * B3: `sesi-3/moderasi-toko-nauval`
  * B4: `sesi-3/gerbang-keranjang-silvia`
  * B5: `sesi-3/buku-besar-damar`
- **Penutup**: PR #33 (`sesi-3/penutup-silvia`)