<?php

namespace App\Support;

use InvalidArgumentException;

class TransactionCalculator
{
    /**
     * Hitung nilai transaksi terbulatkan berdasarkan berat (Kg) dan harga per kg (Rp).
     *
     * Perhitungan menggunakan BCMath penuh tanpa operasi float.
     * Menggunakan pembulatan ke kelipatan konfigurasi config('metanoia.pembulatan').
     *
     * @param string|int|float $beratKg Berat sampah dalam kilogram (maksimal 2 desimal).
     * @param string|int $hargaPerKg Harga satuan per kilogram dalam rupiah bulat.
     * @return int Nilai total transaksi terbulatkan dalam integer rupiah.
     *
     * @throws InvalidArgumentException Jika input tidak valid atau bernilai negatif atau berat > 2 desimal.
     */
    public static function calculate(string|int|float $beratKg, string|int $hargaPerKg): int
    {
        return self::calculateDetails($beratKg, $hargaPerKg)['total_nilai'];
    }

    /**
     * Hitung rincian nilai transaksi: nilai sebelum pembulatan, selisih pembulatan, dan total nilai terbulatkan.
     *
     * @param string|int|float $beratKg Berat sampah dalam kilogram (maksimal 2 desimal).
     * @param string|int $hargaPerKg Harga satuan per kilogram dalam rupiah bulat.
     * @return array{nilai_sebelum_pembulatan: int, pembulatan: int, total_nilai: int}
     *
     * @throws InvalidArgumentException
     */
    public static function calculateDetails(string|int|float $beratKg, string|int $hargaPerKg): array
    {
        $normalizedWeight = self::normalizeWeight($beratKg);
        $normalizedPrice = self::normalizePrice($hargaPerKg);

        if (bccomp($normalizedWeight, '0', 4) < 0) {
            throw new InvalidArgumentException('Berat sampah tidak boleh bernilai negatif.');
        }

        if (bccomp($normalizedPrice, '0', 4) < 0) {
            throw new InvalidArgumentException('Harga per kilogram tidak boleh bernilai negatif.');
        }

        // Jika salah satu bernilai 0, hasil adalah 0
        if (bccomp($normalizedWeight, '0', 4) === 0 || bccomp($normalizedPrice, '0', 4) === 0) {
            return [
                'nilai_sebelum_pembulatan' => 0,
                'pembulatan' => 0,
                'total_nilai' => 0,
            ];
        }

        // Kalikan menggunakan presisi skala desimal 4 murni BCMath
        $rawProduct = bcmul($normalizedWeight, $normalizedPrice, 4);

        // Nilai sebelum pembulatan kelipatan (rupiah bulat terdekat / half-up dari sen)
        $nilaiSebelumStr = bcadd($rawProduct, '0.5', 0);

        // Ambil konfigurasi pembulatan (default 100 jika berjalan di unit test murni tanpa container Laravel)
        $kelipatan = 100;
        if (class_exists(\Illuminate\Container\Container::class) && \Illuminate\Container\Container::getInstance()?->has('config')) {
            $kelipatan = (int) config('metanoia.pembulatan.kelipatan', 100);
        }

        if ($kelipatan <= 1) {
            return [
                'nilai_sebelum_pembulatan' => (int) $nilaiSebelumStr,
                'pembulatan' => 0,
                'total_nilai' => (int) $nilaiSebelumStr,
            ];
        }

        $kelipatanStr = (string) $kelipatan;
        $halfThreshold = bcdiv($kelipatanStr, '2', 0);

        // Sisa pembagian ke kelipatan
        $sisaStr = bcmod($nilaiSebelumStr, $kelipatanStr);

        // Mode terdekat (setengah ke atas / half-up)
        if (bccomp($sisaStr, $halfThreshold, 0) >= 0) {
            $totalNilaiStr = bcadd(bcsub($nilaiSebelumStr, $sisaStr, 0), $kelipatanStr, 0);
        } else {
            $totalNilaiStr = bcsub($nilaiSebelumStr, $sisaStr, 0);
        }

        $pembulatanStr = bcsub($totalNilaiStr, $nilaiSebelumStr, 0);

        return [
            'nilai_sebelum_pembulatan' => (int) $nilaiSebelumStr,
            'pembulatan' => (int) $pembulatanStr,
            'total_nilai' => (int) $totalNilaiStr,
        ];
    }

    /**
     * Normalisasi dan validasi berat sampah (maksimal 2 angka di belakang desimal).
     *
     * @throws InvalidArgumentException
     */
    public static function normalizeWeight(string|int|float $value): string
    {
        if (is_int($value)) {
            return (string) $value;
        }

        if (is_float($value)) {
            $normalized = rtrim(rtrim(number_format($value, 8, '.', ''), '0'), '.');
        } else {
            $trimmed = trim((string) $value);
            $normalized = str_replace(',', '.', $trimmed);
        }

        if (! is_numeric($normalized)) {
            throw new InvalidArgumentException('Nilai Berat harus berupa angka yang valid.');
        }

        // Validasi maksimal 2 desimal tanpa float
        $parts = explode('.', $normalized);
        if (isset($parts[1]) && strlen($parts[1]) > 2) {
            throw new InvalidArgumentException('Berat sampah maksimal memiliki 2 angka di belakang koma (desimal).');
        }

        return $normalized;
    }

    /**
     * Normalisasi dan validasi harga per kg.
     *
     * @throws InvalidArgumentException
     */
    private static function normalizePrice(string|int $value): string
    {
        if (is_int($value)) {
            return (string) $value;
        }

        $trimmed = trim((string) $value);
        $normalized = str_replace(',', '.', $trimmed);

        if (! is_numeric($normalized)) {
            throw new InvalidArgumentException('Nilai Harga harus berupa angka yang valid.');
        }

        return $normalized;
    }
}
