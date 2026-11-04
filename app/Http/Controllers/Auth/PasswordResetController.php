<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\InvalidPhoneException;
use App\Exceptions\OtpConfigurationException;
use App\Exceptions\OtpCooldownException;
use App\Exceptions\OtpDeliveryException;
use App\Exceptions\OtpLockedException;
use App\Http\Controllers\Controller;
use App\Services\AccountDirectory;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function __construct(
        protected OtpService $otpService,
        protected AccountDirectory $accountDirectory
    ) {}

    /**
     * Tampilkan formulir lupa password.
     */
    public function showForgotForm(Request $request): View|RedirectResponse
    {
        $portals = config('metanoia.portals', []);
        $queryRole = $request->query('role');

        if ($queryRole !== null) {
            if (! is_string($queryRole) || ! array_key_exists($queryRole, $portals)) {
                return redirect('/');
            }

            // Jika role dari query berbeda dari reset.role yang tersimpan, hapus semua reset.*
            if (session()->has('reset.role') && session('reset.role') !== $queryRole) {
                $this->clearAllResetSession();
            }

            session(['reset.role' => $queryRole]);
            $role = $queryRole;
        } else {
            $role = session('reset.role');
            if (! is_string($role) || ! array_key_exists($role, $portals)) {
                return redirect('/');
            }
        }

        return view('lupa-password', [
            'role' => $role,
        ]);
    }

    /**
     * Kirim kode OTP ke nomor telepon yang diminta.
     */
    public function sendOtp(Request $request): RedirectResponse
    {
        $role = session('reset.role');
        $portals = config('metanoia.portals', []);

        if (! is_string($role) || ! array_key_exists($role, $portals)) {
            return redirect('/');
        }

        $rawPhone = (string) $request->input('telepon', '');
        if (trim($rawPhone) === '') {
            return back()->withErrors(['telepon' => 'Nomor telepon wajib diisi.'])->withInput();
        }

        if (strlen($rawPhone) > 20) {
            return back()->withErrors(['telepon' => 'Nomor telepon maksimal 20 karakter.'])->withInput();
        }

        try {
            $normalizedPhone = OtpService::normalizePhoneNumber($rawPhone);
        } catch (InvalidPhoneException $e) {
            return back()->withErrors(['telepon' => 'Nomor telepon tidak valid.'])->withInput();
        }

        // Jika environment bukan local dan driver OTP adalah log atau null
        $otpDriver = config('metanoia.otp.driver');
        if (! app()->environment('local') && in_array($otpDriver, ['log', 'null'], true)) {
            return back()->withErrors(['telepon' => 'Layanan pengiriman kode belum tersedia'])->withInput();
        }

        // Rate limiter sebelum pencarian akun: 5 per 15 menit per role+nomor, 20 per jam per IP
        $phoneRateKey = "password_reset:phone:{$role}:{$normalizedPhone}";
        $ipRateKey = 'password_reset:ip:'.$request->ip();

        if (RateLimiter::tooManyAttempts($phoneRateKey, 5) || RateLimiter::tooManyAttempts($ipRateKey, 20)) {
            return back()->withErrors(['telepon' => 'Terlalu banyak permintaan'])->withInput();
        }

        RateLimiter::hit($phoneRateKey, 900);
        RateLimiter::hit($ipRateKey, 3600);

        if ($this->accountDirectory->exists($role, $normalizedPhone)) {
            try {
                $this->otpService->issue($role, $normalizedPhone);
            } catch (OtpCooldownException|OtpLockedException|OtpDeliveryException|OtpConfigurationException $e) {
                $last4 = substr($normalizedPhone, -4);
                Log::warning(sprintf(
                    'Penerbitan OTP gagal [%s] untuk role [%s] nomor berakhiran [%s]',
                    get_class($e),
                    $role,
                    $last4
                ));
            }
        }

        // Sebelum menyimpan nilai baru, hapus flag verifikasi lama
        session()->forget([
            'reset.verified_until',
            'reset.verified_role',
            'reset.verified_phone',
        ]);

        session([
            'reset.role' => $role,
            'reset.phone' => $normalizedPhone,
            'reset.shown' => $this->maskPhone($rawPhone),
        ]);

        return redirect()->route('verification.page')
            ->with('status', 'Jika nomor terdaftar, kode verifikasi telah dikirim.');
    }

    /**
     * Tampilkan formulir input kode verifikasi.
     */
    public function showVerificationForm(): View|RedirectResponse
    {
        if (! session()->has('reset.phone')) {
            return redirect()->route('password.forgot');
        }

        return view('kode-verifikasi');
    }

    /**
     * Verifikasi kode OTP yang dimasukkan pengguna.
     */
    public function checkOtp(Request $request): RedirectResponse
    {
        $role = session('reset.role');
        $phone = session('reset.phone');

        if (! $role || ! $phone) {
            return redirect()->route('password.forgot');
        }

        $otpInput = $request->input('otp');
        $code = is_array($otpInput) ? implode('', $otpInput) : (string) $otpInput;

        if (strlen($code) !== 6 || ! ctype_digit($code)) {
            return back()->withErrors(['otp' => 'Kode verifikasi harus berupa 6 digit angka.']);
        }

        $isValid = false;
        try {
            $isValid = $this->otpService->verify($role, $phone, $code);
        } catch (OtpLockedException|InvalidPhoneException $e) {
            $isValid = false;
        }

        if (! $isValid) {
            return back()->withErrors(['otp' => 'Kode salah atau kedaluwarsa']);
        }

        session([
            'reset.verified_until' => now()->addMinutes(10)->timestamp,
            'reset.verified_role' => $role,
            'reset.verified_phone' => $phone,
        ]);
        session()->regenerate();

        return redirect()->route('password.new');
    }

    /**
     * Tampilkan formulir pembuatan kata sandi baru.
     */
    public function showResetForm(): View|RedirectResponse
    {
        if (! $this->isVerificationValid()) {
            $this->clearAllResetSession();

            return redirect()->route('password.forgot');
        }

        return view('kata-sandi-baru');
    }

    /**
     * Perbarui kata sandi pengguna.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        if (! $this->isVerificationValid()) {
            $this->clearAllResetSession();

            return redirect()->route('password.forgot');
        }

        $request->validate([
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ], [
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $role = (string) session('reset.role');
        $phone = (string) session('reset.phone');
        $hash = Hash::make($request->input('password'));

        $updated = $this->accountDirectory->updatePassword($role, $phone, $hash);

        if ($updated) {
            $this->clearAllResetSession();

            return redirect()->route('login.show', $role)
                ->with('success', 'Kata sandi berhasil diperbarui. Silakan masuk.');
        }

        return back()->withErrors([
            'password' => 'Perubahan kata sandi belum dapat disimpan (penyimpanan akun belum tersedia)',
        ]);
    }

    /**
     * Memeriksa apakah sesi verifikasi OTP valid dan belum kedaluwarsa.
     */
    protected function isVerificationValid(): bool
    {
        $verifiedUntil = session('reset.verified_until');
        $verifiedRole = session('reset.verified_role');
        $verifiedPhone = session('reset.verified_phone');
        $currentRole = session('reset.role');
        $currentPhone = session('reset.phone');

        if (! $verifiedUntil || now()->timestamp > (int) $verifiedUntil) {
            return false;
        }

        if (! $verifiedRole || $verifiedRole !== $currentRole) {
            return false;
        }

        if (! $verifiedPhone || $verifiedPhone !== $currentPhone) {
            return false;
        }

        return true;
    }

    /**
     * Hapus seluruh data sesi alur reset password.
     */
    protected function clearAllResetSession(): void
    {
        session()->forget([
            'reset.role',
            'reset.phone',
            'reset.shown',
            'reset.verified_until',
            'reset.verified_role',
            'reset.verified_phone',
        ]);
    }

    /**
     * Samarkan nomor telepon berdasarkan nomor mentah yang diketikkan pengguna.
     */
    protected function maskPhone(string $phone): string
    {
        $phone = trim($phone);
        $len = strlen($phone);

        if ($len <= 4) {
            return str_repeat('*', $len);
        }

        if ($len <= 7) {
            return substr($phone, 0, 2).str_repeat('*', $len - 4).substr($phone, -2);
        }

        return substr($phone, 0, 4).str_repeat('*', max(3, $len - 6)).substr($phone, -2);
    }
}
