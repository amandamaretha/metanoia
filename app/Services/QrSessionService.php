<?php

namespace App\Services;

use App\Models\Nasabah;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class QrSessionService
{
    public const TTL_SECONDS = 120;

    /**
     * Buat sesi QR baru untuk petugas. Jika ada token lama, matikan terlebih dahulu.
     *
     * @return array{token: string, expires_in: int}
     */
    public function createSession(int $petugasId): array
    {
        // Matikan token lama jika ada yang masih aktif untuk petugas ini
        $activeOldToken = Cache::get("qr_petugas_active:{$petugasId}");
        if ($activeOldToken) {
            Cache::forget("qr_session:{$activeOldToken}");
        }

        $token = Str::random(40);

        $payload = [
            'token' => $token,
            'petugas_id' => $petugasId,
            'status' => 'pending',
            'nasabah_id' => null,
            'created_at' => now()->toIso8601String(),
        ];

        Cache::put("qr_session:{$token}", $payload, self::TTL_SECONDS);
        Cache::put("qr_petugas_active:{$petugasId}", $token, self::TTL_SECONDS);

        return [
            'token' => $token,
            'expires_in' => self::TTL_SECONDS,
        ];
    }

    /**
     * Perbarui QR session dengan menerbitkan token baru dan mematikan token lama.
     *
     * @return array{token: string, expires_in: int}
     */
    public function refreshSession(int $petugasId): array
    {
        return $this->createSession($petugasId);
    }

    /**
     * Batalkan sesi QR aktif.
     */
    public function cancelSession(int $petugasId, ?string $token = null): void
    {
        if ($token) {
            Cache::forget("qr_session:{$token}");
        }

        $activeToken = Cache::get("qr_petugas_active:{$petugasId}");
        if ($activeToken) {
            Cache::forget("qr_session:{$activeToken}");
            Cache::forget("qr_petugas_active:{$petugasId}");
        }
    }

    /**
     * Nasabah memindai token QR. Hanya berlaku sekali pakai dan nasabah harus aktif.
     *
     * @return array{success: bool, message: string, nasabah?: array<string, mixed>}
     */
    public function claimSession(string $token, Nasabah $nasabah): array
    {
        if ($nasabah->status !== 'aktif') {
            return [
                'success' => false,
                'message' => 'Akun nasabah tidak aktif. Transaksi penimbangan tidak dapat dilanjutkan.',
            ];
        }

        $session = Cache::get("qr_session:{$token}");

        if (! $session) {
            return [
                'success' => false,
                'message' => 'Token QR tidak valid atau sudah kedaluwarsa.',
            ];
        }

        if ($session['status'] !== 'pending') {
            return [
                'success' => false,
                'message' => 'Token QR sudah digunakan (sekali pakai).',
            ];
        }

        // Tandai klaim berhasil (sekali pakai)
        $session['status'] = 'claimed';
        $session['nasabah_id'] = $nasabah->id;
        $session['claimed_at'] = now()->toIso8601String();

        // Simpan sisa session agar dapat dibaca petugas
        Cache::put("qr_session:{$token}", $session, 60);

        return [
            'success' => true,
            'message' => 'QR Code berhasil dipindai.',
            'nasabah' => [
                'id' => $nasabah->id,
                'nama' => $nasabah->nama,
                'nik' => $nasabah->masked_nik,
                'no_telp' => $nasabah->no_telp,
                'alamat' => $nasabah->alamat,
                'saldo' => $nasabah->saldo,
            ],
        ];
    }

    /**
     * Petugas memeriksa status sesi QR saat menunggu nasabah memindai.
     *
     * @return array{status: string, nasabah?: mixed}
     */
    public function checkSession(string $token, int $petugasId): array
    {
        $session = Cache::get("qr_session:{$token}");

        if (! $session) {
            return ['status' => 'expired'];
        }

        if ((int) $session['petugas_id'] !== $petugasId) {
            return ['status' => 'invalid'];
        }

        if ($session['status'] === 'claimed' && ! empty($session['nasabah_id'])) {
            $nasabah = Nasabah::find($session['nasabah_id']);
            if ($nasabah) {
                return [
                    'status' => 'claimed',
                    'nasabah' => [
                        'id' => $nasabah->id,
                        'nama' => $nasabah->nama,
                        'nik' => $nasabah->masked_nik,
                        'no_telp' => $nasabah->no_telp,
                        'alamat' => $nasabah->alamat,
                        'saldo' => $nasabah->saldo,
                        'status' => $nasabah->status,
                    ],
                ];
            }
        }

        return ['status' => $session['status']];
    }
}
