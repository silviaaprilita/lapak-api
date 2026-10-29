<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\StatusAkun;
use App\Http\Requests\MasukRequest;
use App\Services\PemeriksaKataSandi;
use App\Services\PenerbitSesi;
use Illuminate\Http\JsonResponse;
use App\Models\Pengguna;
use App\Exceptions\GalatDomain;

final class MasukController extends Controller
{
    public function __construct(
        private readonly PemeriksaKataSandi $sandi,
        private readonly PenerbitSesi $penerbit,
    ) {}

    public function __invoke(MasukRequest $request): JsonResponse
    {
        $pengguna = Pengguna::where('surel', $request->validated('surel'))->first();

        if (! $this->sandi->cocok($pengguna, $request->validated('kata_sandi'))) {
            return response()->json([
                'sukses' => false,
                'galat' => [
                    'kode' => 'KREDENSIAL_SALAH',
                    'pesan' => 'Surel atau kata sandi salah.',
                    'rincian' => null
                ]
            ], 401);
        }

        if ($pengguna->status !== StatusAkun::Aktif) {
            throw new GalatDomain('AKUN_DITANGGUHKAN', 'Akun ini ditangguhkan. Hubungi admin.', 403);
        }

        [$sesi, $kunci] = $this->penerbit->terbitkan(
            $pengguna, $request->namaPerangkat(), $request->ip(), $request->userAgent()
        );

        return $this->sukses([
            'kunci' => $kunci,
            'pengguna' => [
                'id' => $pengguna->id,
                'nama' => $pengguna->nama,
                'surel' => $pengguna->surel,
            ]
        ], 201);
    }
}
