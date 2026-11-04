<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

final class RegistrasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'surel' => mb_strtolower(trim((string) $this->input('surel'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:100'],
            'surel' => ['required', 'string', 'email', 'max:150', 'unique:pengguna,surel'],
            'telepon' => ['nullable', 'string', 'max:20'],
            'kata_sandi' => ['required', 'string', Password::min(12)],
        ];
    }
}
