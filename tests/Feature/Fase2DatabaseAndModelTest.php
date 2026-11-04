<?php

namespace Tests\Feature;

use App\Models\HargaSampah;
use App\Models\JenisSampah;
use App\Models\MutasiSaldo;
use App\Models\Nasabah;
use App\Models\Nota;
use App\Models\Penarikan;
use App\Models\Penjualan;
use App\Models\Petugas;
use App\Models\Setoran;
use App\Models\TransaksiKeuangan;
use App\Services\AccountDirectory;
use App\Services\EloquentAccountDirectory;
use App\Services\OtpSender;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Support\FakeOtpSender;
use Tests\TestCase;

class Fase2DatabaseAndModelTest extends TestCase
{
    use RefreshDatabase;

    protected FakeOtpSender $fakeSender;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fakeSender = new FakeOtpSender;
        $this->app->instance(OtpSender::class, $this->fakeSender);
        config(['metanoia.otp.driver' => 'fake']);
    }

    /**
     * 1. Fillable tidak memuat kolom terlarang (jabatan pada Petugas, status & saldo pada Nasabah).
     */
    public function test_fillable_tidak_memuat_kolom_terlarang(): void
    {
        $petugas = new Petugas;
        $this->assertNotContains('jabatan', $petugas->getFillable());

        $nasabah = new Nasabah;
        $this->assertNotContains('status', $nasabah->getFillable());
        $this->assertNotContains('saldo', $nasabah->getFillable());
    }

    /**
     * 2. ForceFill jabatan pada Petugas berhasil, sedangkan mass assignment diabaikan.
     */
    public function test_force_fill_jabatan_pada_petugas(): void
    {
        // Mass assignment biasa: 'jabatan' diabaikan
        $petugas = new Petugas([
            'nama' => 'Budi Petugas',
            'no_telp' => '6281234567890',
            'password' => 'secret123',
            'jabatan' => 'pengelola',
        ]);
        $this->assertNull($petugas->jabatan);

        // Menggunakan forceFill: 'jabatan' berhasil diisi dan disimpan
        $petugas->forceFill(['jabatan' => 'pengelola'])->save();

        $this->assertSame('pengelola', $petugas->fresh()->jabatan);
    }

    /**
     * 3. Nomor telepon pada Petugas dan Nasabah harus unik.
     */
    public function test_nomor_telepon_unik_pada_petugas_dan_nasabah(): void
    {
        $p1 = new Petugas([
            'nama' => 'Petugas 1',
            'no_telp' => '6281200000001',
            'password' => 'secret123',
        ]);
        $p1->forceFill(['jabatan' => 'pengelola'])->save();

        $this->expectException(QueryException::class);
        $p2 = new Petugas([
            'nama' => 'Petugas 2',
            'no_telp' => '6281200000001', // Nomor duplikat
            'password' => 'secret456',
        ]);
        $p2->forceFill(['jabatan' => 'bendahara'])->save();
    }

    public function test_nomor_telepon_unik_pada_nasabah(): void
    {
        Nasabah::create([
            'nama' => 'Nasabah 1',
            'nik' => '3201010101010001',
            'alamat' => 'Jl. Merdeka No. 1',
            'no_telp' => '6281300000001',
            'password' => 'secret123',
        ]);

        $this->expectException(QueryException::class);
        Nasabah::create([
            'nama' => 'Nasabah 2',
            'nik' => '3201010101010002',
            'alamat' => 'Jl. Merdeka No. 2',
            'no_telp' => '6281300000001', // Nomor duplikat
            'password' => 'secret456',
        ]);
    }

    /**
     * 4. EloquentAccountDirectory normalisasi nomor dan verifikasi keberadaan akun.
     */
    public function test_eloquent_account_directory_exists_dan_update_password(): void
    {
        $petugas = new Petugas([
            'nama' => 'Petugas Test',
            'no_telp' => '6281211112222',
            'password' => 'oldpassword',
        ]);
        $petugas->forceFill(['jabatan' => 'pengelola'])->save();

        $nasabah = Nasabah::create([
            'nama' => 'Nasabah Test',
            'nik' => '3201010101010003',
            'alamat' => 'Jl. Pahlawan',
            'no_telp' => '6281233334444',
            'password' => 'oldpassword',
        ]);

        $directory = app(AccountDirectory::class);
        $this->assertInstanceOf(EloquentAccountDirectory::class, $directory);

        // exists() dengan format lokal (08...) dinormalisasi ke 62...
        $this->assertTrue($directory->exists('petugas', '081211112222'));
        $this->assertTrue($directory->exists('nasabah', '+62 812-3333-4444'));
        $this->assertFalse($directory->exists('petugas', '081299999999'));

        // updatePassword() memperbarui password hash
        $newHash = Hash::make('newSecret123');
        $updated = $directory->updatePassword('petugas', '081211112222', $newHash);
        $this->assertTrue($updated);
        $this->assertTrue(Hash::check('newSecret123', $petugas->fresh()->password));

        $updatedNasabah = $directory->updatePassword('nasabah', '081233334444', $newHash);
        $this->assertTrue($updatedNasabah);
        $this->assertTrue(Hash::check('newSecret123', $nasabah->fresh()->password));
    }

    /**
     * 5. Alur reset password dari awal hingga password baru benar-benar tersimpan di DB untuk Petugas.
     */
    public function test_reset_password_tersimpan_di_database_petugas(): void
    {
        $petugas = new Petugas([
            'nama' => 'Petugas Reset',
            'no_telp' => '6281255556666',
            'password' => 'passwordLama123',
        ]);
        $petugas->forceFill(['jabatan' => 'pengelola'])->save();

        // 1. Forgot form
        $this->get(route('password.forgot', ['role' => 'petugas']))->assertStatus(200);

        // 2. Kirim nomor telepon lokal (08...)
        $this->post(route('password.send'), ['telepon' => '081255556666'])
            ->assertRedirect(route('verification.page'));

        $code = $this->fakeSender->lastCode();
        $this->assertNotNull($code);

        // 3. Verifikasi OTP
        $this->post(route('verification.check'), ['otp' => str_split($code)])
            ->assertRedirect(route('password.new'));

        // 4. Update password
        $this->post(route('password.update'), [
            'password' => 'passwordBaruSuperAman123',
            'password_confirmation' => 'passwordBaruSuperAman123',
        ])->assertRedirect(route('login.show', 'petugas'));

        // Pastikan password di database benar-benar berubah
        $petugasFresh = $petugas->fresh();
        $this->assertTrue(Hash::check('passwordBaruSuperAman123', $petugasFresh->password));
        $this->assertFalse(Hash::check('passwordLama123', $petugasFresh->password));
    }

    /**
     * 6. Alur reset password dari awal hingga password baru benar-benar tersimpan di DB untuk Nasabah.
     */
    public function test_reset_password_tersimpan_di_database_nasabah(): void
    {
        $nasabah = Nasabah::create([
            'nama' => 'Nasabah Reset',
            'nik' => '3201010101010009',
            'alamat' => 'Jl. Kenanga No. 5',
            'no_telp' => '6281277778888',
            'password' => 'passwordNasabahLama',
        ]);

        // 1. Forgot form
        $this->get(route('password.forgot', ['role' => 'nasabah']))->assertStatus(200);

        // 2. Kirim nomor telepon lokal (08...)
        $this->post(route('password.send'), ['telepon' => '081277778888'])
            ->assertRedirect(route('verification.page'));

        $code = $this->fakeSender->lastCode();
        $this->assertNotNull($code);

        // 3. Verifikasi OTP
        $this->post(route('verification.check'), ['otp' => str_split($code)])
            ->assertRedirect(route('password.new'));

        // 4. Update password
        $this->post(route('password.update'), [
            'password' => 'passwordNasabahBaru123',
            'password_confirmation' => 'passwordNasabahBaru123',
        ])->assertRedirect(route('login.show', 'nasabah'));

        // Pastikan password di database benar-benar berubah
        $nasabahFresh = $nasabah->fresh();
        $this->assertTrue(Hash::check('passwordNasabahBaru123', $nasabahFresh->password));
        $this->assertFalse(Hash::check('passwordNasabahLama', $nasabahFresh->password));
    }

    /**
     * 7. Model dan relasi skema Metanoia berjalan sesuai spesifikasi.
     */
    public function test_skema_dan_relasi_model(): void
    {
        $petugas = new Petugas([
            'nama' => 'Petugas Relasi',
            'no_telp' => '6281299990001',
            'password' => 'pass',
        ]);
        $petugas->forceFill(['jabatan' => 'pengelola'])->save();

        $nasabah = Nasabah::create([
            'nama' => 'Nasabah Relasi',
            'nik' => '3201010101010010',
            'alamat' => 'Jl. Mawar',
            'no_telp' => '6281299990002',
            'password' => 'pass',
        ]);

        $jenisSampah = JenisSampah::create([
            'kode' => 'SMP-001',
            'nama' => 'Kardus',
            'deskripsi' => 'Kardus coklat bersih',
            'kategori' => 'Kertas',
        ]);

        $harga = HargaSampah::create([
            'jenis_sampah_id' => $jenisSampah->id,
            'kondisi' => 'sudah_dipilah',
            'harga_per_kg' => 2500,
            'tanggal_mulai' => '2026-01-01',
            'tanggal_berakhir' => null,
        ]);
        $this->assertSame($jenisSampah->id, $harga->jenisSampah->id);

        $setoran = Setoran::create([
            'nasabah_id' => $nasabah->id,
            'petugas_id' => $petugas->id,
            'jenis_sampah_id' => $jenisSampah->id,
            'kondisi' => 'sudah_dipilah',
            'berat' => 10.50,
            'harga_per_kg' => 2500,
            'total_nilai' => 26250,
            'tanggal' => now(),
        ]);
        $this->assertSame($nasabah->id, $setoran->nasabah->id);
        $this->assertSame($petugas->id, $setoran->petugas->id);
        $this->assertSame($jenisSampah->id, $setoran->jenisSampah->id);

        $nota = Nota::create([
            'setoran_id' => $setoran->id,
            'nomor' => 'NOTA-0001',
            'tanggal' => now(),
        ]);
        $this->assertSame($setoran->id, $nota->setoran->id);
        $this->assertSame($nota->id, $setoran->fresh()->nota->id);

        $penarikan = Penarikan::create([
            'nasabah_id' => $nasabah->id,
            'nominal' => 20000,
            'status' => 'menunggu',
            'tanggal_pengajuan' => now(),
        ]);
        $this->assertSame($nasabah->id, $penarikan->nasabah->id);

        $mutasi = MutasiSaldo::create([
            'nasabah_id' => $nasabah->id,
            'jenis' => 'setoran',
            'jumlah' => 26250,
            'saldo_sesudah' => 26250,
            'referensi_tipe' => Setoran::class,
            'referensi_id' => $setoran->id,
        ]);
        $this->assertSame($nasabah->id, $mutasi->nasabah->id);
        $this->assertInstanceOf(Setoran::class, $mutasi->referensi);

        $penjualan = Penjualan::create([
            'tanggal_pengangkutan' => '2026-10-26',
            'total_berat' => 150.75,
            'total_nilai' => 450000,
        ]);
        // Otomatis terisi nomor T00001 dari id
        $this->assertSame('T0000'.$penjualan->id, $penjualan->nomor);

        $transaksi = TransaksiKeuangan::create([
            'petugas_id' => $petugas->id,
            'tanggal' => '2026-10-26',
            'jenis' => 'pemasukan',
            'nominal' => 450000,
            'keterangan' => 'Penjualan ke BSI',
        ]);
        $this->assertSame($petugas->id, $transaksi->petugas->id);
    }
}
