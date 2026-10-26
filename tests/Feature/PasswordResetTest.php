<?php

namespace Tests\Feature;

use App\Services\AccountDirectory;
use App\Services\OtpSender;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Tests\Support\FakeOtpSender;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    protected FakeOtpSender $fakeSender;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        RateLimiter::clear('password_reset:phone:pengelola:6281234567890');
        RateLimiter::clear('password_reset:phone:pengelola:6281211111111');
        RateLimiter::clear('password_reset:phone:pengelola:6281222222222');
        RateLimiter::clear('password_reset:phone:pengelola:6281211112222');
        RateLimiter::clear('password_reset:ip:127.0.0.1');

        $this->fakeSender = new FakeOtpSender();
        $this->app->instance(OtpSender::class, $this->fakeSender);
        config(['metanoia.otp.driver' => 'fake']);
    }

    /**
     * 1. Alur sukses sampai langkah 3 (ganti password berhasil jika updatePassword true).
     */
    public function test_alur_sukses_sampai_langkah_3(): void
    {
        // Mock AccountDirectory agar updatePassword mengembalikan true
        $mockDirectory = new class implements AccountDirectory {
            public function exists(string $role, string $phone): bool
            {
                return true;
            }

            public function updatePassword(string $role, string $phone, string $hash): bool
            {
                return true;
            }
        };
        $this->app->instance(AccountDirectory::class, $mockDirectory);

        // Langkah 1: Buka halaman lupa password dengan role pengelola
        $response1 = $this->get(route('password.forgot', ['role' => 'pengelola']));
        $response1->assertStatus(200);
        $response1->assertSee(route('login.show', 'pengelola'));
        $this->assertSame('pengelola', session('reset.role'));

        // Langkah 2: Kirim nomor telepon untuk permintaan OTP
        $response2 = $this->post(route('password.send'), [
            'telepon' => '081234567890',
        ]);
        $response2->assertRedirect(route('verification.page'));
        $response2->assertSessionHas('status', 'Jika nomor terdaftar, kode verifikasi telah dikirim.');
        $this->assertSame('6281234567890', session('reset.phone'));
        $this->assertNotNull(session('reset.shown'));

        // Pastikan kode terkirim via FakeOtpSender
        $code = $this->fakeSender->lastCode();
        $this->assertNotNull($code);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $code);

        // Buka halaman kode verifikasi
        $responsePage = $this->get(route('verification.page'));
        $responsePage->assertStatus(200);
        $responsePage->assertSee(session('reset.shown'));

        // Langkah 3: Verifikasi kode OTP
        $response3 = $this->post(route('verification.check'), [
            'otp' => str_split($code),
        ]);
        $response3->assertRedirect(route('password.new'));
        $this->assertNotNull(session('reset.verified_until'));
        $this->assertSame('pengelola', session('reset.verified_role'));
        $this->assertSame('6281234567890', session('reset.verified_phone'));

        // Buka formulir kata sandi baru
        $responseForm = $this->get(route('password.new'));
        $responseForm->assertStatus(200);

        // Langkah 4: Simpan kata sandi baru
        $response4 = $this->post(route('password.update'), [
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response4->assertRedirect(route('login.show', 'pengelola'));
        $response4->assertSessionHas('success');

        // Pastikan semua sesi reset.* telah dibersihkan dan tanpa login otomatis
        $this->assertNull(session('reset.role'));
        $this->assertNull(session('reset.phone'));
        $this->assertNull(session('reset.shown'));
        $this->assertNull(session('reset.verified_until'));
        $this->assertNull(session('reset.verified_role'));
        $this->assertNull(session('reset.verified_phone'));
        $this->assertFalse(auth()->check());
    }

    /**
     * 2 & 10. Respons identik untuk nomor terdaftar dan tidak terdaftar:
     * Bandingkan seluruh kunci session reset.* dan seluruh pesan flash.
     */
    public function test_respons_identik_untuk_nomor_terdaftar_dan_tidak_terdaftar(): void
    {
        $mockDirectory = new class implements AccountDirectory {
            public function exists(string $role, string $phone): bool
            {
                return $phone === '6281211112222'; // Hanya nomor ini yang terdaftar
            }

            public function updatePassword(string $role, string $phone, string $hash): bool
            {
                return true;
            }
        };
        $this->app->instance(AccountDirectory::class, $mockDirectory);

        // 1. Pengujian dengan nomor terdaftar
        $this->withSession(['reset.role' => 'pengelola']);
        $respRegistered = $this->post(route('password.send'), ['telepon' => '081211112222']);
        $respRegistered->assertRedirect(route('verification.page'));
        $registeredResetSession = [
            'reset.role'  => session('reset.role'),
            'reset.phone' => session('reset.phone'),
            'reset.shown' => session('reset.shown'),
        ];
        $registeredFlash = session('status');
        $this->assertCount(1, $this->fakeSender->messages);

        // 2. Pengujian dengan nomor tidak terdaftar
        $this->fakeSender->messages = [];
        Cache::flush();
        RateLimiter::clear('password_reset:ip:127.0.0.1');

        $this->withSession(['reset.role' => 'pengelola']);
        $respUnregistered = $this->post(route('password.send'), ['telepon' => '081299998888']);
        $respUnregistered->assertRedirect(route('verification.page'));
        $unregisteredResetSession = [
            'reset.role'  => session('reset.role'),
            'reset.phone' => session('reset.phone'),
            'reset.shown' => session('reset.shown'),
        ];
        $unregisteredFlash = session('status');

        // Nomor tidak terdaftar tidak menerima OTP
        $this->assertCount(0, $this->fakeSender->messages);

        // Status dan URL redirect identik
        $this->assertSame($respRegistered->getStatusCode(), $respUnregistered->getStatusCode());
        $this->assertSame($respRegistered->headers->get('Location'), $respUnregistered->headers->get('Location'));

        // Seluruh kunci session reset.* identik
        $this->assertSame(array_keys($registeredResetSession), array_keys($unregisteredResetSession));
        $this->assertSame($registeredResetSession['reset.role'], $unregisteredResetSession['reset.role']);
        $this->assertSame('6281299998888', $unregisteredResetSession['reset.phone']);

        // Seluruh pesan flash identik
        $this->assertSame($registeredFlash, $unregisteredFlash);
    }

    /**
     * 3. Akses langsung ke verification.page dan password.new tanpa langkah sebelumnya ditolak.
     */
    public function test_akses_langsung_tanpa_langkah_sebelumnya_ditolak(): void
    {
        // Tanpa session 'reset.phone', verification.page ditolak
        $resp1 = $this->get(route('verification.page'));
        $resp1->assertRedirect(route('password.forgot'));

        // Tanpa session 'reset.verified_until', password.new ditolak
        $resp2 = $this->get(route('password.new'));
        $resp2->assertRedirect(route('password.forgot'));

        // Submit password.update tanpa verified_until ditolak
        $resp3 = $this->post(route('password.update'), [
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $resp3->assertRedirect(route('password.forgot'));
    }

    /**
     * 4. Flag kedaluwarsa setelah 10 menit (travel).
     */
    public function test_flag_kedaluwarsa_setelah_10_menit(): void
    {
        $this->withSession([
            'reset.role'           => 'pengelola',
            'reset.phone'          => '6281234567890',
            'reset.verified_until' => now()->addMinutes(10)->timestamp,
            'reset.verified_role'  => 'pengelola',
            'reset.verified_phone' => '6281234567890',
        ]);

        // Verifikasi dalam rentang 10 menit dapat dibuka
        $this->get(route('password.new'))->assertStatus(200);

        // Maju 11 menit (lewat batas 10 menit)
        $this->travel(11)->minutes();

        // Akses form ditolak
        $this->get(route('password.new'))->assertRedirect(route('password.forgot'));

        // Submit form juga ditolak
        $this->post(route('password.update'), [
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('password.forgot'));
    }

    /**
     * 5. RateLimiter: 5 permintaan per 15 menit per role+nomor, 20 per jam per IP.
     */
    public function test_rate_limiter(): void
    {
        $this->withSession(['reset.role' => 'pengelola']);

        // 5 permintaan pertama untuk nomor yang sama berhasil
        for ($i = 0; $i < 5; $i++) {
            $resp = $this->post(route('password.send'), ['telepon' => '081234567890']);
            $resp->assertRedirect(route('verification.page'));
        }

        // Permintaan ke-6 melebihi batas rate limit nomor
        $resp6 = $this->post(route('password.send'), ['telepon' => '081234567890']);
        $resp6->assertSessionHasErrors(['telepon' => 'Terlalu banyak permintaan']);

        // Uji rate limiter per IP: reset IP limiter lalu uji 20 permintaan dengan nomor berbeda
        RateLimiter::clear('password_reset:ip:127.0.0.1');
        for ($i = 10; $i < 30; $i++) {
            $num = '0812' . str_pad((string) $i, 8, '0', STR_PAD_LEFT);
            $this->withSession(['reset.role' => 'pengelola']);
            $resp = $this->post(route('password.send'), ['telepon' => $num]);
            $resp->assertRedirect(route('verification.page'));
        }

        // Permintaan ke-21 dari IP yang sama ditolak
        $this->withSession(['reset.role' => 'pengelola']);
        $respIpBlocked = $this->post(route('password.send'), ['telepon' => '081299999999']);
        $respIpBlocked->assertSessionHasErrors(['telepon' => 'Terlalu banyak permintaan']);
    }

    /**
     * 6. Role tidak valid ditolak.
     */
    public function test_role_tidak_valid_ditolak(): void
    {
        // GET lupa-password dengan role tak terdaftar dialihkan ke /
        $resp1 = $this->get(route('password.forgot', ['role' => 'hacker']));
        $resp1->assertRedirect('/');

        // POST password.send tanpa role di session dialihkan ke /
        $resp2 = $this->post(route('password.send'), ['telepon' => '081234567890']);
        $resp2->assertRedirect('/');
    }

    /**
     * 7. 5 kali salah verifikasi hangus.
     */
    public function test_lima_kali_salah_hangus(): void
    {
        $this->withSession(['reset.role' => 'pengelola']);
        $this->post(route('password.send'), ['telepon' => '081234567890']);
        $code = $this->fakeSender->lastCode();
        $this->assertNotNull($code);

        $wrongCode = ($code === '111111') ? '222222' : '111111';

        // 5 kali salah
        for ($i = 0; $i < 5; $i++) {
            $resp = $this->post(route('verification.check'), [
                'otp' => str_split($wrongCode),
            ]);
            $resp->assertSessionHasErrors(['otp' => 'Kode salah atau kedaluwarsa']);
        }

        // Percobaan ke-6 dengan kode BENAR tetap ditolak karena sudah hangus
        $resp6 = $this->post(route('verification.check'), [
            'otp' => str_split($code),
        ]);
        $resp6->assertSessionHasErrors(['otp' => 'Kode salah atau kedaluwarsa']);
    }

    /**
     * 8. Nomor berformat berbeda diterima dan diseragamkan.
     */
    public function test_nomor_berformat_berbeda(): void
    {
        $this->withSession(['reset.role' => 'pengelola']);

        $resp = $this->post(route('password.send'), ['telepon' => '+62 812-3456-7890']);
        $resp->assertRedirect(route('verification.page'));
        $this->assertSame('6281234567890', session('reset.phone'));
    }

    /**
     * 9. Kode OTP tidak muncul di respons, session (json_encode), maupun log.
     */
    public function test_kode_otp_tidak_muncul_di_respons_session_maupun_log(): void
    {
        Log::spy();

        $this->withSession(['reset.role' => 'pengelola']);
        $resp = $this->post(route('password.send'), ['telepon' => '081234567890']);
        $code = $this->fakeSender->lastCode();
        $this->assertNotNull($code);

        // 1. Tidak ada kode di respons redirect
        $this->assertStringNotContainsString($code, $resp->getContent());

        // 2. Periksa seluruh isi session dengan json_encode(session()->all())
        $sessionJson = json_encode(session()->all());
        $this->assertStringNotContainsString($code, $sessionJson);

        // 3. Kode tidak dicatat di log pada level apapun
        Log::shouldNotHaveReceived('info', function ($message) use ($code) {
            return str_contains((string) $message, $code);
        });
        Log::shouldNotHaveReceived('warning', function ($message) use ($code) {
            return str_contains((string) $message, $code);
        });
        Log::shouldNotHaveReceived('error', function ($message) use ($code) {
            return str_contains((string) $message, $code);
        });
        Log::shouldNotHaveReceived('debug', function ($message) use ($code) {
            return str_contains((string) $message, $code);
        });
    }

    /**
     * 10. Non-local dengan driver log menghasilkan pesan generik tanpa memanggil issue/exists.
     */
    public function test_non_local_dengan_driver_log_menghasilkan_pesan_generik(): void
    {
        config(['metanoia.otp.driver' => 'log']);

        // Mock AccountDirectory yang tidak boleh dipanggil
        $mockDir = new class implements AccountDirectory {
            public bool $existsCalled = false;

            public function exists(string $role, string $phone): bool
            {
                $this->existsCalled = true;
                return true;
            }

            public function updatePassword(string $role, string $phone, string $hash): bool
            {
                return false;
            }
        };
        $this->app->instance(AccountDirectory::class, $mockDir);

        $this->withSession(['reset.role' => 'pengelola']);
        $resp = $this->post(route('password.send'), ['telepon' => '081234567890']);

        $resp->assertSessionHasErrors(['telepon' => 'Layanan pengiriman kode belum tersedia']);
        $this->assertFalse($mockDir->existsCalled);
        $this->assertCount(0, $this->fakeSender->messages);
    }

    /**
     * 11. updatePassword false tidak menampilkan flash sukses dan flag tetap ada.
     */
    public function test_update_password_false_tidak_menampilkan_flash_sukses(): void
    {
        // PendingAccountDirectory default mengembalikan false untuk updatePassword
        $this->withSession([
            'reset.role'           => 'pengelola',
            'reset.phone'          => '6281234567890',
            'reset.verified_until' => now()->addMinutes(10)->timestamp,
            'reset.verified_role'  => 'pengelola',
            'reset.verified_phone' => '6281234567890',
        ]);

        $resp = $this->post(route('password.update'), [
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $resp->assertSessionMissing('success');
        $resp->assertSessionHasErrors(['password' => 'Perubahan kata sandi belum dapat disimpan (penyimpanan akun belum tersedia)']);
        $this->assertNotNull(session('reset.verified_until'));
    }

    /**
     * 7a. Verifikasi nomor A, lalu POST password.send untuk nomor B ->
     * GET dan POST password.new ditolak (redirect password.forgot).
     * AccountDirectory::updatePassword TIDAK dipanggil untuk B (pakai spy).
     */
    public function test_verifikasi_nomor_a_lalu_kirim_nomor_b_membatalkan_akses_password_new(): void
    {
        $mockDirectory = $this->spy(AccountDirectory::class);
        $mockDirectory->shouldReceive('exists')->andReturn(true);

        // 1. Alur sukses sampai verifikasi nomor A
        $this->withSession(['reset.role' => 'pengelola']);
        $this->post(route('password.send'), ['telepon' => '081211111111']);
        $codeA = $this->fakeSender->lastCode();
        $this->assertNotNull($codeA);

        $this->post(route('verification.check'), ['otp' => str_split($codeA)]);
        $this->assertNotNull(session('reset.verified_until'));
        $this->assertSame('6281211111111', session('reset.verified_phone'));

        // 2. Pengguna mengirimkan OTP untuk nomor B
        $this->post(route('password.send'), ['telepon' => '081222222222']);
        // Flag verifikasi nomor A harus dibersihkan oleh sendOtp
        $this->assertNull(session('reset.verified_until'));
        $this->assertNull(session('reset.verified_phone'));
        $this->assertSame('6281222222222', session('reset.phone'));

        // 3. Akses GET password.new ditolak
        $respGet = $this->get(route('password.new'));
        $respGet->assertRedirect(route('password.forgot'));

        // 4. POST password.update ditolak
        $respPost = $this->post(route('password.update'), [
            'password'              => 'passwordBaru123',
            'password_confirmation' => 'passwordBaru123',
        ]);
        $respPost->assertRedirect(route('password.forgot'));

        // Pastikan updatePassword TIDAK PERNAH dipanggil
        $mockDirectory->shouldNotHaveReceived('updatePassword');
    }

    /**
     * 7b. Verifikasi, lalu GET password.forgot?role=<role lain> -> password.new ditolak.
     */
    public function test_verifikasi_lalu_ganti_role_di_forgot_membatalkan_akses_password_new(): void
    {
        // 1. Selesaikan verifikasi untuk role pengelola
        $this->withSession(['reset.role' => 'pengelola']);
        $this->post(route('password.send'), ['telepon' => '081234567890']);
        $code = $this->fakeSender->lastCode();

        $this->post(route('verification.check'), ['otp' => str_split($code)]);
        $this->assertNotNull(session('reset.verified_until'));

        // 2. Buka halaman forgot dengan role lain (bendahara)
        $respForgot = $this->get(route('password.forgot', ['role' => 'bendahara']));
        $respForgot->assertStatus(200);

        // Seluruh reset.* sebelumnya terhapus, kecuali reset.role baru
        $this->assertSame('bendahara', session('reset.role'));
        $this->assertNull(session('reset.verified_until'));
        $this->assertNull(session('reset.verified_role'));
        $this->assertNull(session('reset.verified_phone'));
        $this->assertNull(session('reset.phone'));

        // 3. Akses password.new ditolak
        $this->get(route('password.new'))->assertRedirect(route('password.forgot'));
    }

    /**
     * 7c. Flag dan semua reset.* terhapus setelah updatePassword sukses.
     */
    public function test_flag_dan_semua_reset_terhapus_setelah_update_password_sukses(): void
    {
        $mockDirectory = new class implements AccountDirectory {
            public function exists(string $role, string $phone): bool
            {
                return true;
            }

            public function updatePassword(string $role, string $phone, string $hash): bool
            {
                return true;
            }
        };
        $this->app->instance(AccountDirectory::class, $mockDirectory);

        $this->withSession([
            'reset.role'           => 'pengelola',
            'reset.phone'          => '6281234567890',
            'reset.shown'          => '0812******90',
            'reset.verified_until' => now()->addMinutes(10)->timestamp,
            'reset.verified_role'  => 'pengelola',
            'reset.verified_phone' => '6281234567890',
        ]);

        $resp = $this->post(route('password.update'), [
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $resp->assertRedirect(route('login.show', 'pengelola'));
        $resp->assertSessionHas('success');

        $this->assertNull(session('reset.role'));
        $this->assertNull(session('reset.phone'));
        $this->assertNull(session('reset.shown'));
        $this->assertNull(session('reset.verified_until'));
        $this->assertNull(session('reset.verified_role'));
        $this->assertNull(session('reset.verified_phone'));
    }

    /**
     * 7d. Error telepon tampil di respons lupa-password (assertSee).
     */
    public function test_error_telepon_tampil_di_respons_lupa_password(): void
    {
        $this->withSession(['reset.role' => 'pengelola']);

        // Input telepon tidak valid dan ikuti redirect untuk memastikan pesan error tampil
        $resp = $this->from(route('password.forgot', ['role' => 'pengelola']))
            ->followingRedirects()
            ->post(route('password.send'), ['telepon' => '12345']);

        $resp->assertStatus(200);
        $resp->assertSee('Nomor telepon tidak valid.');
    }
}
