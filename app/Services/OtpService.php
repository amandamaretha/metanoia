<?php

namespace App\Services;

use App\Exceptions\InvalidPhoneException;
use App\Exceptions\OtpConfigurationException;
use App\Exceptions\OtpCooldownException;
use App\Exceptions\OtpDeliveryException;
use App\Exceptions\OtpLockedException;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class OtpService
{
    public function __construct(
        protected ?OtpSender $sender = null
    ) {
        $this->sender = $sender ?? app(OtpSender::class);
    }

    public const ALLOWED_PURPOSES = ['reset', 'ganti_telepon'];

    /**
     * Terbitkan kode OTP 6 digit baru di dalam cache lock atomik dan kirim via sender.
     * Tidak mengembalikan kode (void).
     *
     * Catatan arsitektur: Pada implementasi driver gateway (misalnya HTTP API SMS/WhatsApp)
     * di masa mendatang, pemanggilan sender->send() HARUS dipanggil di luar lock
     * agar operasi network I/O yang berpotensi lambat tidak menahan lock atomik.
     * Saat ini pemanggilan masih berada di dalam lock untuk driver lokal.
     *
     *
     * @throws InvalidArgumentException Jika role atau purpose tidak valid.
     * @throws InvalidPhoneException Jika format nomor telepon tidak valid.
     * @throws OtpLockedException Jika akun terkunci sementara karena anggaran gagal tercapai atau lock timeout.
     * @throws OtpConfigurationException Jika konfigurasi APP_KEY kosong.
     * @throws OtpCooldownException Jika jeda kirim ulang masih aktif (membawa sisa detik).
     * @throws OtpDeliveryException Jika pengiriman pesan gagal.
     */
    public function issue(string $role, string $telepon, string $purpose = 'reset'): void
    {
        $this->validateRole($role);
        $this->validatePurpose($purpose);
        $normalizedPhone = self::normalizePhoneNumber($telepon);
        $lockKey = "otp:lock:{$role}:{$normalizedPhone}:{$purpose}";

        try {
            Cache::lock($lockKey, 10)->block(5, function () use ($role, $normalizedPhone, $purpose) {
                // 1. Cek apakah terkunci karena anggaran gagal verifikasi per jam tercapai
                if ($this->isLocked($role, $normalizedPhone, $purpose)) {
                    throw new OtpLockedException('Batas percobaan verifikasi tercapai. Akun terkunci sementara.');
                }

                $ttl = (int) config('metanoia.otp.ttl', 300);
                $expiresAt = now()->addSeconds($ttl)->timestamp;

                // 2. Generate kode 6 digit dan hitung hash SEBELUM jeda kirim ulang (memvalidasi APP_KEY)
                $kode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $hash = $this->hashKode($role, $normalizedPhone, $kode, $purpose);

                // 3. Jeda kirim ulang pakai Cache::add atomik di dalam lock
                $cooldownKey = "otp:cooldown:{$role}:{$normalizedPhone}:{$purpose}";
                $cooldownSeconds = (int) config('metanoia.otp.resend_cooldown', 60);
                $cooldownExpires = now()->addSeconds($cooldownSeconds)->timestamp;

                $cooldownAdded = Cache::add($cooldownKey, $cooldownExpires, $cooldownSeconds);
                if (! $cooldownAdded) {
                    $existingExpires = (int) Cache::get($cooldownKey, $cooldownExpires);
                    $secondsRemaining = max(1, $existingExpires - now()->timestamp);
                    throw new OtpCooldownException($secondsRemaining, 'Mohon tunggu sebelum meminta kode verifikasi baru.');
                }

                $dataKey = "otp:data:{$role}:{$normalizedPhone}:{$purpose}";

                // Kode baru menimpa kode lama (satu kode aktif per role, nomor, dan purpose)
                Cache::put($dataKey, [
                    'hash' => $hash,
                    'attempts' => 0,
                    'expires_at' => $expiresAt,
                ], $ttl);

                // Kode hanya keluar melalui OtpSender; jika gagal, bersihkan cache dan teruskan exception
                try {
                    $this->sender->send($role, $normalizedPhone, $kode);
                } catch (\Throwable $e) {
                    Cache::forget($dataKey);
                    Cache::forget($cooldownKey);
                    throw $e;
                }
            });
        } catch (LockTimeoutException $e) {
            throw new OtpLockedException('Gagal memperoleh kunci akses untuk menerbitkan OTP.', 0, $e);
        }
    }

    /**
     * Verifikasi kode OTP di dalam cache lock atomik.
     *
     *
     * @throws InvalidArgumentException Jika role atau purpose tidak valid.
     * @throws InvalidPhoneException Jika format nomor telepon tidak valid.
     * @throws OtpLockedException Jika akun terkunci sementara karena anggaran gagal tercapai atau lock timeout.
     */
    public function verify(string $role, string $telepon, string $kode, string $purpose = 'reset'): bool
    {
        $this->validateRole($role);
        $this->validatePurpose($purpose);
        $normalizedPhone = self::normalizePhoneNumber($telepon);

        // Validasi format kode: harus tepat 6 digit angka
        if (! is_string($kode) || ! preg_match('/^\d{6}$/', $kode)) {
            return false;
        }

        $lockKey = "otp:lock:{$role}:{$normalizedPhone}:{$purpose}";

        try {
            return (bool) Cache::lock($lockKey, 10)->block(5, function () use ($role, $normalizedPhone, $kode, $purpose) {
                // Cek apakah akun terkunci karena melebihi anggaran gagal per jam
                if ($this->isLocked($role, $normalizedPhone, $purpose)) {
                    throw new OtpLockedException('Batas percobaan verifikasi tercapai. Akun terkunci sementara.');
                }

                $dataKey = "otp:data:{$role}:{$normalizedPhone}:{$purpose}";
                $data = Cache::get($dataKey);

                if (! is_array($data) || ! isset($data['hash'], $data['expires_at'])) {
                    $this->recordFailure($role, $normalizedPhone, $purpose);

                    return false;
                }

                $now = now()->timestamp;
                $expiresAt = (int) $data['expires_at'];

                // Lewat expires_at -> tolak, hapus kode, dan catat kegagalan
                if ($now >= $expiresAt) {
                    Cache::forget($dataKey);
                    $this->recordFailure($role, $normalizedPhone, $purpose);

                    return false;
                }

                // Increment percobaan SEBELUM membandingkan kode
                $maxAttempts = (int) config('metanoia.otp.max_attempts', 5);
                $attempts = ((int) ($data['attempts'] ?? 0)) + 1;
                $data['attempts'] = $attempts;

                // Lewat batas attempts per kode -> hapus kode, catat kegagalan, dan tolak
                if ($attempts > $maxAttempts) {
                    Cache::forget($dataKey);
                    $this->recordFailure($role, $normalizedPhone, $purpose);

                    return false;
                }

                $inputHash = $this->hashKode($role, $normalizedPhone, $kode, $purpose);
                $isMatch = hash_equals($data['hash'], $inputHash);

                if ($isMatch) {
                    // Sekali pakai: langsung hapus setelah berhasil diverifikasi
                    Cache::forget($dataKey);

                    return true;
                }

                // Verifikasi salah -> catat ke anggaran kegagalan per jam
                $this->recordFailure($role, $normalizedPhone, $purpose);

                // Jika salah dan telah mencapai batas maksimal percobaan kode: hapus kode dan tolak
                if ($attempts >= $maxAttempts) {
                    Cache::forget($dataKey);

                    return false;
                }

                // Simpan ulang dengan SISA waktu, bukan TTL penuh
                $remainingSeconds = max(1, $expiresAt - $now);
                Cache::put($dataKey, $data, $remainingSeconds);

                return false;
            });
        } catch (LockTimeoutException $e) {
            throw new OtpLockedException('Gagal memperoleh kunci akses untuk verifikasi OTP.', 0, $e);
        }
    }

    /**
     * Normalisasi nomor telepon ke format kanonik '62'.
     * Menangani awalan '00', '+62 0812', '0', '8', dan '+62'.
     * Panjang akhir harus 10 sampai 15 digit.
     *
     *
     * @throws InvalidPhoneException Jika format nomor tidak valid atau di luar batas 10-15 digit.
     */
    public static function normalizePhoneNumber(string $phoneNumber): string
    {
        $digits = preg_replace('/\D+/', '', $phoneNumber);

        if ($digits === null || $digits === '') {
            throw new InvalidPhoneException('Nomor telepon tidak valid.');
        }

        // Tangani awalan internasional '00' (misal 0062... atau 008...)
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        // Tangani awalan '+62 0812...' atau '620812...' di mana ada angka 0 setelah kode negara 62
        if (str_starts_with($digits, '620')) {
            $digits = '62'.substr($digits, 3);
        } elseif (str_starts_with($digits, '0')) {
            // Awalan lokal '08...' diubah ke '628...'
            $digits = '62'.substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            // Awalan tanpa 0 seperti '812...' diubah ke '62812...'
            $digits = '62'.$digits;
        }

        $length = strlen($digits);

        if ($length < 10 || $length > 15 || ! str_starts_with($digits, '62')) {
            throw new InvalidPhoneException("Nomor telepon [{$phoneNumber}] tidak valid.");
        }

        return $digits;
    }

    /**
     * Memvalidasi apakah role terdaftar di config('metanoia.portals').
     *
     * @throws InvalidArgumentException Jika role tidak terdaftar.
     */
    protected function validateRole(string $role): void
    {
        $portals = config('metanoia.portals');

        if (! is_array($portals) || ! array_key_exists($role, $portals)) {
            throw new InvalidArgumentException("Role portal [{$role}] tidak valid.");
        }
    }

    /**
     * Memvalidasi apakah purpose diperbolehkan ('reset' atau 'ganti_telepon').
     *
     * @throws InvalidArgumentException Jika purpose tidak diizinkan.
     */
    protected function validatePurpose(string $purpose): void
    {
        if (! in_array($purpose, self::ALLOWED_PURPOSES, true)) {
            throw new InvalidArgumentException("Tujuan OTP [{$purpose}] tidak valid.");
        }
    }

    /**
     * Memeriksa apakah role, nomor, dan purpose telah mencapai batas kegagalan verifikasi per jam.
     */
    protected function isLocked(string $role, string $normalizedPhone, string $purpose = 'reset'): bool
    {
        $failureKey = "otp:failures:{$role}:{$normalizedPhone}:{$purpose}";
        $data = Cache::get($failureKey);

        if (! is_array($data) || ! isset($data['count'], $data['expires_at'])) {
            return false;
        }

        $now = now()->timestamp;
        if ($now >= (int) $data['expires_at']) {
            Cache::forget($failureKey);

            return false;
        }

        $maxFailures = (int) config('metanoia.otp.max_failures_per_hour', 10);

        return ((int) $data['count']) >= $maxFailures;
    }

    /**
     * Menambahkan catatan kegagalan verifikasi pada jendela 1 jam.
     */
    protected function recordFailure(string $role, string $normalizedPhone, string $purpose = 'reset'): void
    {
        $failureKey = "otp:failures:{$role}:{$normalizedPhone}:{$purpose}";
        $data = Cache::get($failureKey);
        $now = now()->timestamp;

        if (is_array($data) && isset($data['count'], $data['expires_at']) && $now < (int) $data['expires_at']) {
            $count = ((int) $data['count']) + 1;
            $expiresAt = (int) $data['expires_at'];
            $remainingTtl = max(1, $expiresAt - $now);
        } else {
            $count = 1;
            $expiresAt = now()->addHour()->timestamp;
            $remainingTtl = 3600;
        }

        Cache::put($failureKey, [
            'count' => $count,
            'expires_at' => $expiresAt,
        ], $remainingTtl);
    }

    /**
     * Menghitung hash HMAC dari 'role|nomor|purpose|kode' menggunakan APP_KEY tanpa kunci cadangan.
     *
     * @throws OtpConfigurationException Jika APP_KEY kosong.
     */
    protected function hashKode(string $role, string $normalizedPhone, string $kode, string $purpose = 'reset'): string
    {
        $appKey = config('app.key');

        if (empty($appKey)) {
            throw new OtpConfigurationException('APP_KEY tidak terkonfigurasi.');
        }

        if (str_starts_with($appKey, 'base64:')) {
            $appKey = base64_decode(substr($appKey, 7));
        }

        return hash_hmac('sha256', "{$role}|{$normalizedPhone}|{$purpose}|{$kode}", $appKey);
    }
}
