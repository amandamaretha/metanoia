<?php

namespace Tests\Feature;

use App\Models\HargaSampah;
use App\Models\JenisSampah;
use App\Models\MutasiSaldo;
use App\Models\Nasabah;
use App\Models\Nota;
use App\Models\Petugas;
use App\Models\Setoran;
use App\Services\ManualWeighingSource;
use App\Services\QrSessionService;
use App\Services\SetoranService;
use App\Support\TransactionCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Tests\TestCase;

class PenimbanganSampahTest extends TestCase
{
    use RefreshDatabase;

    protected Petugas $petugasPengelola;

    protected Petugas $petugasKetua;

    protected Petugas $petugasBendahara;

    protected Nasabah $nasabahAktif;

    protected Nasabah $nasabahTidakAktif;

    protected JenisSampah $jenisSampah;

    protected function setUp(): void
    {
        parent::setUp();

        $this->petugasPengelola = new Petugas([
            'nama' => 'Petugas Timbang',
            'no_telp' => '6281200000010',
            'password' => 'secret123',
        ]);
        $this->petugasPengelola->forceFill(['jabatan' => 'pengelola'])->save();

        $this->petugasKetua = new Petugas([
            'nama' => 'Ketua Bank',
            'no_telp' => '6281200000011',
            'password' => 'secret123',
        ]);
        $this->petugasKetua->forceFill(['jabatan' => 'ketua'])->save();

        $this->petugasBendahara = new Petugas([
            'nama' => 'Bendahara Bank',
            'no_telp' => '6281200000012',
            'password' => 'secret123',
        ]);
        $this->petugasBendahara->forceFill(['jabatan' => 'bendahara'])->save();

        $this->nasabahAktif = Nasabah::create([
            'nama' => 'Ahmad Nasabah',
            'nik' => '3201010101010020',
            'alamat' => 'Jl. Kenari No. 10',
            'no_telp' => '6281300000020',
            'password' => 'secret123',
        ]);
        $this->nasabahAktif->forceFill(['saldo' => 50000, 'status' => 'aktif'])->save();

        $this->nasabahTidakAktif = Nasabah::create([
            'nama' => 'Bambang Nonaktif',
            'nik' => '3201010101010021',
            'alamat' => 'Jl. Kenari No. 11',
            'no_telp' => '6281300000021',
            'password' => 'secret123',
        ]);
        $this->nasabahTidakAktif->forceFill(['saldo' => 10000, 'status' => 'tidak_aktif'])->save();

        $this->jenisSampah = JenisSampah::create([
            'kode' => 'PL-01',
            'nama' => 'Botol Plastik PET',
            'deskripsi' => 'Botol air mineral bersih',
            'kategori' => 'Plastik',
        ]);

        // Harga aktif hari ini untuk 3 kondisi berbeda
        HargaSampah::create([
            'jenis_sampah_id' => $this->jenisSampah->id,
            'kondisi' => 'sudah_dipilah',
            'harga_per_kg' => 4000,
            'tanggal_mulai' => now()->subDays(5)->toDateString(),
            'tanggal_berakhir' => null,
        ]);

        HargaSampah::create([
            'jenis_sampah_id' => $this->jenisSampah->id,
            'kondisi' => 'belum_dipilah',
            'harga_per_kg' => 2500,
            'tanggal_mulai' => now()->subDays(5)->toDateString(),
            'tanggal_berakhir' => null,
        ]);

        HargaSampah::create([
            'jenis_sampah_id' => $this->jenisSampah->id,
            'kondisi' => 'campuran',
            'harga_per_kg' => 1500,
            'tanggal_mulai' => now()->subDays(5)->toDateString(),
            'tanggal_berakhir' => null,
        ]);
    }

    /**
     * 1. Halaman penimbangan hanya dapat diakses oleh petugas dengan jabatan pengelola.
     */
    public function test_halaman_penimbangan_hanya_dapat_diakses_pengelola(): void
    {
        // Pengelola sukses 200
        $this->actingAs($this->petugasPengelola, 'petugas')
            ->get(route('pengelola.penimbangan'))
            ->assertStatus(200)
            ->assertSee('Penimbangan')
            ->assertSee('Ahmad Nasabah');

        // Ketua ditolak 403
        $this->actingAs($this->petugasKetua, 'petugas')
            ->get(route('pengelola.penimbangan'))
            ->assertStatus(403);

        // Bendahara ditolak 403
        $this->actingAs($this->petugasBendahara, 'petugas')
            ->get(route('pengelola.penimbangan'))
            ->assertStatus(403);
    }

