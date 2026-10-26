<?php

namespace Tests\Support;

use App\Services\OtpSender;

class FakeOtpSender implements OtpSender
{
    /** @var array<int, array{role: string, telepon: string, kode: string}> */
    public array $messages = [];

    public function send(string $role, string $telepon, string $kode): void
    {
        $this->messages[] = [
            'role'    => $role,
            'telepon' => $telepon,
            'kode'    => $kode,
        ];
    }

    public function lastCode(): ?string
    {
        $last = end($this->messages);
        return $last ? $last['kode'] : null;
    }
}
