<?php

namespace Tests\Feature;

use App\Http\Controllers\Pengelola\LaporanController;
use App\Models\HargaSampah;
use App\Models\JenisSampah;
use App\Models\Nasabah;
use App\Models\Penjualan;
use App\Models\Petugas;
use App\Models\Setoran;
use App\Models\TransaksiKeuangan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class LaporanPenjualanTest extends TestCase
{
    use RefreshDatabase;

    protected Petugas $petugasPengelola;

    protected Petugas $petugasKetua;

    protected Petugas $petugasBendahara;

    protected Nasabah $nasabah;

    protected JenisSampah $sampahKardus;

    protected JenisSampah $sampahBotol;

    protected function setUp(): void
    {
        parent::setUp();

        config(['metanoia.penjualan.batas_minimum_kg' => 100]);

        $this->petugasPengelola = new Petugas([
            'nama' => 'Pengelola Utama',
            'no_telp' => '6281200000021',
            'password' => 'secret123',
        ]);
        $this->petugasPengelola->forceFill(['jabatan' => 'pengelola'])->save();

        $this->petugasKetua = new Petugas([
            'nama' => 'Ketua Metanoia',
            'no_telp' => '6281200000022',
            'password' => 'secret123',
        ]);
        $this->petugasKetua->forceFill(['jabatan' => 'ketua'])->save();

        $this->petugasBendahara = new Petugas([
            'nama' => 'Bendahara Metanoia',
            'no_telp' => '6281200000023',
            'password' => 'secret123',
        ]);
        $this->petugasBendahara->forceFill(['jabatan' => 'bendahara'])->save();

        $this->nasabah = Nasabah::create([
            'nama' => 'Nasabah Lestari',
            'nik' => '3201010101010031',
            'alamat' => 'Jl. Mawar Indah No. 12',
            'no_telp' => '6281200000031',
            'password' => 'secret123',
        ]);
        $this->nasabah->forceFill(['status' => 'aktif', 'saldo' => 50000])->save();

        $this->sampahKardus = JenisSampah::create([
            'kode' => 'KRD-01',
            'nama' => 'Kardus Cokelat',
            'deskripsi' => 'Kardus bekas bersih',
            'kategori' => 'Kertas',
        ]);

        HargaSampah::create([
            'jenis_sampah_id' => $this->sampahKardus->id,
            'kondisi' => 'sudah_dipilah',
            'harga_per_kg' => 2000,
            'tanggal_mulai' => '2026-01-01',
        ]);

        $this->sampahBotol = JenisSampah::create([
            'kode' => 'BTL-01',
            'nama' => 'Botol Plastik PET',
            'deskripsi' => 'Botol air mineral bersih',
            'kategori' => 'Plastik',
        ]);

        HargaSampah::create([
            'jenis_sampah_id' => $this->sampahBotol->id,
            'kondisi' => 'sudah_dipilah',
            'harga_per_kg' => 3000,
            'tanggal_mulai' => '2026-01-01',
        ]);
    }

    /**
     * Otorisasi: Hanya petugas pengelola yang berhak mengakses menu Laporan dan Penjualan.
     */
    public function test_akses_laporan_dan_penjualan_hanya_untuk_pengelola(): void
    {
        // 1. Guest diarahkan ke login
        $this->get(route('pengelola.laporan'))->assertRedirect(route('login.show', 'petugas'));
        $this->get(route('pengelola.laporan.penjualan'))->assertRedirect(route('login.show', 'petugas'));

        // 2. Ketua diizinkan melihat laporan & penjualan, tetapi dilarang mutasi penjualan
        $this->actingAs($this->petugasKetua, 'petugas')
            ->get(route('pengelola.laporan'))
            ->assertOk();

        $this->actingAs($this->petugasKetua, 'petugas')
            ->get(route('pengelola.laporan.penjualan'))
            ->assertOk();

        $this->actingAs($this->petugasKetua, 'petugas')
            ->post(route('pengelola.penjualan.store'), [
                'tanggal_pengangkutan' => now()->toDateString(),
                'total_berat' => 10,
                'total_nilai' => 10000,
            ])
            ->assertForbidden();

        // 3. Petugas bendahara dilarang mengakses laporan pengelola (403)
        $this->actingAs($this->petugasBendahara, 'petugas')
            ->get(route('pengelola.laporan'))
            ->assertForbidden();

        $this->actingAs($this->petugasBendahara, 'petugas')
            ->get(route('pengelola.laporan.penjualan'))
            ->assertForbidden();

        // 4. Petugas pengelola diizinkan melihat dan mengelola (200)
        $this->actingAs($this->petugasPengelola, 'petugas')
            ->get(route('pengelola.laporan'))
            ->assertOk()
            ->assertViewIs('dashboard.laporan');

        $this->actingAs($this->petugasPengelola, 'petugas')
            ->get(route('pengelola.laporan.penjualan'))
            ->assertOk()
            ->assertViewIs('dashboard.penjualan');
    }

    /**
     * Laporan Setoran: Agregat bulan berjalan, ringkasan, dan rincian per jenis sampah.
     */
    public function test_laporan_setoran_agregat_bulan_berjalan(): void
    {
        $now = Carbon::now();

        // Transaksi di bulan berjalan
        Setoran::create([
            'nasabah_id' => $this->nasabah->id,
            'petugas_id' => $this->petugasPengelola->id,
            'jenis_sampah_id' => $this->sampahKardus->id,
            'kondisi' => 'sudah_dipilah',
            'berat' => 15.0,
            'harga_per_kg' => 2000,
            'total_nilai' => 30000,
            'tanggal' => $now->copy()->startOfMonth()->addDays(2),
        ]);

        Setoran::create([
            'nasabah_id' => $this->nasabah->id,
            'petugas_id' => $this->petugasPengelola->id,
            'jenis_sampah_id' => $this->sampahBotol->id,
            'kondisi' => 'sudah_dipilah',
            'berat' => 10.0,
            'harga_per_kg' => 3000,
            'total_nilai' => 30000,
            'tanggal' => $now->copy()->startOfMonth()->addDays(5),
        ]);

        // Transaksi di luar bulan berjalan (bulan lalu) -> tidak boleh masuk agregat default
        Setoran::create([
            'nasabah_id' => $this->nasabah->id,
            'petugas_id' => $this->petugasPengelola->id,
            'jenis_sampah_id' => $this->sampahKardus->id,
            'kondisi' => 'sudah_dipilah',
            'berat' => 50.0,
            'harga_per_kg' => 2000,
            'total_nilai' => 100000,
            'tanggal' => $now->copy()->subMonth()->startOfMonth(),
        ]);

        $response = $this->actingAs($this->petugasPengelola, 'petugas')
            ->get(route('pengelola.laporan'));

        $response->assertOk();
        $response->assertViewHas('ringkasan', function ($ringkasan) {
            return $ringkasan['jumlah_transaksi'] === 2
                && $ringkasan['total_berat'] == 25.0
                && $ringkasan['nilai_setoran'] === 60000
                && $ringkasan['bagian_nasabah'] === '-';
        });

        $response->assertViewHas('kategoriLabels', function ($labels) {
            return in_array('Kertas', $labels) && in_array('Plastik', $labels);
        });

        $response->assertViewHas('rincianJenis', function ($rincian) {
            return $rincian->count() === 2;
        });
    }

    /**
     * Laporan Setoran: Filter rentang tanggal opsional.
     */
    public function test_laporan_setoran_filter_rentang_tanggal(): void
    {
        Setoran::create([
            'nasabah_id' => $this->nasabah->id,
            'petugas_id' => $this->petugasPengelola->id,
            'jenis_sampah_id' => $this->sampahKardus->id,
            'kondisi' => 'sudah_dipilah',
            'berat' => 20.0,
            'harga_per_kg' => 2000,
            'total_nilai' => 40000,
            'tanggal' => '2026-05-10',
        ]);

        Setoran::create([
            'nasabah_id' => $this->nasabah->id,
            'petugas_id' => $this->petugasPengelola->id,
            'jenis_sampah_id' => $this->sampahBotol->id,
            'kondisi' => 'sudah_dipilah',
            'berat' => 30.0,
            'harga_per_kg' => 3000,
            'total_nilai' => 90000,
            'tanggal' => '2026-06-15',
        ]);

        // Filter hanya untuk bulan Mei 2026
        $response = $this->actingAs($this->petugasPengelola, 'petugas')
            ->get(route('pengelola.laporan', [
                'tanggal_mulai' => '2026-05-01',
                'tanggal_selesai' => '2026-05-31',
            ]));

        $response->assertOk();
        $response->assertViewHas('ringkasan', function ($ringkasan) {
            return $ringkasan['jumlah_transaksi'] === 1
                && $ringkasan['total_berat'] == 20.0
                && $ringkasan['nilai_setoran'] === 40000;
        });
    }

    /**
     * Download Data: CSV streaming dan perlindungan CSV Injection.
     * Karakter formula =, +, -, @ pada sel diberi awalan tanda kutip tunggal (').
     */
    public function test_download_csv_streaming_dan_proteksi_csv_injection(): void
    {
        $controller = new LaporanController;

        // Uji sanitasi unit untuk formula injection
        $this->assertSame("'=SUM(A1:A10)", $controller->sanitizeCsvCell('=SUM(A1:A10)'));
        $this->assertSame("'+123456", $controller->sanitizeCsvCell('+123456'));
        $this->assertSame("'-5000", $controller->sanitizeCsvCell('-5000'));
        $this->assertSame("'@cmd|' /C calc'!A0", $controller->sanitizeCsvCell("@cmd|' /C calc'!A0"));
        $this->assertSame('Kertas Bersih', $controller->sanitizeCsvCell('Kertas Bersih'));
        $this->assertSame('100', $controller->sanitizeCsvCell(100));

        // Buat jenis sampah dengan nama diawali simbol formula berisiko
        $sampahInjeksi = JenisSampah::create([
            'kode' => '=INJ-01',
            'nama' => '=CMD(calc)',
            'deskripsi' => 'Uji formula injection',
            'kategori' => '+PlastikFormula',
        ]);

        Setoran::create([
            'nasabah_id' => $this->nasabah->id,
            'petugas_id' => $this->petugasPengelola->id,
            'jenis_sampah_id' => $sampahInjeksi->id,
            'kondisi' => 'sudah_dipilah',
            'berat' => 12.5,
            'harga_per_kg' => 1000,
            'total_nilai' => 12500,
            'tanggal' => now(),
        ]);

        $response = $this->actingAs($this->petugasPengelola, 'petugas')
            ->get(route('pengelola.laporan.download'));

        $response->assertOk();
        $this->assertTrue($response->headers->contains('content-type', 'text/csv; charset=UTF-8'));

        // Baca isi stream
        ob_start();
        $response->sendContent();
        $csvContent = ob_get_clean();

        // Verifikasi sel diawali tanda kutip tunggal untuk proteksi CSV injection
        $this->assertStringContainsString("'=CMD(calc) (=INJ-01)", $csvContent);
        $this->assertStringContainsString("'+PlastikFormula", $csvContent);
        $this->assertStringContainsString('Jumlah Transaksi', $csvContent);
    }

    /**
     * Progres Penjualan ke BSI dan Perhitungan Stok Gudang Saat Ini.
     * Batas minimum 100 Kg dari config, stok = total setoran - total penjualan aktif.
     */
    public function test_penjualan_progres_dan_stok_saat_ini(): void
    {
        // Setoran 85 Kg
        Setoran::create([
            'nasabah_id' => $this->nasabah->id,
            'petugas_id' => $this->petugasPengelola->id,
            'jenis_sampah_id' => $this->sampahKardus->id,
            'kondisi' => 'sudah_dipilah',
            'berat' => 85.0,
            'harga_per_kg' => 2000,
            'total_nilai' => 170000,
            'tanggal' => now(),
        ]);

        // Belum ada penjualan -> Stok 85 Kg, Progres 85%, Kurang 15 Kg
        $response = $this->actingAs($this->petugasPengelola, 'petugas')
            ->get(route('pengelola.laporan.penjualan'));

        $response->assertOk();
        $response->assertViewHas('stokSaatIni', 85.0);
        $response->assertViewHas('batasMinimumKg', 100.0);
        $response->assertViewHas('progresPersen', 85.0);
        $response->assertViewHas('kurangKg', 15.0);

        // Catat penjualan 50 Kg
        Penjualan::create([
            'tanggal_pengangkutan' => now()->toDateString(),
            'total_berat' => 50.0,
            'total_nilai' => 150000,
        ]);

        // Setelah penjualan 50 Kg -> Stok 35 Kg, Progres 35%, Kurang 65 Kg
        $responseAfter = $this->actingAs($this->petugasPengelola, 'petugas')
            ->get(route('pengelola.laporan.penjualan'));

        $responseAfter->assertOk();
        $responseAfter->assertViewHas('stokSaatIni', 35.0);
        $responseAfter->assertViewHas('progresPersen', 35.0);
        $responseAfter->assertViewHas('kurangKg', 65.0);
    }

    /**
     * Catat Penjualan Manual: Nomor otomatis T00001 dari ID setelah insert (atomik).
     */
    public function test_catat_penjualan_manual_nomor_otomatis_atomik(): void
    {
        $response = $this->actingAs($this->petugasPengelola, 'petugas')
            ->post(route('pengelola.penjualan.store'), [
                'tanggal_pengangkutan' => '2026-10-26',
                'total_berat' => 75.50,
                'total_nilai' => 300000,
            ]);

        $response->assertRedirect(route('pengelola.laporan.penjualan'));
        $response->assertSessionHas('success');

        $penjualan = Penjualan::first();
        $this->assertNotNull($penjualan);
        $this->assertSame('T'.str_pad((string) $penjualan->id, 5, '0', STR_PAD_LEFT), $penjualan->nomor);
        $this->assertEquals(75.50, $penjualan->total_berat);
        $this->assertSame(300000, $penjualan->total_nilai);

        // Insert kedua
        $this->actingAs($this->petugasPengelola, 'petugas')
            ->post(route('pengelola.penjualan.store'), [
                'tanggal_pengangkutan' => '2026-10-27',
                'total_berat' => 120.0,
                'total_nilai' => 450000,
            ]);

        $penjualan2 = Penjualan::latest('id')->first();
        $this->assertSame('T'.str_pad((string) $penjualan2->id, 5, '0', STR_PAD_LEFT), $penjualan2->nomor);
        $this->assertNotEquals($penjualan->nomor, $penjualan2->nomor);
    }

    /**
     * Catat Penjualan: Otomatis mencatat transaksi keuangan pemasukan di DB::transaction.
     */
    public function test_catat_penjualan_mencatat_transaksi_keuangan_pemasukan_otomatis(): void
    {
        $this->actingAs($this->petugasPengelola, 'petugas')
            ->post(route('pengelola.penjualan.store'), [
                'tanggal_pengangkutan' => '2026-11-08',
                'total_berat' => 80.0,
                'total_nilai' => 250000,
            ]);

        $penjualan = Penjualan::first();
        $this->assertNotNull($penjualan);

        $transaksi = TransaksiKeuangan::first();
        $this->assertNotNull($transaksi);
        $this->assertSame($this->petugasPengelola->id, $transaksi->petugas_id);
        $this->assertSame('pemasukan', $transaksi->jenis);
        $this->assertSame(250000, $transaksi->nominal);
        $this->assertSame('2026-11-08', $transaksi->tanggal->toDateString());
        $this->assertSame("Pemasukan dari penjualan sampah nomor {$penjualan->nomor}", $transaksi->keterangan);
    }

    /**
     * Validasi input formulir Catat Penjualan Manual.
     */
    public function test_validasi_catat_penjualan_manual(): void
    {
        $response = $this->actingAs($this->petugasPengelola, 'petugas')
            ->post(route('pengelola.penjualan.store'), [
                'tanggal_pengangkutan' => '',
                'total_berat' => 0,
                'total_nilai' => -100,
            ]);

        $response->assertSessionHasErrors(['tanggal_pengangkutan', 'total_berat', 'total_nilai']);
    }

    /**
     * Detail Penjualan: Petugas boleh mengubah tanggal, berat, atau total nilai.
     */
    public function test_detail_penjualan_dapat_mengubah_tanggal_berat_dan_nilai(): void
    {
        $penjualan = Penjualan::create([
            'tanggal_pengangkutan' => '2026-10-20',
            'total_berat' => 50.0,
            'total_nilai' => 150000,
        ]);

        $originalNomor = $penjualan->nomor;

        $response = $this->actingAs($this->petugasPengelola, 'petugas')
            ->put(route('pengelola.penjualan.update', $penjualan), [
                'tanggal_pengangkutan' => '2026-10-25',
                'total_berat' => 65.50,
                'total_nilai' => 200000,
            ]);

        $response->assertRedirect(route('pengelola.laporan.penjualan'));
        $response->assertSessionHas('success');

        $fresh = $penjualan->fresh();
        $this->assertSame('2026-10-25', $fresh->tanggal_pengangkutan->toDateString());
        $this->assertEquals(65.50, $fresh->total_berat);
        $this->assertSame(200000, $fresh->total_nilai);
        $this->assertSame($originalNomor, $fresh->nomor);
    }

    /**
     * Nomor Transaksi Penjualan tidak bisa diubah (immutable).
     */
    public function test_nomor_transaksi_penjualan_tidak_bisa_diubah(): void
    {
        $penjualan = Penjualan::create([
            'tanggal_pengangkutan' => '2026-10-20',
            'total_berat' => 50.0,
            'total_nilai' => 150000,
        ]);

        $originalNomor = $penjualan->nomor;

        // Model melempar InvalidArgumentException jika properti nomor diubah saat update
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Nomor transaksi penjualan tidak dapat diubah.');

        $penjualan->nomor = 'T99999';
        $penjualan->save();

        // Pastikan tidak berubah di database
        $this->assertSame($originalNomor, $penjualan->fresh()->nomor);
    }

    /**
     * Hapus Penjualan: Soft delete dan mencatat siapa petugas yang menghapusnya.
     */
    public function test_hapus_penjualan_melakukan_soft_delete_dan_mencatat_petugas_penghapus(): void
    {
        // 1. Buat setoran 100 Kg
        Setoran::create([
            'nasabah_id' => $this->nasabah->id,
            'petugas_id' => $this->petugasPengelola->id,
            'jenis_sampah_id' => $this->sampahKardus->id,
            'kondisi' => 'sudah_dipilah',
            'berat' => 100.0,
            'harga_per_kg' => 2000,
            'total_nilai' => 200000,
            'tanggal' => now(),
        ]);

        // 2. Buat penjualan 40 Kg
        $penjualan = Penjualan::create([
            'tanggal_pengangkutan' => now()->toDateString(),
            'total_berat' => 40.0,
            'total_nilai' => 120000,
        ]);

        // Sebelum hapus, stok gudang = 100 - 40 = 60 Kg
        $this->assertEquals(60.0, Setoran::sum('berat') - Penjualan::sum('total_berat'));

        // 3. Petugas pengelola menghapus transaksi penjualan
        $response = $this->actingAs($this->petugasPengelola, 'petugas')
            ->delete(route('pengelola.penjualan.destroy', $penjualan));

        $response->assertRedirect(route('pengelola.laporan.penjualan'));
        $response->assertSessionHas('success');

        // 4. Verifikasi bahwa record telah di-soft-delete dan mencatat ID petugas
        $this->assertSoftDeleted('penjualan', ['id' => $penjualan->id]);

        $deletedPenjualan = Penjualan::withTrashed()->find($penjualan->id);
        $this->assertSame($this->petugasPengelola->id, $deletedPenjualan->dihapus_oleh);
        $this->assertNotNull($deletedPenjualan->deleted_at);

        // 5. Setelah dihapus, penjualan tidak lagi mengurangi stok gudang (stok kembali 100 Kg)
        $this->assertEquals(100.0, Setoran::sum('berat') - Penjualan::sum('total_berat'));

        $responseIndex = $this->actingAs($this->petugasPengelola, 'petugas')
            ->get(route('pengelola.laporan.penjualan'));
        $responseIndex->assertViewHas('stokSaatIni', 100.0);
        $responseIndex->assertViewHas('progresPersen', 100.0);
    }

    /**
     * Pencarian Riwayat Penjualan berdasarkan nomor transaksi.
     */
    public function test_pencarian_riwayat_penjualan(): void
    {
        $p1 = Penjualan::create([
            'tanggal_pengangkutan' => '2026-10-10',
            'total_berat' => 25.0,
            'total_nilai' => 75000,
        ]);

        $p2 = Penjualan::create([
            'tanggal_pengangkutan' => '2026-10-15',
            'total_berat' => 45.0,
            'total_nilai' => 135000,
        ]);

        $response = $this->actingAs($this->petugasPengelola, 'petugas')
            ->get(route('pengelola.laporan.penjualan', [
                'tanggal_mulai' => '2026-10-01',
                'tanggal_selesai' => '2026-10-31',
                'search' => $p1->nomor,
            ]));

        $response->assertOk();
        $response->assertViewHas('riwayatPenjualan', function ($riwayat) use ($p1, $p2) {
            return $riwayat->contains('id', $p1->id) && ! $riwayat->contains('id', $p2->id);
        });
    }

    /**
     * Laporan Penjualan: Filter rentang tanggal opsional (DFD 6.0).
     */
    public function test_laporan_penjualan_filter_rentang_tanggal(): void
    {
        $p1 = Penjualan::create([
            'tanggal_pengangkutan' => '2026-05-10',
            'total_berat' => 30.0,
            'total_nilai' => 90000,
        ]);

        $p2 = Penjualan::create([
            'tanggal_pengangkutan' => '2026-06-15',
            'total_berat' => 40.0,
            'total_nilai' => 120000,
        ]);

        $response = $this->actingAs($this->petugasPengelola, 'petugas')
            ->get(route('pengelola.laporan.penjualan', [
                'tanggal_mulai' => '2026-05-01',
                'tanggal_selesai' => '2026-05-31',
            ]));

        $response->assertOk();
        $response->assertViewHas('startDate', '2026-05-01');
        $response->assertViewHas('endDate', '2026-05-31');
        $response->assertViewHas('riwayatPenjualan', function ($riwayat) use ($p1, $p2) {
            return $riwayat->contains('id', $p1->id) && ! $riwayat->contains('id', $p2->id);
        });
    }
}