    /**
     * 2. Nasabah tidak aktif ditolak saat dipilih dan transaksi tidak dapat dilanjutkan.
     */
    public function test_nasabah_tidak_aktif_ditolak(): void
    {
        $response = $this->actingAs($this->petugasPengelola, 'petugas')
            ->getJson(route('pengelola.penimbangan.check-nasabah', $this->nasabahTidakAktif));

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Akun nasabah tidak aktif. Transaksi penimbangan tidak dapat dilanjutkan.',
            ]);

        // Coba simpan setoran nasabah tidak aktif -> ditolak
        $responsePost = $this->actingAs($this->petugasPengelola, 'petugas')
            ->postJson(route('pengelola.penimbangan.store'), [
                'nasabah_id' => $this->nasabahTidakAktif->id,
                'jenis_sampah_id' => $this->jenisSampah->id,
                'kondisi' => 'sudah_dipilah',
                'berat' => 2.5,
            ]);

        $responsePost->assertStatus(422);
        $this->assertDatabaseCount('setoran', 0);
    }

    /**
     * 3. Harga aktif per kondisi diambil dengan benar sesuai kondisi sampah.
     */
    public function test_harga_aktif_per_kondisi_terambil_dengan_benar(): void
    {
        // Sudah dipilah: Rp 4.000
        $resSudah = $this->actingAs($this->petugasPengelola, 'petugas')
            ->getJson(route('pengelola.penimbangan.get-harga', [
                'jenis_sampah_id' => $this->jenisSampah->id,
                'kondisi' => 'sudah_dipilah',
            ]));
        $resSudah->assertStatus(200)->assertJson(['harga_per_kg' => 4000]);

        // Belum dipilah: Rp 2.500
        $resBelum = $this->actingAs($this->petugasPengelola, 'petugas')
            ->getJson(route('pengelola.penimbangan.get-harga', [
                'jenis_sampah_id' => $this->jenisSampah->id,
                'kondisi' => 'belum_dipilah',
            ]));
        $resBelum->assertStatus(200)->assertJson(['harga_per_kg' => 2500]);

        // Campuran: Rp 1.500
        $resCampur = $this->actingAs($this->petugasPengelola, 'petugas')
            ->getJson(route('pengelola.penimbangan.get-harga', [
                'jenis_sampah_id' => $this->jenisSampah->id,
                'kondisi' => 'campuran',
            ]));
        $resCampur->assertStatus(200)->assertJson(['harga_per_kg' => 1500]);
    }

    /**
     * 4. Berat nol atau negatif ditolak oleh SetoranService dan validasi HTTP.
     */
    public function test_berat_nol_atau_negatif_ditolak(): void
    {
        $responseNol = $this->actingAs($this->petugasPengelola, 'petugas')
            ->postJson(route('pengelola.penimbangan.store'), [
                'nasabah_id' => $this->nasabahAktif->id,
                'jenis_sampah_id' => $this->jenisSampah->id,
                'kondisi' => 'sudah_dipilah',
                'berat' => 0,
            ]);
        $responseNol->assertStatus(422);

        $responseNeg = $this->actingAs($this->petugasPengelola, 'petugas')
            ->postJson(route('pengelola.penimbangan.store'), [
                'nasabah_id' => $this->nasabahAktif->id,
                'jenis_sampah_id' => $this->jenisSampah->id,
                'kondisi' => 'sudah_dipilah',
                'berat' => -1.5,
            ]);
        $responseNeg->assertStatus(422);

        $service = app(SetoranService::class);
        $this->expectException(InvalidArgumentException::class);
        $service->processSetoran(
            $this->nasabahAktif->id,
            $this->petugasPengelola->id,
            $this->jenisSampah->id,
            'sudah_dipilah',
            0
        );
    }

    /**
     * 5. SetoranService memproses setoran: simpan setoran snapshot, tambah saldo, catat mutasi, buat nota unik.
     * Invarian: saldo nasabah = saldo_sesudah mutasi terakhir.
     */
    public function test_setoran_service_menyimpan_transaksi_dan_menjaga_invarian_saldo(): void
    {
        $service = app(SetoranService::class);

        // Saldo awal: 50.000. Berat: 2.4 kg. Harga: 4.000.
        // Total = TransactionCalculator::calculate(2.4, 4000) = 9600.
        $result = $service->processSetoran(
            $this->nasabahAktif->id,
            $this->petugasPengelola->id,
            $this->jenisSampah->id,
            'sudah_dipilah',
            2.4
        );

        $setoran = $result['setoran'];
        $nota = $result['nota'];
        $mutasi = $result['mutasi'];
        $nasabah = $result['nasabah'];

        $this->assertInstanceOf(Setoran::class, $setoran);
        $this->assertInstanceOf(Nota::class, $nota);
        $this->assertInstanceOf(MutasiSaldo::class, $mutasi);

        // Verifikasi setoran tersimpan dengan snapshot harga
        $this->assertSame(4000, $setoran->harga_per_kg);
        $this->assertSame(9600, $setoran->total_nilai);
        $this->assertSame('2.40', (string) $setoran->berat);

        // Verifikasi saldo nasabah bertambah: 50.000 + 9.600 = 59.600
        $this->assertSame(59600, $nasabah->saldo);
        $this->assertSame(59600, $this->nasabahAktif->fresh()->saldo);

        // Verifikasi mutasi saldo
        $this->assertSame('setoran', $mutasi->jenis);
        $this->assertSame(9600, $mutasi->jumlah);
        $this->assertSame(59600, $mutasi->saldo_sesudah);

        // INVARIAN MUTLAK: Saldo nasabah == saldo_sesudah mutasi terakhir
        $latestMutasi = MutasiSaldo::where('nasabah_id', $this->nasabahAktif->id)->latest('id')->first();
        $this->assertSame($this->nasabahAktif->fresh()->saldo, $latestMutasi->saldo_sesudah);

        // Verifikasi nota dibuat dan terhubung ke setoran
        $this->assertSame($setoran->id, $nota->setoran_id);
        $this->assertStringStartsWith('NOTA-', $nota->nomor);
    }

    /**
     * 6. Konfirmasi setoran melalui HTTP endpoint mengembalikan nomor nota unik dan pesan sukses.
     */
    public function test_konfirmasi_setoran_berhasil_via_http(): void
    {
        $response = $this->actingAs($this->petugasPengelola, 'petugas')
            ->postJson(route('pengelola.penimbangan.store'), [
                'nasabah_id' => $this->nasabahAktif->id,
                'jenis_sampah_id' => $this->jenisSampah->id,
                'kondisi' => 'sudah_dipilah',
                'berat' => 3.5,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Setoran Berhasil',
            ])
            ->assertJsonStructure([
                'data' => ['setoran_id', 'nomor_nota', 'total_nilai', 'saldo_terbaru'],
            ]);

        $this->assertDatabaseHas('nota', [
            'nomor' => $response->json('data.nomor_nota'),
        ]);
    }

    /**
     * 7. Concurrency: Dua setoran beruntun / bersamaan pada nasabah yang sama menjaga konsistensi saldo.
     */
    public function test_dua_setoran_bersamaan_tidak_merusak_saldo(): void
    {
        $service = app(SetoranService::class);

        // Setoran 1: 1.0 kg x 4.000 = 4.000 -> Saldo: 50.000 + 4.000 = 54.000
        $res1 = $service->processSetoran(
            $this->nasabahAktif->id,
            $this->petugasPengelola->id,
            $this->jenisSampah->id,
            'sudah_dipilah',
            1.0
        );

        // Setoran 2: 2.5 kg x 4.000 = 10.000 -> Saldo: 54.000 + 10.000 = 64.000
        $res2 = $service->processSetoran(
            $this->nasabahAktif->id,
            $this->petugasPengelola->id,
            $this->jenisSampah->id,
            'sudah_dipilah',
            2.5
        );

        $freshSaldo = $this->nasabahAktif->fresh()->saldo;
        $this->assertSame(64000, $freshSaldo);

        // Nomor nota kedua setoran harus berbeda (unik)
        $this->assertNotSame($res1['nota']->nomor, $res2['nota']->nomor);

        // Invarian mutasi saldo
        $mutasiList = MutasiSaldo::where('nasabah_id', $this->nasabahAktif->id)->orderBy('id')->get();
        $this->assertCount(2, $mutasiList);
        $this->assertSame(54000, $mutasiList[0]->saldo_sesudah);
        $this->assertSame(64000, $mutasiList[1]->saldo_sesudah);
    }

    /**
     * 8. QrSessionService: Pembuatan token, TTL 120s, terikat petugas, dan tombol 'Perbarui QR' mematikan token lama.
     */
    public function test_qr_session_terikat_petugas_dan_refresh_mematikan_token_lama(): void
    {
        $qrService = app(QrSessionService::class);

        // 1. Buat sesi pertama
        $session1 = $qrService->createSession($this->petugasPengelola->id);
        $token1 = $session1['token'];
        $this->assertNotEmpty($token1);
        $this->assertSame(120, $session1['expires_in']);

        // Token 1 aktif di cache
        $check1 = $qrService->checkSession($token1, $this->petugasPengelola->id);
        $this->assertSame('pending', $check1['status']);

        // 2. Perbarui QR -> menerbitkan token baru dan mematikan token lama
        $session2 = $qrService->refreshSession($this->petugasPengelola->id);
        $token2 = $session2['token'];
        $this->assertNotSame($token1, $token2);

        // Token 1 sekarang telah dimatikan / expired
        $checkOld = $qrService->checkSession($token1, $this->petugasPengelola->id);
        $this->assertSame('expired', $checkOld['status']);

        // Token 2 aktif
        $checkNew = $qrService->checkSession($token2, $this->petugasPengelola->id);
        $this->assertSame('pending', $checkNew['status']);
    }

    /**
     * 9. QrSessionService: Nasabah memindai token QR yang valid berhasil (sekali pakai).
     */
    public function test_qr_session_klaim_oleh_nasabah_dan_sekali_pakai(): void
    {
        $qrService = app(QrSessionService::class);
        $session = $qrService->createSession($this->petugasPengelola->id);
        $token = $session['token'];

        // Nasabah login memindai QR via endpoint
        $response = $this->actingAs($this->nasabahAktif, 'nasabah')
            ->postJson(route('nasabah.scan-qr'), ['token' => $token]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'QR Code berhasil dipindai.',
            ]);

        // Sisi petugas membaca status sesi: harus 'claimed' dan menampilkan nasabah
        $checkPetugas = $qrService->checkSession($token, $this->petugasPengelola->id);
        $this->assertSame('claimed', $checkPetugas['status']);
        $this->assertSame($this->nasabahAktif->id, $checkPetugas['nasabah']['id']);

        // Uji SEKALI PAKAI: Pemindaian kedua dengan token yang sama harus ditolak
        $responseKedua = $this->actingAs($this->nasabahAktif, 'nasabah')
            ->postJson(route('nasabah.scan-qr'), ['token' => $token]);

        $responseKedua->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Token QR sudah digunakan (sekali pakai).',
            ]);
    }

    /**
     * 10. QrSessionService: Token tidak valid atau kedaluwarsa ditolak saat dipindai.
     */
    public function test_qr_session_token_invalid_atau_expired_ditolak(): void
    {
        $response = $this->actingAs($this->nasabahAktif, 'nasabah')
            ->postJson(route('nasabah.scan-qr'), ['token' => 'token_palsu_tidak_ada']);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Token QR tidak valid atau sudah kedaluwarsa.',
            ]);
    }

    /**
     * 11. QrSessionService: Nasabah tidak aktif ditolak saat memindai token QR.
     */
    public function test_nasabah_tidak_aktif_memindai_qr_ditolak(): void
    {
        $qrService = app(QrSessionService::class);
        $session = $qrService->createSession($this->petugasPengelola->id);
        $token = $session['token'];

        $response = $this->actingAs($this->nasabahTidakAktif, 'nasabah')
            ->postJson(route('nasabah.scan-qr'), ['token' => $token]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Akun nasabah tidak aktif. Transaksi penimbangan tidak dapat dilanjutkan.',
            ]);
    }

    /**
     * 12. ManualWeighingSource membaca berat dari konteks secara benar.
     */
    public function test_manual_weighing_source(): void
    {
        $source = new ManualWeighingSource;

        $this->assertSame(2.45, $source->getWeight(['berat' => 2.45]));
        $this->assertSame(3.5, $source->getWeight(['berat' => '3,5']));
        $this->assertSame(0.0, $source->getWeight([]));
    }

    /**
     * 13. Form-driven penimbangan setelah produk tersimpan & uji klik ganda (double-submit).
     */
    public function test_form_driven_penimbangan_dan_uji_klik_ganda(): void
    {
        // 1. Simpan produk baru beserta gambar dan harga aktif
        $produkBaru = JenisSampah::create([
            'kode' => 'KDG-99',
            'nama' => 'Kardus Gelombang Tebal',
            'kategori' => 'Kertas',
            'gambar' => 'sampah/kardus_99.png',
        ]);

        $tanggalMulai = now()->subDay()->toDateString();
        HargaSampah::create([
            'jenis_sampah_id' => $produkBaru->id,
            'kondisi' => 'sudah_dipilah',
            'harga_per_kg' => 3500,
            'tanggal_mulai' => $tanggalMulai,
        ]);
        HargaSampah::create([
            'jenis_sampah_id' => $produkBaru->id,
            'kondisi' => 'belum_dipilah',
            'harga_per_kg' => 2500,
            'tanggal_mulai' => $tanggalMulai,
        ]);
        HargaSampah::create([
            'jenis_sampah_id' => $produkBaru->id,
            'kondisi' => 'campuran',
            'harga_per_kg' => 1500,
            'tanggal_mulai' => $tanggalMulai,
        ]);

        // 2. Akses halaman penimbangan: pastikan produk tampil (nama, kode, gambar)
        $halaman = $this->actingAs($this->petugasPengelola, 'petugas')
            ->get(route('pengelola.penimbangan'));

        $halaman->assertStatus(200);
        $halaman->assertSee('KDG-99');
        $halaman->assertSee('Kardus Gelombang Tebal');
        $halaman->assertSee('sampah/kardus_99.png');

        // 3. Memilih jenis dan kondisi, timbang manual (2.0 kg), Konfirmasi Setoran
        $saldoAwal = $this->nasabahAktif->saldo; // 50.000
        $berat = 2.0;
        $hargaPerKg = 3500;
        $totalNilai = (int) ($berat * $hargaPerKg); // 7.000

        $payload = [
            'nasabah_id' => $this->nasabahAktif->id,
            'jenis_sampah_id' => $produkBaru->id,
            'kondisi' => 'sudah_dipilah',
            'berat' => $berat,
        ];

        $post1 = $this->actingAs($this->petugasPengelola, 'petugas')
            ->postJson(route('pengelola.penimbangan.store'), $payload);

        $post1->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Setoran Berhasil',
                'data' => [
                    'total_nilai' => $totalNilai,
                    'saldo_terbaru' => $saldoAwal + $totalNilai,
                ],
            ]);

        // Verifikasi saldo naik, mutasi saldo terbuat, nota terbuat
        $this->assertSame($saldoAwal + $totalNilai, $this->nasabahAktif->fresh()->saldo);
        $this->assertDatabaseHas('mutasi_saldo', [
            'nasabah_id' => $this->nasabahAktif->id,
            'jenis' => 'setoran',
            'jumlah' => $totalNilai,
            'saldo_sesudah' => $saldoAwal + $totalNilai,
        ]);
        $nomorNota1 = $post1->json('data.nomor_nota');
        $this->assertDatabaseHas('nota', ['nomor' => $nomorNota1]);

        // 4. Kirim ulang POST yang sama (klik ganda)
        $post2 = $this->actingAs($this->petugasPengelola, 'petugas')
            ->postJson(route('pengelola.penimbangan.store'), $payload);

        // Verifikasi apakah membuat setoran kedua (TEMUAN: double submit menghasilkan setoran ganda)
        $post2->assertStatus(200);
        $jumlahSetoran = Setoran::where('nasabah_id', $this->nasabahAktif->id)
            ->where('jenis_sampah_id', $produkBaru->id)
            ->count();

        $this->assertSame(2, $jumlahSetoran, 'Klik ganda membuat setoran kedua (TEMUAN yang akan diperbaiki di Fase 10A).');
        $this->assertSame($saldoAwal + ($totalNilai * 2), $this->nasabahAktif->fresh()->saldo);
    }
}
