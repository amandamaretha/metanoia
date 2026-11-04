<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterNasabahRequest;
use App\Models\Nasabah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterNasabahController extends Controller
{
    /**
     * Tampilkan formulir pendaftaran akun nasabah baru.
     */
    public function showRegistrationForm(): View
    {
        return view('auth.daftar-nasabah');
    }

    /**
     * Proses pendaftaran nasabah baru.
     */
    public function register(RegisterNasabahRequest $request): RedirectResponse
    {
        Nasabah::create([
            'nama' => $request->validated('nama'),
            'nik' => $request->validated('nik'),
            'alamat' => $request->validated('alamat'),
            'no_telp' => $request->validated('telepon'),
            'password' => Hash::make($request->validated('password')),
        ]);

        return redirect()->route('login.show', config('metanoia.portals.nasabah', 'nasabah'))
            ->with('success', 'Pendaftaran berhasil! Silakan masuk menggunakan nomor telepon dan password Anda.');
    }
}
