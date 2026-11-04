<?php

namespace App\Http\Requests;

use App\Services\OtpService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterNasabahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('telepon') && is_string($this->telepon)) {
            try {
                $normalized = OtpService::normalizePhoneNumber($this->telepon);
                $this->merge(['telepon' => $normalized]);
            } catch (\Throwable $e) {
                // Biarkan nilai asli agar validator menolaknya
            }
        }
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'size:16', 'regex:/^[0-9]{16}$/', Rule::unique('nasabah', 'nik')],
            'alamat' => ['required', 'string'],
            'telepon' => ['required', 'string', 'regex:/^62[0-9]{8,13}$/', Rule::unique('nasabah', 'no_telp')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'nik.required' => 'NIK wajib diisi.',
            'nik.size' => 'NIK harus tepat 16 digit angka.',
            'nik.regex' => 'NIK harus berupa 16 digit angka.',
            'nik.unique' => 'NIK sudah terdaftar dalam sistem.',
            'alamat.required' => 'Alamat lengkap wajib diisi.',
            'telepon.required' => 'Nomor telepon wajib diisi.',
            'telepon.regex' => 'Format nomor telepon tidak valid.',
            'telepon.unique' => 'Nomor telepon sudah terdaftar dalam sistem.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal terdiri dari 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ];
    }
}
