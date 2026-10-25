<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class OtpCooldownException extends RuntimeException
{
    public function __construct(
        protected int $secondsRemaining = 0,
        string $message = 'Mohon tunggu sebelum meminta kode verifikasi baru.',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getSecondsRemaining(): int
    {
        return $this->secondsRemaining;
    }
}
