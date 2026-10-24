<?php

namespace App\Services;

class NullOtpSender implements OtpSender
{
    /**
     * Driver null yang tidak mengeksekusi aksi apa pun.
     */
    public function send(string $role, string $telepon, string $kode): void
    {
        // No-op
    }
}
