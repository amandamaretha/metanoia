<?php

namespace Tests\Feature;

use App\Models\Petugas;
use App\Models\TransaksiKeuangan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TransaksiKeuanganTest extends TestCase
{
    use RefreshDatabase;

    protected Petugas $bendahara;

    protected Petugas $pengelola;

    protected Petugas $ketua;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->bendahara = new Petugas([
            'nama' => 'Bendahara Siti',
            'no_telp' => '6283333333333',
            'password' => 'secret123',
        ]);
        $this->bendahara->forceFill(['jabatan' => 'bendahara'])->save();

        $this->pengelola = new Petugas([
            'nama' => 'Pengelola Budi',
            'no_telp' => '6282222222222',
            'password' => 'secret123',
        ]);
        $this->pengelola->forceFill(['jabatan' => 'pengelola'])->save();

        $this->ketua = new Petugas([
            'nama' => 'Ketua Ahmad',
            'no_telp' => '6281111111111',
            'password' => 'secret123',
        ]);
        $this->ketua->forceFill(['jabatan' => 'ketua'])->save();
    }

    public function test_akses_halaman_transaksi_bendahara_hanya_untuk_bendahara(): void
    {
        // Tanpa login -> redirect ke login petugas
        $response = $this->get(route('bendahara.transaksi'));
        $response->assertRedirect(route('login.show', ['role' => 'petugas']));

        // Pengelola -> 403
        $this->actingAs($this->pengelola, 'petugas');
        $this->get(route('bendahara.transaksi'))->assertStatus(403);
        $this->get(route('bendahara.riwayat'))->assertStatus(403);
        $this->get(route('bendahara.laporan-keuangan'))->assertStatus(403);

        // Ketua -> 403
        $this->actingAs($this->ketua, 'petugas');
        $this->get(route('bendahara.transaksi'))->assertStatus(403);

        // Bendahara -> 200
        $this->actingAs($this->bendahara, 'petugas');
        $this->get(route('bendahara.transaksi'))->assertOk();
        $this->get(route('bendahara.riwayat'))->assertOk();
        $this->get(route('bendahara.laporan-keuangan'))->assertOk();
    }

    public function test_perhitungan_saldo_kas_dinamis_dari_transaksi(): void
    {
        $this->actingAs($this->bendahara, 'petugas');

        // Tambah Pemasukan Rp 500.000
        $response = $this->post(route('bendahara.transaksi.store'), [
            'nominal' => 500000,
            'tanggal' => '2026-10-20',
            'jenis' => 'pemasukan',
            'keterangan' => 'Penjualan sampah daur ulang',
        ]);
        $response->assertRedirect(route('bendahara.transaksi'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('transaksi_keuangan', [
            'petugas_id' => $this->bendahara->id,
            'nominal' => 500000,
            'jenis' => 'pemasukan',
            'keterangan' => 'Penjualan sampah daur ulang',
        ]);

        // Cek saldo di halaman transaksi
        $resTransaksi = $this->get(route('bendahara.transaksi'));
        $resTransaksi->assertSee('Rp 500.000');

        // Tambah Pengeluaran Rp 200.000
        $this->post(route('bendahara.transaksi.store'), [
            'nominal' => 200000,
            'tanggal' => '2026-10-21',
            'jenis' => 'pengeluaran',
            'keterangan' => 'Operasional logistik karung',
        ]);

        // Saldo sekarang: 500.000 - 200.000 = 300.000
        $resTransaksi = $this->get(route('bendahara.transaksi'));
        $resTransaksi->assertSee('Rp 300.000');
    }

    public function test_edit_transaksi_memperbarui_saldo_kas(): void
    {
        $this->actingAs($this->bendahara, 'petugas');

        $t = TransaksiKeuangan::create([
            'petugas_id' => $this->bendahara->id,
            'nominal' => 300000,
            'tanggal' => '2026-10-20',
            'jenis' => 'pemasukan',
            'keterangan' => 'Sponsorship kegiatan',
        ]);

        $this->get(route('bendahara.transaksi'))->assertSee('Rp 300.000');

        // Ubah nominal menjadi 450.000
        $response = $this->put(route('bendahara.transaksi.update', $t), [
            'nominal' => 450000,
            'tanggal' => '2026-10-20',
            'jenis' => 'pemasukan',
            'keterangan' => 'Sponsorship kegiatan (revisi)',
        ]);
        $response->assertRedirect(route('bendahara.riwayat'));

        $this->assertDatabaseHas('transaksi_keuangan', [
            'id' => $t->id,
            'nominal' => 450000,
            'keterangan' => 'Sponsorship kegiatan (revisi)',
        ]);

        // Saldo sekarang harus 450.000
        $this->get(route('bendahara.transaksi'))->assertSee('Rp 450.000');
    }

    public function test_hapus_transaksi_soft_delete_dan_mencatat_petugas_serta_memperbarui_saldo(): void
    {
        $this->actingAs($this->bendahara, 'petugas');

        $t1 = TransaksiKeuangan::create([
            'petugas_id' => $this->bendahara->id,
            'nominal' => 1000000,
            'tanggal' => '2026-10-20',
            'jenis' => 'pemasukan',
            'keterangan' => 'Dana Hibah',
        ]);

        $t2 = TransaksiKeuangan::create([
            'petugas_id' => $this->bendahara->id,
            'nominal' => 400000,
            'tanggal' => '2026-10-21',
            'jenis' => 'pengeluaran',
            'keterangan' => 'Konsumsi rapat',
        ]);

        // Saldo awal: 1.000.000 - 400.000 = 600.000
        $this->get(route('bendahara.transaksi'))->assertSee('Rp 600.000');

        // Hapus pengeluaran (t2)
        $response = $this->delete(route('bendahara.transaksi.destroy', $t2));
        $response->assertRedirect(route('bendahara.riwayat'));

        // Cek soft delete & dihapus_oleh
        $this->assertSoftDeleted('transaksi_keuangan', ['id' => $t2->id]);
        $t2Fresh = TransaksiKeuangan::withTrashed()->find($t2->id);
        $this->assertEquals($this->bendahara->id, $t2Fresh->dihapus_oleh);

        // Saldo otomatis kembali menjadi 1.000.000
        $this->get(route('bendahara.transaksi'))->assertSee('Rp 1.000.000');
    }

    public function test_upload_lampiran_pada_disk_privat_dan_hanya_bisa_diunduh_bendahara(): void
    {
        $this->actingAs($this->bendahara, 'petugas');

        $file = UploadedFile::fake()->create('nota_transaksi.pdf', 1024, 'application/pdf');

        $response = $this->post(route('bendahara.transaksi.store'), [
            'nominal' => 75000,
            'tanggal' => '2026-10-22',
            'jenis' => 'pengeluaran',
            'keterangan' => 'Beli ATK',
            'lampiran' => $file,
        ]);
        $response->assertRedirect(route('bendahara.transaksi'));

        $transaksi = TransaksiKeuangan::where('keterangan', 'Beli ATK')->firstOrFail();
        $this->assertNotNull($transaksi->lampiran);
        Storage::disk('local')->assertExists($transaksi->lampiran);

        // Akses lampiran oleh bendahara -> 200
        $resLampiran = $this->get(route('bendahara.transaksi.lampiran', $transaksi));
        $resLampiran->assertOk();

        // Akses lampiran oleh pengelola -> 403
        $this->actingAs($this->pengelola, 'petugas');
        $this->get(route('bendahara.transaksi.lampiran', $transaksi))->assertStatus(403);
    }

    public function test_validasi_lampiran_ditolak_jika_tipe_atau_ukuran_salah(): void
    {
        $this->actingAs($this->bendahara, 'petugas');

        // File ekstensi palsu / tipe salah (executable script)
        $invalidFile = UploadedFile::fake()->create('malicious.php', 100, 'text/x-php');

        $response = $this->from(route('bendahara.transaksi'))->post(route('bendahara.transaksi.store'), [
            'nominal' => 50000,
            'tanggal' => '2026-10-22',
            'jenis' => 'pengeluaran',
            'keterangan' => 'Tes lampiran salah',
            'lampiran' => $invalidFile,
        ]);
        $response->assertRedirect(route('bendahara.transaksi'));
        $response->assertSessionHasErrors('lampiran');

        // File melebihi batas 5 MB (5121 KB)
        $largeFile = UploadedFile::fake()->create('huge.pdf', 5200, 'application/pdf');

        $responseLarge = $this->from(route('bendahara.transaksi'))->post(route('bendahara.transaksi.store'), [
            'nominal' => 50000,
            'tanggal' => '2026-10-22',
            'jenis' => 'pengeluaran',
            'keterangan' => 'Tes lampiran terlalu besar',
            'lampiran' => $largeFile,
        ]);
        $responseLarge->assertRedirect(route('bendahara.transaksi'));
        $responseLarge->assertSessionHasErrors('lampiran');
    }

    public function test_download_riwayat_csv_streaming_dan_proteksi_csv_injection(): void
    {
        $this->actingAs($this->bendahara, 'petugas');

        TransaksiKeuangan::create([
            'petugas_id' => $this->bendahara->id,
            'nominal' => 150000,
            'tanggal' => '2026-10-22',
            'jenis' => 'pemasukan',
            'keterangan' => '=cmd|"/C calc"!A0',
        ]);

        $response = $this->get(route('bendahara.transaksi.download'));
        $response->assertOk();
        $this->assertEquals('text/csv; charset=UTF-8', $response->headers->get('content-type'));

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        // Keterangan yang diawali '=' harus di-escape dengan tanda kutip tunggal di awal
        $this->assertStringContainsString("'=cmd", $content);
    }

    public function test_laporan_keuangan_saldo_awal_sebelum_periode_dan_saldo_akhir(): void
    {
        $this->actingAs($this->bendahara, 'petugas');

        // Transaksi sebelum periode (sebelum 2026-10-10): Saldo awal = 100.000 - 30.000 = 70.000
        TransaksiKeuangan::create([
            'petugas_id' => $this->bendahara->id,
            'nominal' => 100000,
            'tanggal' => '2026-10-05',
            'jenis' => 'pemasukan',
            'keterangan' => 'Pemasukan Awal',
        ]);
        TransaksiKeuangan::create([
            'petugas_id' => $this->bendahara->id,
            'nominal' => 30000,
            'tanggal' => '2026-10-08',
            'jenis' => 'pengeluaran',
            'keterangan' => 'Pengeluaran Awal',
        ]);

        // Transaksi dalam periode (2026-10-10 s.d. 2026-10-25)
        TransaksiKeuangan::create([
            'petugas_id' => $this->bendahara->id,
            'nominal' => 250000,
            'tanggal' => '2026-10-15',
            'jenis' => 'pemasukan',
            'keterangan' => 'Pemasukan Periode',
        ]);
        TransaksiKeuangan::create([
            'petugas_id' => $this->bendahara->id,
            'nominal' => 50000,
            'tanggal' => '2026-10-20',
            'jenis' => 'pengeluaran',
            'keterangan' => 'Pengeluaran Periode',
        ]);

        // Akses laporan keuangan
        $response = $this->get(route('bendahara.laporan-keuangan', [
            'tanggal_mulai' => '2026-10-10',
            'tanggal_selesai' => '2026-10-25',
        ]));
        $response->assertOk();

        // Saldo Awal: 70.000
        $response->assertSee('70.000');
        // Total Pemasukan: 250.000
        $response->assertSee('250.000');
        // Total Pengeluaran: 50.000
        $response->assertSee('50.000');
        // Saldo Akhir: 70.000 + 250.000 - 50.000 = 270.000
        $response->assertSee('270.000');
    }

    public function test_laporan_keuangan_periode_tidak_valid_ditolak(): void
    {
        $this->actingAs($this->bendahara, 'petugas');

        // Tanggal selesai lebih awal dari tanggal mulai
        $response = $this->get(route('bendahara.laporan-keuangan', [
            'tanggal_mulai' => '2026-10-25',
            'tanggal_selesai' => '2026-10-10',
        ]));

        $response->assertSessionHasErrors('tanggal_selesai');
    }
}
