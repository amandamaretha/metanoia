<?php

namespace App\Support;

use InvalidArgumentException;

class Rupiah
{
    /**
     * Parse string representasi rupiah menjadi integer bilangan bulat positif.
     *
     * Hanya menerima format angka positif tanpa pemisah (contoh: "500", "4000")
     * atau dengan titik pemisah ribuan yang benar (contoh: "1.500", "12.500.000").
     * Menolak koma, desimal, huruf, notasi ilmiah ("1e3"), negatif, nol,
     * serta titik pemisah pada posisi yang salah (contoh: "1.50").
     *
     * @throws InvalidArgumentException
     */
    public static function parse(string $value): int
    {
        $trimmed = trim($value);

        if ($trimmed === '' || ! preg_match('/^(?:[1-9]\d{0,2}(?:\.\d{3})+|[1-9]\d*)$/', $trimmed)) {
            throw new InvalidArgumentException("Format rupiah tidak valid: '{$value}'.");
        }

        $digitsOnly = str_replace('.', '', $trimmed);

        return (int) $digitsOnly;
    }
}

