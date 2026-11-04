<?php

namespace Tests\Feature;

use App\Models\Nasabah;
use App\Models\Petugas;
use App\Services\OtpSender;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\Support\FakeOtpSender;
use Tests\TestCase;

class LoginAuthTest extends TestCase
{
    use RefreshDatabase;

    protected FakeOtpSender $fakeSender;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        RateLimiter::clear('login:petugas:6281234567890|127.0.0.1');
        RateLimiter::clear('login:nasabah:6289876543210|127.0.0.1');
        RateLimiter::clear('password_reset:phone:petugas:6281234567890');
        RateLimiter::clear('password_reset:ip:127.0.0.1');

        $this->fakeSender = new FakeOtpSender;
        $this->app->instance(OtpSender::class, $this->fakeSender);
        config(['metanoia.otp.driver' => 'fake']);
    }

    private function createPetugas(string $nama, string $telepon, string $jabatan, string $password = 'secret123'): Petugas
    {
        $petugas = new Petugas([
            'nama' => $nama,
            'no_telp' => OtpService::normalizePhoneNumber($telepon),
            'password' => $password,
        ]);
        $petugas->forceFill(['jabatan' => $jabatan])->save();

        return $petugas;
    }

    private function createNasabah(string $nama, string $telepon, string $password = 'secret123', ?string $nik = null): Nasabah
    {
        $nasabah = new Nasabah([
            'nama' => $nama,
            'nik' => $nik ?? substr(str_shuffle('1234567890123456'), 0, 16),
            'no_telp' => OtpService::normalizePhoneNumber($telepon),
            'password' => $password,
            'alamat' => 'Jl. Testing No. 1',
        ]);
        $nasabah->forceFill([
            'saldo' => 100000,
            'status' => 'aktif',
        ])->save();

        return $nasabah;
    }

    public function test_login_petugas_pengelola_berhasil_dan_redirect_ke_dashboard(): void
    {
        $this->createPetugas('Pengelola A', '081234567890', 'pengelola', 'password123');

        $response = $this->post(route('login.attempt', 'petugas'), [
            'telepon' => '081234567890',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('pengelola.dashboard'));
        $this->assertTrue(Auth::guard('petugas')->check());
        $this->assertSame('pengelola', Auth::guard('petugas')->user()->jabatan);
    }

    public function test_login_petugas_bendahara_berhasil_dan_redirect_ke_bendahara_transaksi(): void
    {
        $bendahara = $this->createPetugas('Bendahara B', '081234567890', 'bendahara', 'password123');

        $response = $this->post(route('login.attempt', 'petugas'), [
            'telepon' => '081234567890',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('bendahara.transaksi'));
        $this->assertTrue(Auth::guard('petugas')->check());
        $this->assertSame('bendahara', Auth::guard('petugas')->user()->jabatan);

        // Route bendahara.beranda tetap ada sebagai redirect
        $this->actingAs($bendahara, 'petugas')->get(route('bendahara.beranda'))->assertRedirect(route('bendahara.transaksi'));
    }

    public function test_login_petugas_ketua_berhasil_dan_redirect_ke_ketua_pengajuan_penarikan(): void
    {
        $ketua = $this->createPetugas('Ketua C', '081234567890', 'ketua', 'password123');

        $response = $this->post(route('login.attempt', 'petugas'), [
            'telepon' => '081234567890',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('ketua.pengajuan-penarikan'));
        $this->assertTrue(Auth::guard('petugas')->check());
        $this->assertSame('ketua', Auth::guard('petugas')->user()->jabatan);

        // Route ketua.beranda tetap ada sebagai redirect
        $this->actingAs($ketua, 'petugas')->get(route('ketua.beranda'))->assertRedirect(route('ketua.pengajuan-penarikan'));
    }

    public function test_login_nasabah_berhasil_dan_redirect_ke_nasabah_beranda(): void
    {
        $this->createNasabah('Nasabah D', '089876543210', 'password123');

        $response = $this->post(route('login.attempt', 'nasabah'), [
            'telepon' => '089876543210',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('nasabah.beranda'));
        $this->assertTrue(Auth::guard('nasabah')->check());
        $this->assertSame('Nasabah D', Auth::guard('nasabah')->user()->nama);
    }

    public function test_login_gagal_password_salah_menampilkan_pesan_generik(): void
    {
        $this->createPetugas('Pengelola A', '081234567890', 'pengelola', 'password123');

        $response = $this->from(route('login.show', 'petugas'))->post(route('login.attempt', 'petugas'), [
            'telepon' => '081234567890',
            'password' => 'passwordsalah',
        ]);

        $response->assertRedirect(route('login.show', 'petugas'));
        $response->assertSessionHasErrors([
            'telepon' => 'Nomor telepon atau password salah',
        ]);
        $this->assertFalse(Auth::guard('petugas')->check());
    }

    public function test_login_gagal_nomor_telepon_tidak_terdaftar_pesan_generik_identik(): void
    {
        $response = $this->from(route('login.show', 'petugas'))->post(route('login.attempt', 'petugas'), [
            'telepon' => '081299999999',
            'password' => 'sembarang',
        ]);

        $response->assertRedirect(route('login.show', 'petugas'));
        $response->assertSessionHasErrors([
            'telepon' => 'Nomor telepon atau password salah',
        ]);
        $this->assertFalse(Auth::guard('petugas')->check());
    }

    public function test_login_petugas_ditolak_jika_jabatan_tidak_terdaftar_di_config(): void
    {
        // Buat petugas dengan jabatan ketua di database
        $this->createPetugas('Petugas Pembatasan', '081234567890', 'ketua', 'password123');

        // Batasi config metanoia hanya mengizinkan pengelola & bendahara
        config(['metanoia.jabatan' => ['pengelola', 'bendahara']]);

        $response = $this->post(route('login.attempt', 'petugas'), [
            'telepon' => '081234567890',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'telepon' => 'Nomor telepon atau password salah',
        ]);
        $this->assertFalse(Auth::guard('petugas')->check());
    }

    public function test_nasabah_tidak_bisa_masuk_lewat_portal_petugas(): void
    {
        $this->createNasabah('Nasabah E', '089876543210', 'password123');

        $response = $this->post(route('login.attempt', 'petugas'), [
            'telepon' => '089876543210',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'telepon' => 'Nomor telepon atau password salah',
        ]);
        $this->assertFalse(Auth::guard('petugas')->check());
        $this->assertFalse(Auth::guard('nasabah')->check());
    }

    public function test_petugas_tidak_bisa_masuk_lewat_portal_nasabah(): void
    {
        $this->createPetugas('Pengelola F', '081234567890', 'pengelola', 'password123');

        $response = $this->post(route('login.attempt', 'nasabah'), [
            'telepon' => '081234567890',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'telepon' => 'Nomor telepon atau password salah',
        ]);
        $this->assertFalse(Auth::guard('petugas')->check());
        $this->assertFalse(Auth::guard('nasabah')->check());
    }

    public function test_throttle_login_5_kali_per_menit_per_telepon_dan_ip(): void
    {
        $this->createPetugas('Petugas Throttle', '081234567890', 'pengelola', 'password123');

        // 5 percobaan gagal pertama
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post(route('login.attempt', 'petugas'), [
                'telepon' => '081234567890',
                'password' => 'wrongpass',
            ]);
            $response->assertSessionHasErrors([
                'telepon' => 'Nomor telepon atau password salah',
            ]);
        }

        // Percobaan ke-6 ditolak oleh throttle
        $response6 = $this->post(route('login.attempt', 'petugas'), [
            'telepon' => '081234567890',
            'password' => 'password123',
        ]);

        $response6->assertSessionHasErrors('telepon');
        $errorMessage = session('errors')->first('telepon');
        $this->assertStringContainsString('Too many login attempts', $errorMessage);
    }

    public function test_akses_halaman_pengelola_tanpa_login_dialihkan_ke_login_petugas(): void
    {
        $response = $this->get(route('pengelola.dashboard'));
        $response->assertRedirect(route('login.show', 'petugas'));

        $responsePenimbangan = $this->get(route('pengelola.penimbangan'));
        $responsePenimbangan->assertRedirect(route('login.show', 'petugas'));
    }

    public function test_akses_halaman_nasabah_tanpa_login_dialihkan_ke_login_nasabah(): void
    {
        $response = $this->get(route('nasabah.beranda'));
        $response->assertRedirect(route('login.show', 'nasabah'));
    }

    public function test_jabatan_salah_mengakses_halaman_menghasilkan_403(): void
    {
        $bendahara = $this->createPetugas('Bendahara X', '081234567890', 'bendahara');
        $pengelola = $this->createPetugas('Pengelola Y', '081211112222', 'pengelola');

        // Bendahara mengakses penimbangan (khusus pengelola) -> 403
        $this->actingAs($bendahara, 'petugas');
        $responsePenimbangan = $this->get(route('pengelola.penimbangan'));
        $responsePenimbangan->assertStatus(403);

        // Bendahara mengakses data nasabah (khusus pengelola) -> 403
        $responseNasabah = $this->get(route('pengelola.nasabah'));
        $responseNasabah->assertStatus(403);

        // Pengelola mengakses transaksi (khusus bendahara) -> 403
        $this->actingAs($pengelola, 'petugas');
        $responseTransaksi = $this->get(route('pengelola.transaksi'));
        $responseTransaksi->assertStatus(403);
    }

    public function test_hanya_jabatan_pengelola_yang_bisa_mengakses_dashboard_pengelola(): void
    {
        $pengelola = $this->createPetugas('Pengelola 1', '081211111111', 'pengelola');
        $bendahara = $this->createPetugas('Bendahara 2', '081222222222', 'bendahara');
        $ketua = $this->createPetugas('Ketua 3', '081233333333', 'ketua');

        $this->actingAs($pengelola, 'petugas')->get(route('pengelola.dashboard'))->assertStatus(200);
        $this->actingAs($bendahara, 'petugas')->get(route('pengelola.dashboard'))->assertStatus(403);
        $this->actingAs($ketua, 'petugas')->get(route('pengelola.dashboard'))->assertStatus(403);
    }

    public function test_logout_berhasil_menghapus_sesi(): void
    {
        $pengelola = $this->createPetugas('Pengelola Logout', '081234567890', 'pengelola');
        $this->actingAs($pengelola, 'petugas');

        $this->assertTrue(Auth::guard('petugas')->check());

        $response = $this->post(route('logout'));
        $response->assertRedirect('/');

        $this->assertFalse(Auth::guard('petugas')->check());
    }

    public function test_setelah_reset_password_bisa_login_dengan_password_baru(): void
    {
        // 1. Setup Petugas dengan password lama
        $this->createPetugas('Petugas Reset', '081234567890', 'pengelola', 'passwordLama1');

        // 2. Buka form lupa password dan kirim OTP
        $this->get(route('password.forgot', ['role' => 'petugas']));
        $this->post(route('password.send'), ['telepon' => '081234567890']);

        // Dapatkan OTP dari FakeOtpSender
        $otp = $this->fakeSender->lastCode();
        $this->assertNotNull($otp);

        // 3. Verifikasi OTP
        $this->post(route('verification.check'), ['otp' => str_split($otp)]);

        // 4. Perbarui password ke password baru
        $this->post(route('password.update'), [
            'password' => 'passwordBaruSuper123',
            'password_confirmation' => 'passwordBaruSuper123',
        ]);

        // 5. Coba login dengan password lama (harus gagal)
        $respLama = $this->post(route('login.attempt', 'petugas'), [
            'telepon' => '081234567890',
            'password' => 'passwordLama1',
        ]);
        $respLama->assertSessionHasErrors('telepon');
        $this->assertFalse(Auth::guard('petugas')->check());

        // 6. Coba login dengan password baru (harus berhasil)
        $respBaru = $this->post(route('login.attempt', 'petugas'), [
            'telepon' => '081234567890',
            'password' => 'passwordBaruSuper123',
        ]);
        $respBaru->assertRedirect(route('pengelola.dashboard'));
        $this->assertTrue(Auth::guard('petugas')->check());
    }

    public function test_artisan_petugas_buat_secara_interaktif(): void
    {
        $this->artisan('petugas:buat')
            ->expectsQuestion('Nama lengkap', 'Siti Rahmawati')
            ->expectsQuestion('Nomor telepon', '081298765432')
            ->expectsChoice('Pilih jabatan petugas', 'bendahara', ['pengelola', 'bendahara', 'ketua'])
            ->expectsQuestion('Password', 'sitiSecret123')
            ->assertSuccessful();

        $petugas = Petugas::where('no_telp', '6281298765432')->first();

        $this->assertNotNull($petugas);
        $this->assertSame('Siti Rahmawati', $petugas->nama);
        $this->assertSame('bendahara', $petugas->jabatan);
        $this->assertTrue(Hash::check('sitiSecret123', $petugas->password));
    }
}
