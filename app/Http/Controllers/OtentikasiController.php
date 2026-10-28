<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\RegistrasiRequest;
use App\Models\Pengguna;
use Illuminate\Http\JsonResponse;

final class OtentikasiController extends Controller
{
    public function registrasi(RegistrasiRequest $request): JsonResponse
    {
        $data = $request->validated();

        $pengguna = Pengguna::create([
            'nama' => $data['nama'],
            'surel' => $data['surel'],
            'telepon' => $data['telepon'] ?? null,
            'kata_sandi' => $data['kata_sandi'], // Otomatis ter-hash oleh casts di model Pengguna
        ]);

        return $this->sukses([
            'id' => $pengguna->id,
            'nama' => $pengguna->nama,
            'surel' => $pengguna->surel,
        ], 201);
    }
}
