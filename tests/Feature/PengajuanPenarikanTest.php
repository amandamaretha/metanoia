<?php

namespace Tests\Feature;

use App\Models\MutasiSaldo;
use App\Models\Nasabah;
use App\Models\Penarikan;
use App\Models\Petugas;
use App\Services\PenarikanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class PengajuanPenarikanTest extends TestCase
{
    use RefreshDatabase;

    protected Nasabah $nasabahAktif;

    protected Nasabah $nasabahTidakAktif;

    protected Petugas $petugasKetua;

    protected Petugas $petugasPengelola;

    protected Petugas $petugasBendahara;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nasabahAktif = Nasabah::create([
            'nama' => 'Budi Nasabah',
            'nik' => '3201010101010001',
            'alamat' => 'Jl. Kebon Jeruk No. 1',
            'no_telp' => '6281234567890',
            'password' => 'secret123',
        ]);
        $this->nasabahAktif->forceFill(['saldo' => 100000, 'status' => 'aktif'])->save();

        $this->nasabahTidakAktif = Nasabah::create([
            'nama' => 'Siti Nonaktif',
            'nik' => '3201010101010002',
            'alamat' => 'Jl. Kebon Jeruk No. 2',
            'no_telp' => '6281234567891',
            'password' => 'secret123',
        ]);
        $this->nasabahTidakAktif->forceFill(['saldo' => 50000, 'status' => 'tidak_aktif'])->save();

        $this->petugasKetua = new Petugas([
            'nama' => 'Pak Ketua',
            'no_telp' => '6281111111111',
            'password' => 'secret123',
        ]);
        $this->petugasKetua->forceFill(['jabatan' => 'ketua'])->save();

        $this->petugasPengelola = new Petugas([
            'nama' => 'Petugas Pengelola',
            'no_telp' => '6282222222222',
            'password' => 'secret123',
        ]);
        $this->petugasPengelola->forceFill(['jabatan' => 'pengelola'])->save();

        $this->petugasBendahara = new Petugas([
            'nama' => 'Bu Bendahara',
            'no_telp' => '6283333333333',
            'password' => 'secret123',
        ]);
        $this->petugasBendahara->forceFill(['jabatan' => 'bendahara'])->save();
    }

    /**
     * 1. Nasabah berhasil mengajukan penarikan jika saldo cukup, akun aktif, dan tidak ada pengajuan pending.
     */
    public function test_nasabah_berhasil_mengajukan_penarikan(): void
    {
        $response = $this->actingAs($this->nasabahAktif, 'nasabah')
            ->post(route('nasabah.pengajuan-penarikan'), [
                'nominal' => 50000,
            ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('penarikan', [
            'nasabah_id' => $this->nasabahAktif->id,
            'nominal' => 50000,
            'status' => 'menunggu',
        ]);
    }

    /**
     * 2. Pengajuan ditolak jika nominal melebihi saldo nasabah.
     */
    public function test_pengajuan_ditolak_jika_nominal_melebihi_saldo(): void
    {
        $response = $this->actingAs($this->nasabahAktif, 'nasabah')
            ->post(route('nasabah.pengajuan-penarikan'), [
                'nominal' => 150000, // Saldo hanya 100.000
            ]);

        $response->assertSessionHasErrors(['nominal']);
        $this->assertDatabaseCount('penarikan', 0);
    }

    /**
     * 3. Pengajuan ditolak jika akun nasabah tidak aktif.
     */
    public function test_pengajuan_ditolak_jika_akun_nasabah_tidak_aktif(): void
    {
        $response = $this->actingAs($this->nasabahTidakAktif, 'nasabah')
            ->post(route('nasabah.pengajuan-penarikan'), [
                'nominal' => 20000,
            ]);

        $response->assertSessionHasErrors(['nominal']);
        $this->assertDatabaseCount('penarikan', 0);
    }

    /**
     * 4. Pengajuan ditolak jika nasabah sudah memiliki pengajuan berstatus 'menunggu'.
     */
    public function test_pengajuan_ditolak_jika_sudah_ada_pengajuan_menunggu(): void
    {
        Penarikan::create([
            'nasabah_id' => $this->nasabahAktif->id,
            'nominal' => 30000,
            'status' => 'menunggu',
            'tanggal_pengajuan' => now(),
        ]);

        $response = $this->actingAs($this->nasabahAktif, 'nasabah')
            ->post(route('nasabah.pengajuan-penarikan'), [
                'nominal' => 20000,
            ]);

        $response->assertSessionHasErrors(['nominal']);
        $this->assertDatabaseCount('penarikan', 1);
    }

    /**
     * 5. Nominal 0 atau negatif ditolak oleh validasi.
     */
    public function test_pengajuan_nominal_nol_atau_negatif_ditolak(): void
    {
        $response = $this->actingAs($this->nasabahAktif, 'nasabah')
            ->post(route('nasabah.pengajuan-penarikan'), [
                'nominal' => 0,
            ]);
        $response->assertSessionHasErrors(['nominal']);

        $responseNeg = $this->actingAs($this->nasabahAktif, 'nasabah')
            ->post(route('nasabah.pengajuan-penarikan'), [
                'nominal' => -5000,
            ]);
        $responseNeg->assertSessionHasErrors(['nominal']);
    }

    /**
     * 6. Ketua menyetujui pengajuan: status disetujui, saldo berkurang, mutasi_saldo dicatat.
     */
    public function test_ketua_berhasil_menyetujui_pengajuan(): void
    {
        $penarikan = Penarikan::create([
            'nasabah_id' => $this->nasabahAktif->id,
            'nominal' => 40000,
            'status' => 'menunggu',
            'tanggal_pengajuan' => now(),
        ]);

        $response = $this->actingAs($this->petugasKetua, 'petugas')
            ->post(route('ketua.pengajuan-penarikan.setujui', $penarikan), [
                'catatan' => 'Persetujuan disetujui untuk penarikan tabungan.',
            ]);

        $response->assertSessionHas('success', 'Pengajuan Berhasil Disetujui!');

        $penarikanFresh = $penarikan->fresh();
        $this->assertSame('disetujui', $penarikanFresh->status);
        $this->assertSame($this->petugasKetua->id, $penarikanFresh->diputuskan_oleh);
        $this->assertNotNull($penarikanFresh->tanggal_keputusan);
        $this->assertSame('Persetujuan disetujui untuk penarikan tabungan.', $penarikanFresh->catatan);

        // Saldo nasabah berkurang: 100.000 - 40.000 = 60.000
        $nasabahFresh = $this->nasabahAktif->fresh();
        $this->assertSame(60000, $nasabahFresh->saldo);

        // Mutasi saldo tercatat
        $mutasi = MutasiSaldo::where('referensi_tipe', Penarikan::class)
            ->where('referensi_id', $penarikan->id)
            ->first();

        $this->assertNotNull($mutasi);
        $this->assertSame('penarikan', $mutasi->jenis);
        $this->assertSame(40000, $mutasi->jumlah);
        $this->assertSame(60000, $mutasi->saldo_sesudah);
    }

    /**
     * 7. Jika saldo tidak cukup saat disetujui -> ditolak dengan pesan dan status tetap 'menunggu'.
     */
    public function test_saldo_tidak_cukup_saat_disetujui_status_tetap_menunggu(): void
    {
        $penarikan = Penarikan::create([
            'nasabah_id' => $this->nasabahAktif->id,
            'nominal' => 80000,
            'status' => 'menunggu',
            'tanggal_pengajuan' => now(),
        ]);

        // Simulasi saldo nasabah berkurang sebelum disetujui
        $this->nasabahAktif->forceFill(['saldo' => 30000])->save();

        $response = $this->actingAs($this->petugasKetua, 'petugas')
            ->post(route('ketua.pengajuan-penarikan.setujui', $penarikan), [
                'catatan' => 'Ingin disetujui tapi saldo berkurang',
            ]);

        $response->assertSessionHas('error');

        // Status TETAP 'menunggu'
        $penarikanFresh = $penarikan->fresh();
        $this->assertSame('menunggu', $penarikanFresh->status);
        $this->assertNull($penarikanFresh->diputuskan_oleh);

        // Saldo tidak berubah
        $this->assertSame(30000, $this->nasabahAktif->fresh()->saldo);

        // Mutasi saldo tidak boleh dibuat
        $this->assertDatabaseMissing('mutasi_saldo', [
            'referensi_tipe' => Penarikan::class,
            'referensi_id' => $penarikan->id,
        ]);
    }

    /**
     * 8. Ketua menolak pengajuan: status ditolak, saldo nasabah tidak berubah, tidak ada mutasi saldo.
     */
    public function test_ketua_menolak_pengajuan_tidak_mengubah_saldo(): void
    {
        $penarikan = Penarikan::create([
            'nasabah_id' => $this->nasabahAktif->id,
            'nominal' => 50000,
            'status' => 'menunggu',
            'tanggal_pengajuan' => now(),
        ]);

        $response = $this->actingAs($this->petugasKetua, 'petugas')
            ->post(route('ketua.pengajuan-penarikan.tolak', $penarikan), [
                'catatan' => 'Mohon maaf saldo kas fisik loket belum siap.',
            ]);

        $response->assertSessionHas('warning', 'Mohon Maaf Pengajuan ditolak!');

        $penarikanFresh = $penarikan->fresh();
        $this->assertSame('ditolak', $penarikanFresh->status);
        $this->assertSame($this->petugasKetua->id, $penarikanFresh->diputuskan_oleh);
        $this->assertSame('Mohon maaf saldo kas fisik loket belum siap.', $penarikanFresh->catatan);

        // Saldo tidak berubah
        $this->assertSame(100000, $this->nasabahAktif->fresh()->saldo);

        // Tidak ada mutasi saldo
        $this->assertDatabaseMissing('mutasi_saldo', [
            'referensi_tipe' => Penarikan::class,
            'referensi_id' => $penarikan->id,
        ]);
    }

    /**
     * 9. Keputusan ganda ditolak (pengajuan yang bukan 'menunggu' tidak bisa diputuskan lagi).
     */
    public function test_keputusan_ganda_pada_pengajuan_ditolak(): void
    {
        $penarikan = Penarikan::create([
            'nasabah_id' => $this->nasabahAktif->id,
            'nominal' => 30000,
            'status' => 'disetujui',
            'diputuskan_oleh' => $this->petugasKetua->id,
            'tanggal_pengajuan' => now(),
            'tanggal_keputusan' => now(),
        ]);

        $service = app(PenarikanService::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Keputusan sudah pernah diambil');

        $service->setujui($penarikan, $this->petugasKetua->id);
    }

    /**
     * 10. Jabatan selain ketua (pengelola / bendahara) mendapat 403 saat mengakses pengajuan penarikan ketua.
     */
    public function test_jabatan_selain_ketua_mendapat_403(): void
    {
        $penarikan = Penarikan::create([
            'nasabah_id' => $this->nasabahAktif->id,
            'nominal' => 25000,
            'status' => 'menunggu',
            'tanggal_pengajuan' => now(),
        ]);

        // Pengelola mencoba mengakses halaman daftar
        $this->actingAs($this->petugasPengelola, 'petugas')
            ->get(route('ketua.pengajuan-penarikan'))
            ->assertStatus(403);

        // Bendahara mencoba menyetujui
        $this->actingAs($this->petugasBendahara, 'petugas')
            ->post(route('ketua.pengajuan-penarikan.setujui', $penarikan))
            ->assertStatus(403);

        // Pengelola mencoba menolak
        $this->actingAs($this->petugasPengelola, 'petugas')
            ->post(route('ketua.pengajuan-penarikan.tolak', $penarikan))
            ->assertStatus(403);
    }

    /**
     * 11. Masked NIK ditampilkan pada halaman detail dan daftar ketua.
     */
    public function test_masked_nik_tampil_pada_halaman_ketua(): void
    {
        $penarikan = Penarikan::create([
            'nasabah_id' => $this->nasabahAktif->id,
            'nominal' => 20000,
            'status' => 'menunggu',
            'tanggal_pengajuan' => now(),
        ]);

        $response = $this->actingAs($this->petugasKetua, 'petugas')
            ->get(route('ketua.pengajuan-penarikan.show', $penarikan));

        $response->assertStatus(200);
        $response->assertSee($this->nasabahAktif->masked_nik);
        $response->assertDontSee('3201010101010001'); // NIK utuh tidak boleh bocor
    }
}
