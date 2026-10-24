<?php

namespace App\Support;

use InvalidArgumentException;

class TransactionCalculator
{
    /**
     * Hitung nilai transaksi berdasarkan berat (Kg) dan harga per kg (Rp).
     *
     * Perhitungan menggunakan BCMath tanpa operasi float:
     * Nilai (Rp) = round(berat_kg * harga_per_kg)
     *
     * @param string|int|float $beratKg Berat sampah dalam kilogram.
     * @param string|int $hargaPerKg Harga satuan per kilogram dalam rupiah bulat.
     * @return int Nilai total transaksi dalam integer rupiah.
     *
     * @throws InvalidArgumentException Jika input tidak valid atau bernilai negatif.
     */
    public static function calculate(string|int|float $beratKg, string|int $hargaPerKg): int
    {
        $normalizedWeight = self::normalizeNumber($beratKg, 'Berat');
        $normalizedPrice = self::normalizeNumber($hargaPerKg, 'Harga');

        if (bccomp($normalizedWeight, '0', 4) < 0) {
            throw new InvalidArgumentException('Berat sampah tidak boleh bernilai negatif.');
        }

        if (bccomp($normalizedPrice, '0', 4) < 0) {
            throw new InvalidArgumentException('Harga per kilogram tidak boleh bernilai negatif.');
        }

        // Jika salah satu bernilai 0, hasil adalah 0
        if (bccomp($normalizedWeight, '0', 4) === 0 || bccomp($normalizedPrice, '0', 4) === 0) {
            return 0;
        }

        // Kalikan menggunakan presisi skala desimal 4
        $rawProduct = bcmul($normalizedWeight, $normalizedPrice, 4);

        // Bulatkan ke integer rupiah terdekat (half-up) murni melalui BCMath tanpa float
        $rounded = bcadd($rawProduct, '0.5', 0);

        return (int) $rounded;
    }

    /**
     * Normalisasi nilai numerik menjadi string yang valid untuk BCMath.
     */
    private static function normalizeNumber(string|int|float $value, string $fieldName): string
    {
        if (is_int($value)) {
            return (string) $value;
        }

        if (is_float($value)) {
            return rtrim(rtrim(number_format($value, 8, '.', ''), '0'), '.');
        }

        $trimmed = trim((string) $value);

        // Dukung pemisah desimal berupa koma maupun titik (contoh '2,4' -> '2.4')
        $normalized = str_replace(',', '.', $trimmed);

        if (! is_numeric($normalized)) {
            throw new InvalidArgumentException("Nilai {$fieldName} harus berupa angka yang valid.");
        }

        return $normalized;
    }
}
