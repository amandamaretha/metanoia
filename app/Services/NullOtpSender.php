<?php

namespace App\Services;

use App\Exceptions\OtpDeliveryException;

class NullOtpSender implements OtpSender
{
    /**
     * Driver null yang hanya diizinkan pada environment 'local'.
     *
     * @throws OtpDeliveryException Jika dijalankan di luar environment 'local'.
     */
    public function send(string $role, string $telepon, string $kode): void
    {
        if (! app()->environment('local')) {
            throw new OtpDeliveryException("Pengiriman OTP gagal: driver null hanya diizinkan pada environment 'local'.");
        }
    }
}
