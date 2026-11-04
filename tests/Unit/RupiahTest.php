<?php

namespace Tests\Unit;

use App\Support\Rupiah;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class RupiahTest extends TestCase
{
    public function test_parse_valid_rupiah(): void
    {
        $this->assertSame(1500, Rupiah::parse('1.500'));
        $this->assertSame(12500000, Rupiah::parse('12.500.000'));
        $this->assertSame(500, Rupiah::parse('500'));
        $this->assertSame(4000, Rupiah::parse('4000'));
        $this->assertSame(1000000, Rupiah::parse(' 1.000.000 '));
    }

    public function test_tolak_koma_dan_desimal(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Rupiah::parse('1500,50');
    }

    public function test_tolak_titik_salah_posisi(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Rupiah::parse('1.50');
    }

    public function test_tolak_huruf(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Rupiah::parse('abc');
    }

    public function test_tolak_nol(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Rupiah::parse('0');
    }

    public function test_tolak_negatif(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Rupiah::parse('-500');
    }

    public function test_tolak_notasi_ilmiah(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Rupiah::parse('1e3');
    }
}

