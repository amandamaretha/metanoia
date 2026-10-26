<?php

namespace App\Services;

interface AccountDirectory
{
    /**
     * Memeriksa apakah akun dengan role dan nomor telepon terdaftar.
     */
    public function exists(string $role, string $phone): bool;

    /**
     * Memperbarui kata sandi akun dengan hash baru.
     */
    public function updatePassword(string $role, string $phone, string $hash): bool;
}
