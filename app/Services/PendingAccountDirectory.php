<?php

namespace App\Services;

use RuntimeException;

class PendingAccountDirectory implements AccountDirectory
{
    public function exists(string $role, string $phone): bool
    {
        $this->ensureSupportedEnvironment();

        return true;
    }

    public function updatePassword(string $role, string $phone, string $hash): bool
    {
        $this->ensureSupportedEnvironment();

        return false;
    }

    protected function ensureSupportedEnvironment(): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new RuntimeException('Penyimpanan akun belum dikonfigurasi untuk environment ini.');
        }
    }
}
