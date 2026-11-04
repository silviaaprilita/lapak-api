<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $barangLama = DB::table('barang')->orderBy('id')->get();

        if ($barangLama->isNotEmpty()) {
            DB::table('kategori')->insertOrIgnore([
                'nama' => 'Warisan',
                'slug' => 'warisan',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $kategoriId = DB::table('kategori')->where('slug', 'warisan')->value('id');

            foreach ($barangLama as $barang) {
                DB::table('produk')->insertOrIgnore([
                    'id' => $barang->id,
                    'toko_id' => $barang->toko_id,
                    'kategori_id' => $kategoriId,
                    'nama' => $barang->nama,
                    'slug' => Str::slug($barang->slug.'-'.$barang->id),
                    'deskripsi' => $barang->deskripsi,
                    'harga_rupiah' => $barang->harga_rupiah,
                    'stok' => $barang->stok,
                    'berat_gram' => 0,
                    'status' => 'draf',
                    'created_at' => $barang->created_at,
                    'updated_at' => $barang->updated_at,
                ]);
            }
        }

        if (DB::connection()->getDriverName() === 'sqlite') {
            $this->gantiTabelSqlite(
                'keranjang',
                'butir_keranjang',
                'barang_id',
                'produk_id',
                true
            );
            $this->gantiTabelSqlite(
                'pesanan_item',
                'butir_pesanan',
                'barang_id',
                'produk_id',
                false
            );

            return;
        }

        Schema::table('keranjang', function (Blueprint $table) {
            $table->dropForeign('keranjang_barang_id_foreign');
            $table->dropForeign('keranjang_pengguna_id_foreign');
            $table->dropUnique('keranjang_pengguna_id_barang_id_unique');
            $table->renameColumn('barang_id', 'produk_id');
        });
        Schema::table('keranjang', function (Blueprint $table) {
            $table->foreign('pengguna_id', 'keranjang_pengguna_id_foreign')
                ->references('id')
                ->on('pengguna')
                ->cascadeOnDelete();
            $table->foreign('produk_id', 'keranjang_produk_id_foreign')
                ->references('id')
                ->on('produk')
                ->cascadeOnDelete();
            $table->unique(['pengguna_id', 'produk_id'], 'keranjang_pengguna_id_produk_id_unique');
        });
        Schema::rename('keranjang', 'butir_keranjang');

        Schema::table('pesanan_item', function (Blueprint $table) {
            $table->dropForeign('pesanan_item_barang_id_foreign');
            $table->renameColumn('barang_id', 'produk_id');
        });
        Schema::table('pesanan_item', function (Blueprint $table) {
            $table->foreign('produk_id', 'pesanan_item_produk_id_foreign')
                ->references('id')
                ->on('produk')
                ->restrictOnDelete();
        });
        Schema::rename('pesanan_item', 'butir_pesanan');
    }

    public function down(): void
    {
        foreach (DB::table('produk')->orderBy('id')->get() as $produk) {
            DB::table('barang')->updateOrInsert(
                ['id' => $produk->id],
                [
                    'toko_id' => $produk->toko_id,
                    'nama' => $produk->nama,
                    'slug' => $produk->slug,
                    'deskripsi' => $produk->deskripsi,
                    'harga_rupiah' => $produk->harga_rupiah,
                    'stok' => $produk->stok,
                    'created_at' => $produk->created_at,
                    'updated_at' => $produk->updated_at,
                ]
            );
        }

        if (DB::connection()->getDriverName() === 'sqlite') {
            $this->gantiTabelSqlite(
                'butir_keranjang',
                'keranjang',
                'produk_id',
                'barang_id',
                true
            );
            $this->gantiTabelSqlite(
                'butir_pesanan',
                'pesanan_item',
                'produk_id',
                'barang_id',
                false
            );

            return;
        }

        Schema::rename('butir_keranjang', 'keranjang');
        Schema::table('keranjang', function (Blueprint $table) {
            $table->dropForeign('keranjang_produk_id_foreign');
            $table->dropForeign('keranjang_pengguna_id_foreign');
            $table->dropUnique('keranjang_pengguna_id_produk_id_unique');
            $table->renameColumn('produk_id', 'barang_id');
        });
        Schema::table('keranjang', function (Blueprint $table) {
            $table->foreign('pengguna_id', 'keranjang_pengguna_id_foreign')
                ->references('id')
                ->on('pengguna')
                ->cascadeOnDelete();
            $table->foreign('barang_id', 'keranjang_barang_id_foreign')
                ->references('id')
                ->on('barang')
                ->cascadeOnDelete();
            $table->unique(['pengguna_id', 'barang_id'], 'keranjang_pengguna_id_barang_id_unique');
        });

        Schema::rename('butir_pesanan', 'pesanan_item');
        Schema::table('pesanan_item', function (Blueprint $table) {
            $table->dropForeign('pesanan_item_produk_id_foreign');
            $table->renameColumn('produk_id', 'barang_id');
        });
        Schema::table('pesanan_item', function (Blueprint $table) {
            $table->foreign('barang_id', 'pesanan_item_barang_id_foreign')
                ->references('id')
                ->on('barang')
                ->restrictOnDelete();
        });
    }

    private function gantiTabelSqlite(
        string $tabelLama,
        string $tabelBaru,
        string $kolomLama,
        string $kolomBaru,
        bool $unikPerPengguna
    ): void {
        Schema::create($tabelBaru, function (Blueprint $table) use ($kolomBaru, $unikPerPengguna) {
            $table->id();
            if ($unikPerPengguna) {
                $table->foreignId('pengguna_id')->constrained('pengguna')->cascadeOnDelete();
            } else {
                $table->foreignId('pesanan_id')->constrained('pesanan')->cascadeOnDelete();
            }
            if ($unikPerPengguna) {
                $table->foreignId($kolomBaru)->constrained($kolomBaru === 'produk_id' ? 'produk' : 'barang')->cascadeOnDelete();
            } else {
                $table->foreignId($kolomBaru)->constrained($kolomBaru === 'produk_id' ? 'produk' : 'barang')->restrictOnDelete();
            }

            if ($unikPerPengguna) {
                $table->unsignedInteger('kuantitas');
                $table->unique(['pengguna_id', $kolomBaru]);
            } else {
                $table->string('nama_barang_arsip', 150);
                $table->unsignedInteger('harga_satuan_rupiah');
                $table->unsignedInteger('kuantitas');
            }
            $table->timestamps();
        });

        foreach (DB::table($tabelLama)->orderBy('id')->get() as $baris) {
            $data = (array) $baris;
            $data[$kolomBaru] = $data[$kolomLama];
            unset($data[$kolomLama]);
            DB::table($tabelBaru)->insert($data);
        }

        Schema::drop($tabelLama);
    }
};
