<?php

namespace App\Http\Requests;

use App\Support\Rupiah;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class StoreJenisSampahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('petugas')?->jabatan === 'pengelola';
    }

    protected function prepareForValidation(): void
    {
        $parsePrice = function ($val) {
            if (is_string($val)) {
                try {
                    return Rupiah::parse($val);
                } catch (InvalidArgumentException) {
                    return $val;
                }
            }

            return $val;
        };

        $this->merge([
            'harga_sudah_dipilah' => $parsePrice($this->harga_sudah_dipilah),
            'harga_belum_dipilah' => $parsePrice($this->harga_belum_dipilah),
            'harga_campuran' => $parsePrice($this->harga_campuran),
        ]);
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'kode' => ['required', 'string', 'max:50', Rule::unique('jenis_sampah', 'kode')],
            'deskripsi' => ['nullable', 'string'],
            'kategori' => ['required', 'string', 'max:100'],
            'harga_sudah_dipilah' => ['required', 'integer', 'min:1'],
            'harga_belum_dipilah' => ['required', 'integer', 'min:1'],
            'harga_campuran' => ['required', 'integer', 'min:1'],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama produk wajib diisi.',
            'kode.required' => 'Kode produk wajib diisi.',
            'kode.unique' => 'Kode produk sudah dipakai.',
            'kategori.required' => 'Kategori produk wajib diisi.',
            'harga_sudah_dipilah.required' => 'Harga kondisi sudah dipilah wajib diisi.',
            'harga_sudah_dipilah.min' => 'Harga kondisi sudah dipilah harus berupa bilangan bulat positif.',
            'harga_belum_dipilah.required' => 'Harga kondisi belum dipilah wajib diisi.',
            'harga_belum_dipilah.min' => 'Harga kondisi belum dipilah harus berupa bilangan bulat positif.',
            'harga_campuran.required' => 'Harga kondisi campuran wajib diisi.',
            'harga_campuran.min' => 'Harga kondisi campuran harus berupa bilangan bulat positif.',
            'gambar.image' => 'File gambar harus berformat gambar valid.',
            'gambar.mimes' => 'Format gambar harus berupa jpg, jpeg, png, atau webp.',
            'gambar.max' => 'Ukuran gambar maksimal 2 MB.',
        ];
    }
}
