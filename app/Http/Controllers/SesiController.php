<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\KonfirmasiSandiRequest;
use App\Services\PemeriksaKataSandi;
use App\Services\PenerbitSesi;
use App\Models\Sesi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SesiController extends Controller
{
    public function __construct(private readonly PenerbitSesi $penerbit) {}

    /** GET /saya: Who am I, and what session identifier am I holding right now. */
    public function saya(Request $request): JsonResponse
    {
        $sesi = Sesi::dariPermintaan($request);
        
        return $this->sukses([
            'pengguna' => [
                'id' => $request->user()->id,
                'nama' => $request->user()->nama,
                'surel' => $request->user()->surel,
            ],
            'sesi' => [
                'pengenal' => $sesi->pengenal,
                'nama_perangkat' => $sesi->nama_perangkat,
                'ip' => $sesi->ip,
                'sedang_dipakai' => true,
                'segar' => $sesi->segar(),
            ],
        ]);
    }

    /** GET /sesi: View all active authorized devices registered under this account. */
    public function index(Request $request): JsonResponse
    {
        $sesi = Sesi::query()
            ->berlaku()
            ->where('pengguna_id', $request->user()->id)
            ->orderByDesc('terakhir_dipakai_pada')
            ->get()
            ->map(fn (Sesi $s) => [
                'pengenal' => $s->pengenal,
                'nama_perangkat' => $s->nama_perangkat,
                'ip' => $s->ip,
                'sedang_dipakai' => $s->pengenal === Sesi::dariPermintaan($request)->pengenal,
                'segar' => $s->segar(),
            ]);

        return $this->sukses($sesi);
    }

    /** POST /keluar: Destroy current session only. Other devices stay active. */
    public function keluar(Request $request): JsonResponse
    {
        $this->penerbit->cabut(Sesi::dariPermintaan($request));
        return $this->sukses(null);
    }

    /** POST /sesi/konfirmasi: Re-verify password to gain a fresh session + immediate key rotation (LP-15). */
    public function konfirmasi(KonfirmasiSandiRequest $request, PemeriksaKataSandi $sandi): JsonResponse
    {
        if (! $sandi->cocok($request->user(), $request->validated('kata_sandi'))) {
            return response()->json([
                'sukses' => false,
                'galat' => [
                    'kode' => 'KREDENSIAL_SALAH',
                    'pesan' => 'Kata sandi salah.',
                    'rincian' => null
                ]
            ], 422);
        }

        $sesi = Sesi::dariPermintaan($request);
        $this->penerbit->tandaiSegar($sesi);
        $kunciBaru = $this->penerbit->rotasi($sesi);

        return $this->sukses([
            'kunci' => $kunciBaru,
            'sesi' => [
                'pengenal' => $sesi->pengenal,
                'segar' => true,
            ]
        ]);
    }

    /** DELETE /sesi/{pengenal}: Revoke a specific external device (LP-15 Broken Object Level protection). */
    public function destroy(Request $request, string $pengenal): JsonResponse
    {
        $sesi = Sesi::query()
            ->berlaku()
            ->where('pengguna_id', $request->user()->id)
            ->where('pengenal', $pengenal)
            ->firstOrFail();

        $this->penerbit->cabut($sesi);
        return $this->sukses(null);
    }
}
