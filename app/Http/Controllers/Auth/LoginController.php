<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\InvalidPhoneException;
use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Tampilkan formulir login portal (Petugas / Nasabah).
     */
    public function showLoginForm(string $role): View
    {
        $portals = config('metanoia.portals', []);

        if (! array_key_exists($role, $portals)) {
            abort(404);
        }

        return view('login-role', [
            'portal' => $role,
            'role' => config("metanoia.portal_labels.{$role}", ucfirst($role)),
        ]);
    }

    /**
     * Memproses upaya otentikasi pengguna berdasarkan portal.
     *
     * @throws ValidationException
     */
    public function login(Request $request, string $role): RedirectResponse
    {
        $portals = config('metanoia.portals', []);

        if (! array_key_exists($role, $portals)) {
            abort(404);
        }

        $request->validate([
            'telepon' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $rawPhone = (string) $request->input('telepon', '');

        try {
            $normalizedPhone = OtpService::normalizePhoneNumber($rawPhone);
        } catch (InvalidPhoneException) {
            $normalizedPhone = preg_replace('/\D+/', '', $rawPhone) ?: 'invalid';
        }

        // Throttle key: 5 percobaan per menit per telepon + IP
        $throttleKey = "login:{$role}:{$normalizedPhone}|".$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'telepon' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }

        // Jika nomor tidak valid sejak awal, gagal otentikasi dan catat rate limiter
        if ($normalizedPhone === 'invalid' || ! str_starts_with($normalizedPhone, '62')) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'telepon' => 'Nomor telepon atau password salah',
            ]);
        }

        $guard = $portals[$role];
        $remember = $request->boolean('remember');

        if (! Auth::guard($guard)->attempt(['no_telp' => $normalizedPhone, 'password' => $request->password], $remember)) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'telepon' => 'Nomor telepon atau password salah',
            ]);
        }

        // Petugas hanya diterima bila jabatannya terdaftar di config
        if ($guard === 'petugas') {
            $petugas = Auth::guard('petugas')->user();
            $allowedJabatan = config('metanoia.jabatan', []);

            if (! $petugas || ! in_array($petugas->jabatan, $allowedJabatan, true)) {
                Auth::guard('petugas')->logout();
                RateLimiter::hit($throttleKey, 60);

                throw ValidationException::withMessages([
                    'telepon' => 'Nomor telepon atau password salah',
                ]);
            }
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        if ($guard === 'petugas') {
            $petugas = $request->user('petugas') ?? Auth::guard('petugas')->user();

            return match ($petugas->jabatan) {
                'pengelola' => redirect()->intended(route('pengelola.dashboard')),
                'bendahara' => redirect()->intended(route('bendahara.transaksi')),
                'ketua' => redirect()->intended(route('ketua.pengajuan-penarikan')),
                default => redirect()->intended(route('pengelola.dashboard')),
            };
        }

        return redirect()->intended(route('nasabah.beranda'));
    }

    /**
     * Memproses logout akun dan membersihkan sesi.
     */
    public function logout(Request $request): RedirectResponse
    {
        if (Auth::guard('petugas')->check()) {
            Auth::guard('petugas')->logout();
        }

        if (Auth::guard('nasabah')->check()) {
            Auth::guard('nasabah')->logout();
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
