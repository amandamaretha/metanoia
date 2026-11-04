<?php

namespace App\Services;

use App\Exceptions\InvalidPhoneException;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Auth;

class EloquentAccountDirectory implements AccountDirectory
{
    /**
     * Memeriksa apakah akun dengan role dan nomor telepon terdaftar.
     */
    public function exists(string $role, string $phone): bool
    {
        try {
            $normalizedPhone = OtpService::normalizePhoneNumber($phone);
        } catch (InvalidPhoneException $e) {
            return false;
        }

        $provider = $this->getProvider($role);
        if (! $provider) {
            return false;
        }

        $user = $provider->retrieveByCredentials(['no_telp' => $normalizedPhone]);

        return $user !== null;
    }

    /**
     * Memperbarui kata sandi akun dengan hash baru.
     */
    public function updatePassword(string $role, string $phone, string $hash): bool
    {
        try {
            $normalizedPhone = OtpService::normalizePhoneNumber($phone);
        } catch (InvalidPhoneException $e) {
            return false;
        }

        $provider = $this->getProvider($role);
        if (! $provider) {
            return false;
        }

        $user = $provider->retrieveByCredentials(['no_telp' => $normalizedPhone]);
        if (! $user) {
            return false;
        }

        $user->forceFill(['password' => $hash])->save();

        return true;
    }

    /**
     * Mendapatkan user provider berdasarkan konfigurasi guard role.
     */
    protected function getProvider(string $role): ?UserProvider
    {
        $providerName = config("auth.guards.{$role}.provider") ?? $role;

        return Auth::createUserProvider($providerName);
    }
}
