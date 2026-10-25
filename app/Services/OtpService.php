<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use RuntimeException;

class OtpService
{
    public function __construct(
        protected ?OtpSender $sender = null
    ) {
        $this->sender = $sender ?? app(OtpSender::class);
    }

    /**
     * Terbitkan kode OTP 6 digit baru di dalam cache lock atomik dan kirim via sender.
     * Tidak mengembalikan kode (void).
     *
     * @param string $role
     * @param string $telepon
     * @return void
     *
     * @throws RuntimeException Jika jeda kirim ulang masih aktif atau APP_KEY kosong.
     */
    public function issue(string $role, string $telepon): void
    {
        $normalizedPhone = self::normalizePhoneNumber($telepon);
        $lockKey = "otp:lock:{$role}:{$normalizedPhone}";

        Cache::lock($lockKey, 10)->block(5, function () use ($role, $normalizedPhone) {
            $cooldownKey = "otp:cooldown:{$role}:{$normalizedPhone}";
            $cooldownSeconds = (int) config('metanoia.otp.resend_cooldown', 60);

            // Jeda kirim ulang pakai Cache::add atomik di dalam lock
            $cooldownAdded = Cache::add($cooldownKey, now()->addSeconds($cooldownSeconds)->timestamp, $cooldownSeconds);
            if (! $cooldownAdded) {
                throw new RuntimeException('Mohon tunggu sebelum meminta kode verifikasi baru.');
            }

            $ttl = (int) config('metanoia.otp.ttl', 300);
            $expiresAt = now()->addSeconds($ttl)->timestamp;

            // Generate 6 digit angka acak dengan zero padding
            $kode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $hash = $this->hashKode($role, $normalizedPhone, $kode);

            $dataKey = "otp:data:{$role}:{$normalizedPhone}";

            // Kode baru menimpa kode lama (satu kode aktif per role dan nomor)
            Cache::put($dataKey, [
                'hash'       => $hash,
                'attempts'   => 0,
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
    }

    /**
     * Verifikasi kode OTP di dalam cache lock atomik.
     *
     * @param string $role
     * @param string $telepon
     * @param string $kode
     * @return bool
     */
    public function verify(string $role, string $telepon, string $kode): bool
    {
        // Validasi format kode: harus tepat 6 digit angka
        if (! is_string($kode) || ! preg_match('/^\d{6}$/', $kode)) {
            return false;
        }

        $normalizedPhone = self::normalizePhoneNumber($telepon);
        $lockKey = "otp:lock:{$role}:{$normalizedPhone}";

        return (bool) Cache::lock($lockKey, 10)->block(5, function () use ($role, $normalizedPhone, $kode) {
            $dataKey = "otp:data:{$role}:{$normalizedPhone}";
            $data = Cache::get($dataKey);

            if (! is_array($data) || ! isset($data['hash'], $data['expires_at'])) {
                return false;
            }

            $now = now()->timestamp;
            $expiresAt = (int) $data['expires_at'];

            // Lewat expires_at -> tolak dan hapus
            if ($now >= $expiresAt) {
                Cache::forget($dataKey);
                return false;
            }

            // Increment percobaan SEBELUM membandingkan kode
            $maxAttempts = (int) config('metanoia.otp.max_attempts', 5);
            $attempts = ((int) ($data['attempts'] ?? 0)) + 1;
            $data['attempts'] = $attempts;

            // Lewat batas -> hapus kode dan tolak
            if ($attempts > $maxAttempts) {
                Cache::forget($dataKey);
                return false;
            }

            $inputHash = $this->hashKode($role, $normalizedPhone, $kode);
            $isMatch = hash_equals($data['hash'], $inputHash);

            if ($isMatch) {
                // Sekali pakai: langsung hapus setelah berhasil diverifikasi
                Cache::forget($dataKey);
                return true;
            }

            // Jika salah dan telah mencapai batas maksimal: hapus kode dan tolak
            if ($attempts >= $maxAttempts) {
                Cache::forget($dataKey);
                return false;
            }

            // Simpan ulang dengan SISA waktu, bukan TTL penuh
            $remainingSeconds = max(1, $expiresAt - $now);
            Cache::put($dataKey, $data, $remainingSeconds);

            return false;
        });
    }

    /**
     * Normalisasi nomor telepon: hanya digit, awalan 0, 62, +62 diseragamkan ke kanonik '62'.
     *
     * @param string $phoneNumber
     * @return string
     *
     * @throws RuntimeException Jika nomor kosong setelah pembersihan digit.
     */
    public static function normalizePhoneNumber(string $phoneNumber): string
    {
        $digits = preg_replace('/\D+/', '', $phoneNumber);

        if (empty($digits)) {
            throw new RuntimeException('Nomor telepon tidak valid.');
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '62')) {
            // Sudah diawali 62
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        }

        return $digits;
    }

    /**
     * Menghitung hash HMAC dari 'role|nomor|kode' menggunakan APP_KEY tanpa kunci cadangan.
     *
     * @throws RuntimeException Jika APP_KEY kosong.
     */
    protected function hashKode(string $role, string $normalizedPhone, string $kode): string
    {
        $appKey = config('app.key');

        if (empty($appKey)) {
            throw new RuntimeException('APP_KEY tidak terkonfigurasi.');
        }

        if (str_starts_with($appKey, 'base64:')) {
            $appKey = base64_decode(substr($appKey, 7));
        }

        return hash_hmac('sha256', "{$role}|{$normalizedPhone}|{$kode}", $appKey);
    }
}
