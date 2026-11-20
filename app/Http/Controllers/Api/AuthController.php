<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NasabahResource;
use App\Models\Nasabah;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Pendaftaran nasabah baru melalui API.
     * Status default dari migration ('aktif'), tidak login otomatis.
     *
     * @throws ValidationException
     */
    public function register(Request $request): JsonResponse
    {
        $rawPhone = $request->input('no_telp') ?? $request->input('telepon');
        if (is_string($rawPhone) && trim($rawPhone) !== '') {
            try {
                $normalizedPhone = OtpService::normalizePhoneNumber($rawPhone);
                $request->merge(['no_telp' => $normalizedPhone]);
            } catch (\Throwable) {
                // Biarkan nilai asli agar validator menolaknya bila tidak sesuai format
            }
        }

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nik' => [
                'required',
                'string',
                'size:16',
                'regex:/^[0-9]{16}$/',
                Rule::unique('nasabah', 'nik'),
            ],
            'no_telp' => [
                'required',
                'string',
                'regex:/^62[0-9]{8,13}$/',
                Rule::unique('nasabah', 'no_telp'),
            ],
            'alamat' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'nik.required' => 'NIK wajib diisi.',
            'nik.size' => 'NIK harus tepat 16 digit angka.',
            'nik.regex' => 'NIK harus berupa 16 digit angka.',
            'nik.unique' => 'NIK sudah terdaftar.',
            'no_telp.required' => 'Nomor telepon wajib diisi.',
            'no_telp.regex' => 'Format nomor telepon tidak valid.',
            'no_telp.unique' => 'Nomor telepon sudah terdaftar.',
            'alamat.required' => 'Alamat lengkap wajib diisi.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        Nasabah::create([
            'nama' => $validated['nama'],
            'nik' => $validated['nik'],
            'alamat' => $validated['alamat'],
            'no_telp' => $validated['no_telp'],
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'message' => 'Pendaftaran nasabah berhasil.',
        ], 201);
    }

    /**
     * Otentikasi login nasabah via API.
     * Mengembalikan Bearer token dan data profil tanpa nik/alamat.
     * Rate limit 5 percobaan per menit per nomor+IP.
     * Nasabah berstatus non-aktif tetap diizinkan login.
     *
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        $phoneInput = (string) ($request->input('no_telp') ?? $request->input('telepon') ?? '');
        $password = (string) $request->input('password', '');

        if (trim($phoneInput) === '' || trim($password) === '') {
            $request->validate([
                'no_telp' => ['required_without:telepon', 'string'],
                'password' => ['required', 'string'],
            ], [
                'no_telp.required_without' => 'Nomor telepon wajib diisi.',
                'password.required' => 'Password wajib diisi.',
            ]);
        }

        try {
            $normalizedPhone = OtpService::normalizePhoneNumber($phoneInput);
        } catch (\Throwable) {
            $normalizedPhone = preg_replace('/\D+/', '', $phoneInput) ?: 'invalid';
        }

        $throttleKey = "login:api:nasabah:{$normalizedPhone}|".$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'no_telp' => [trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ])],
            ]);
        }

        if ($normalizedPhone === 'invalid' || ! str_starts_with($normalizedPhone, '62')) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'no_telp' => ['Nomor telepon atau password salah.'],
            ]);
        }

        /** @var Nasabah|null $nasabah */
        $nasabah = Nasabah::where('no_telp', $normalizedPhone)->first();

        if (! $nasabah || ! Hash::check($password, $nasabah->password)) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'no_telp' => ['Nomor telepon atau password salah.'],
            ]);
        }

        RateLimiter::clear($throttleKey);

        $token = $nasabah->createToken('nasabah-mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'nasabah' => new NasabahResource($nasabah, false),
        ], 200);
    }

    /**
     * Cabut personal access token yang sedang aktif digunakan.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Berhasil logout.',
        ], 200);
    }

    /**
     * Dapatkan profil nasabah pemilik akun terotentikasi, memuat nik dan alamat.
     */
    public function me(Request $request): NasabahResource
    {
        return new NasabahResource($request->user(), true);
    }
}
