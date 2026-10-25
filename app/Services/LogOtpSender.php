<?php

namespace App\Services;

use App\Exceptions\OtpDeliveryException;
use Illuminate\Support\Facades\Log;

class LogOtpSender implements OtpSender
{
    /**
     * Tulis kode OTP ke log HANYA bila aplikasi berjalan di environment 'local'.
     *
     * @throws OtpDeliveryException Jika dijalankan di luar environment 'local'.
     */
    public function send(string $role, string $telepon, string $kode): void
    {
        if (! app()->environment('local')) {
            throw new OtpDeliveryException("Pengiriman OTP gagal: driver log hanya diizinkan pada environment 'local'.");
        }

        Log::info("OTP untuk portal [{$role}] nomor {$telepon}: {$kode}");
    }
}
