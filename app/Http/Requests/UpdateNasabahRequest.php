<?php

namespace App\Http\Requests;

use App\Models\Nasabah;
use App\Services\OtpService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNasabahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('petugas')?->jabatan === 'pengelola';
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('no_telp') && is_string($this->no_telp)) {
            try {
                $normalized = OtpService::normalizePhoneNumber($this->no_telp);
                $this->merge(['no_telp' => $normalized]);
            } catch (\Throwable $e) {
                // Biarkan nilai asli agar validator menolaknya
            }
        }
    }

    public function rules(): array
    {
        $routeParam = $this->route('nasabah');
        $id = $routeParam instanceof Nasabah ? $routeParam->id : $routeParam;

        return [
            'nama' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string'],
            'no_telp' => ['required', 'string', 'regex:/^62[0-9]{8,13}$/', Rule::unique('nasabah', 'no_telp')->ignore($id)],
            'status' => ['required', Rule::in(['aktif', 'tidak_aktif'])],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama nasabah wajib diisi.',
            'alamat.required' => 'Alamat nasabah wajib diisi.',
            'no_telp.required' => 'Nomor telepon wajib diisi.',
            'no_telp.regex' => 'Format nomor telepon tidak valid.',
            'no_telp.unique' => 'Nomor telepon sudah terdaftar pada nasabah lain.',
            'status.required' => 'Status akun nasabah wajib dipilih.',
            'status.in' => 'Status nasabah harus aktif atau tidak_aktif.',
        ];
    }
}
