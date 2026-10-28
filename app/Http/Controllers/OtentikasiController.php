<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegistrasiRequest;
use App\Models\Pengguna;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

final class OtentikasiController extends Controller
{
    public function registrasi(RegistrasiRequest $request): JsonResponse
    {
        $data = $request->validated();

        $pengguna = Pengguna::create([
            'nama' => $data['nama'],
            'surel' => $data['surel'],
            'telepon' => $data['telepon'] ?? null,
            'kata_sandi' => $data['kata_sandi'],
        ]);

        return $this->sukses([
            'id' => $pengguna->id,
            'nama' => $pengguna->nama,
            'surel' => $pengguna->surel,
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        $pengguna = Pengguna::where('surel', $data['surel'])->first();

        // Validasi apakah pengguna ada dan kata sandi cocok (LP-3)
        if (! $pengguna || ! Hash::check($data['kata_sandi'], $pengguna->kata_sandi)) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Kredensial yang Anda masukkan salah.',
            ], 401);
        }

        // Generate token akses sesi baru
        $token = $pengguna->createToken('token-sesi-lapak')->plainTextToken;

        return $this->sukses([
            'token_akses' => $token,
            'pengguna' => [
                'id' => $pengguna->id,
                'nama' => $pengguna->nama,
                'surel' => $pengguna->surel,
            ],
        ]);
    }
}
