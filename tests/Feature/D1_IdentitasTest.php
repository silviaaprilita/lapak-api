<?php

use App\Models\Pengguna;
use App\Enums\StatusAkun;
use Illuminate\Support\Facades\Hash;

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

it('dapat melakukan login dengan kredensial yang valid (LP-3)', function () {
    // Buat akun contoh menggunakan seeder yang sudah ada
    $this->artisan('db:seed', ['--class' => 'PenggunaSeeder']);

    $response = $this->postJson('/api/v1/login', [
        'surel' => 'sri@lapak.test',
        'kata_sandi' => 'LapakContoh#2026',
    ]);

    // Memastikan sukses login dan mengembalikan token sesi sesuai kontrak
    $response->assertStatus(200)
        ->assertJson([
            'sukses' => true,
        ])
        ->assertJsonStructure([
            'data' => [
                'token_akses',
                'pengguna' => ['id', 'nama', 'surel'],
            ],
        ]);
});

it('menolak login jika kata sandi salah (LP-3)', function () {
    $this->artisan('db:seed', ['--class' => 'PenggunaSeeder']);

    $response = $this->postJson('/api/v1/login', [
        'surel' => 'sri@lapak.test',
        'kata_sandi' => 'SandiSalah#123',
    ]);

    // Gagal otentikasi mengembalikan status HTTP 401 (Unauthorized)
    $response->assertStatus(401);
});
