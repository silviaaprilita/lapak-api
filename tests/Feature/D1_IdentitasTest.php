<?php

use App\Models\Pengguna;
use App\Enums\StatusAkun;

it('dapat mendaftarkan pengguna baru dengan data valid (LP-2)', function () {
    $response = $this->postJson('/api/v1/registrasi', [
        'nama' => 'Silvia Aprilita',
        'surel' => 'silvia@student.uns.ac.id',
        'telepon' => '081234567890',
        'kata_sandi' => 'PasswordSilvia#2026',
    ]);

    // Memastikan respons sukses dan format amplop API sesuai kontrak D5
    $response->assertStatus(201)
        ->assertJson([
            'sukses' => true,
            'data' => [
                'nama' => 'Silvia Aprilita',
                'surel' => 'silvia@student.uns.ac.id',
            ],
        ]);

    // Memastikan data benar-benar masuk ke database MySQL
    $this->assertDatabaseHas('pengguna', [
        'surel' => 'silvia@student.uns.ac.id',
        'status' => StatusAkun::Aktif->value,
    ]);

    // Memastikan kata sandi aman ter-hash (tidak disimpan sebagai teks polos)
    $pengguna = Pengguna::where('surel', 'silvia@student.uns.ac.id')->first();
    expect(Hash::check('PasswordSilvia#2026', $pengguna->kata_sandi))->toBeTrue();
});

it('menolak pendaftaran jika surel sudah terdaftar (LP-2)', function () {
    // Membuat satu pengguna dummy terlebih dahulu
    Pengguna::factory()->create(['surel' => 'silvia@student.uns.ac.id']);

    $response = $this->postJson('/api/v1/registrasi', [
        'nama' => 'Silvia Duplikat',
        'surel' => 'silvia@student.uns.ac.id',
        'kata_sandi' => 'PasswordBebas#123',
    ]);

    // Harus ditolak dengan status HTTP 422 (Unprocessable Entity)
    $response->assertStatus(422);
});
