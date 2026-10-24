<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class LogOtpSender implements OtpSender
{
    /**
     * Tulis kode OTP ke log HANYA bila aplikasi berjalan di environment 'local'.
     */
    public function send(string $role, string $telepon, string $kode): void
    {
        if (app()->environment('local')) {
            Log::info("OTP untuk portal [{$role}] nomor {$telepon}: {$kode}");
        }
    }
}
