<?php

namespace Tests\Unit;

use App\Support\TransactionCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TransactionCalculatorTest extends TestCase
{
    /**
     * Uji coba input bernilai nol menghasilkan nilai transaksi 0.
     */
    public function test_perhitungan_dengan_input_nol_menghasilkan_nol(): void
    {
        $this->assertSame(0, TransactionCalculator::calculate(0, 2400));
        $this->assertSame(0, TransactionCalculator::calculate('0', 2400));
        $this->assertSame(0, TransactionCalculator::calculate('0.00', 2400));
        $this->assertSame(0, TransactionCalculator::calculate('2.4', 0));
        $this->assertSame(0, TransactionCalculator::calculate(0, 0));
    }

    /**
     * Uji coba perhitungan dengan desimal berat maksimal 2 desimal (titik dan koma).
     */
    public function test_perhitungan_dengan_desimal_berat(): void
    {
        // 2.4 kg x Rp 2.400 = 5760 -> dibulatkan ke kelipatan 100 terdekat = 5800
        $this->assertSame(5800, TransactionCalculator::calculate('2.4', 2400));
        // Desimal dengan koma: 2,4 kg x Rp 2.400 = 5760 -> 5800
        $this->assertSame(5800, TransactionCalculator::calculate('2,4', 2400));
        // 1.25 kg x Rp 1.000 = 1250 -> 1300
        $this->assertSame(1300, TransactionCalculator::calculate('1.25', 1000));
        // 0.5 kg x Rp 3.000 = 1500 -> 1500
        $this->assertSame(1500, TransactionCalculator::calculate('0.5', 3000));
    }

    /**
     * Uji coba pembulatan ke kelipatan 100 terdekat (setengah ke atas).
     * Kasus spesifik:
     * - 3.46 x 850 = 2941 -> 2900 (pembulatan: -41)
     * - 3.46 x 1000 = 3460 -> 3500 (pembulatan: +40)
     * - 3.45 x 1000 = 3450 -> 3500 (pembulatan: +50)
     * - 0.04 x 1000 = 40 -> 0 (pembulatan: -40)
     */
    public function test_pembulatan_rupiah_ke_kelipatan_terdekat_dan_rincian(): void
    {
        // 1. 3.46 x 850 = 2941 -> 2900 (pembulatan: -41)
        $detail1 = TransactionCalculator::calculateDetails('3.46', 850);
        $this->assertSame(2941, $detail1['nilai_sebelum_pembulatan']);
        $this->assertSame(-41, $detail1['pembulatan']);
        $this->assertSame(2900, $detail1['total_nilai']);
        $this->assertSame(2900, TransactionCalculator::calculate('3.46', 850));

        // 2. 3.46 x 1000 = 3460 -> 3500 (pembulatan: +40)
        $detail2 = TransactionCalculator::calculateDetails('3.46', 1000);
        $this->assertSame(3460, $detail2['nilai_sebelum_pembulatan']);
        $this->assertSame(40, $detail2['pembulatan']);
        $this->assertSame(3500, $detail2['total_nilai']);
        $this->assertSame(3500, TransactionCalculator::calculate('3.46', 1000));

        // 3. 3.45 x 1000 = 3450 -> 3500 (pembulatan: +50)
        $detail3 = TransactionCalculator::calculateDetails('3.45', 1000);
        $this->assertSame(3450, $detail3['nilai_sebelum_pembulatan']);
        $this->assertSame(50, $detail3['pembulatan']);
        $this->assertSame(3500, $detail3['total_nilai']);
        $this->assertSame(3500, TransactionCalculator::calculate('3.45', 1000));

        // 4. 0.04 x 1000 = 40 -> 0 (pembulatan: -40)
        $detail4 = TransactionCalculator::calculateDetails('0.04', 1000);
        $this->assertSame(40, $detail4['nilai_sebelum_pembulatan']);
        $this->assertSame(-40, $detail4['pembulatan']);
        $this->assertSame(0, $detail4['total_nilai']);
        $this->assertSame(0, TransactionCalculator::calculate('0.04', 1000));
    }

    /**
     * Uji coba berat lebih dari 2 desimal ditolak.
     */
    public function test_berat_lebih_dari_dua_desimal_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Berat sampah maksimal memiliki 2 angka di belakang koma (desimal).');

        TransactionCalculator::calculate('2.456', 2400);
    }

    /**
     * Uji coba input negatif pada berat ditolak dengan InvalidArgumentException.
     */
    public function test_berat_negatif_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Berat sampah tidak boleh bernilai negatif.');

        TransactionCalculator::calculate('-1.5', 2400);
    }

    /**
     * Uji coba input negatif pada harga per kg ditolak dengan InvalidArgumentException.
     */
    public function test_harga_negatif_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Harga per kilogram tidak boleh bernilai negatif.');

        TransactionCalculator::calculate('2.4', -2400);
    }

    /**
     * Uji coba input string non-numerik ditolak.
     */
    public function test_input_non_numerik_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);

        TransactionCalculator::calculate('bukan_angka', 2400);
    }
}
