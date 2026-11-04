<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\NamaPeran;
use App\Http\Requests\RegistrasiRequest;
use App\Http\Resources\PenggunaResource;
use App\Models\Pengguna;
use App\Models\Peran;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

final class RegistrasiController extends Controller
{
    public function __invoke(RegistrasiRequest $request): JsonResponse
    {
        $pengguna = DB::transaction(function () use ($request): Pengguna {
            $pengguna = Pengguna::create(
                $request->safe()->only(['nama', 'surel', 'telepon', 'kata_sandi'])
            );
            $peranPembeli = Peran::query()
                ->where('nama', NamaPeran::Pembeli->value)
                ->firstOrFail();
            $pengguna->peran()->attach($peranPembeli->id);

            return $pengguna->refresh();
        });

        return $this->sukses(PenggunaResource::make($pengguna), 201);
    }
}
