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
     * Uji coba perhitungan dengan desimal berat (titik dan koma).
     */
    public function test_perhitungan_dengan_desimal_berat(): void
    {
        // 2.4 kg x Rp 2.400 = 5760
        $this->assertSame(5760, TransactionCalculator::calculate('2.4', 2400));
        // Desimal dengan koma: 2,4 kg x Rp 2.400 = 5760
        $this->assertSame(5760, TransactionCalculator::calculate('2,4', 2400));
        // 1.25 kg x Rp 1.000 = 1250
        $this->assertSame(1250, TransactionCalculator::calculate('1.25', 1000));
        // 0.5 kg x Rp 3.000 = 1500
        $this->assertSame(1500, TransactionCalculator::calculate('0.5', 3000));
    }

    /**
     * Uji coba pembulatan half-up ke atas dan ke bawah pada bilangan rupiah.
     */
    public function test_pembulatan_rupiah_ke_terdekat(): void
    {
        // 2.456 kg x Rp 2.400 = 5894.4 -> dibulatkan ke bawah menjadi 5894
        $this->assertSame(5894, TransactionCalculator::calculate('2.456', 2400));

        // 1.5 kg x Rp 3 = 4.5 -> dibulatkan ke atas (half-up) menjadi 5
        $this->assertSame(5, TransactionCalculator::calculate('1.5', 3));

        // 1.4 kg x Rp 3 = 4.2 -> dibulatkan ke bawah menjadi 4
        $this->assertSame(4, TransactionCalculator::calculate('1.4', 3));

        // 2.4565 kg x Rp 2.000 = 4913.0 -> tepat 4913
        $this->assertSame(4913, TransactionCalculator::calculate('2.4565', 2000));
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
