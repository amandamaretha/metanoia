<?php

namespace App\Services;

interface OtpSender
{
    /**
     * Kirim kode OTP ke nomor tujuan.
     *
     * @param string $role
     * @param string $telepon
     * @param string $kode
     * @return void
     */
    public function send(string $role, string $telepon, string $kode): void;
}
