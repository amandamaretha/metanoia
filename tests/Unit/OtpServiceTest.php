<?php

namespace Tests\Unit;

use App\Exceptions\InvalidPhoneException;
use App\Exceptions\OtpConfigurationException;
use App\Exceptions\OtpCooldownException;
use App\Exceptions\OtpDeliveryException;
use App\Exceptions\OtpLockedException;
use App\Services\LogOtpSender;
use App\Services\NullOtpSender;
use App\Services\OtpSender;
use App\Services\OtpService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Tests\Support\FakeOtpSender;
use Tests\TestCase;

class OtpServiceTest extends TestCase
{
    protected FakeOtpSender $sender;
    protected OtpService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->sender = new FakeOtpSender();
        $this->service = new OtpService($this->sender);
    }

    /**
     * 1. Kedaluwarsa (test time / travel): waktu lewat -> tolak dan hapus data.
     */
    public function test_kedaluwarsa(): void
    {
        $this->service->issue('pengelola', '081234567890');
        $code = $this->sender->lastCode();
        $this->assertNotNull($code);

        // Maju 301 detik melewati batas TTL (300 detik)
        $this->travel(301)->seconds();

        $verified = $this->service->verify('pengelola', '081234567890', $code);
        $this->assertFalse($verified);

        // Cache harus sudah terhapus
        $this->assertNull(Cache::get('otp:data:pengelola:6281234567890'));
    }

    /**
     * 2. 5 kali salah -> hangus dan tolak, kode tidak bisa dipakai lagi meski benar.
     */
    public function test_lima_kali_salah_hangus(): void
    {
        $this->service->issue('pengelola', '081234567890');
        $code = $this->sender->lastCode();
        $this->assertNotNull($code);

        $wrongCode = ($code === '111111') ? '222222' : '111111';

        // 4 kali salah: masih tersimpan
        for ($i = 1; $i <= 4; $i++) {
            $this->assertFalse($this->service->verify('pengelola', '081234567890', $wrongCode));
            $this->assertNotNull(Cache::get('otp:data:pengelola:6281234567890'));
        }

        // Percobaan ke-5 yang salah: hangus dan terhapus
        $this->assertFalse($this->service->verify('pengelola', '081234567890', $wrongCode));
        $this->assertNull(Cache::get('otp:data:pengelola:6281234567890'));

        // Percobaan ke-6 dengan KODE YANG BENAR tetap ditolak karena sudah hangus
        $this->assertFalse($this->service->verify('pengelola', '081234567890', $code));
    }

    /**
     * 3. Sekali pakai: setelah verifikasi berhasil, kode langsung terhapus.
     */
    public function test_sekali_pakai(): void
    {
        $this->service->issue('pengelola', '081234567890');
        $code = $this->sender->lastCode();
        $this->assertNotNull($code);

        // Verifikasi pertama berhasil
        $this->assertTrue($this->service->verify('pengelola', '081234567890', $code));

        // Verifikasi kedua dengan kode yang sama langsung ditolak
        $this->assertFalse($this->service->verify('pengelola', '081234567890', $code));
        $this->assertNull(Cache::get('otp:data:pengelola:6281234567890'));
    }

    /**
     * 4. Kode baru menggantikan kode lama.
     */
    public function test_kode_baru_menggantikan_lama(): void
    {
        $this->service->issue('pengelola', '081234567890');
        $code1 = $this->sender->lastCode();
        $this->assertNotNull($code1);

        // Lewati cooldown 60 detik untuk menerbitkan kode baru
        $this->travel(61)->seconds();

        $this->service->issue('pengelola', '081234567890');
        $code2 = $this->sender->lastCode();
        $this->assertNotNull($code2);

        // Kode pertama sudah tidak valid
        $this->assertFalse($this->service->verify('pengelola', '081234567890', $code1));

        // Kode kedua yang aktif dan valid
        $this->assertTrue($this->service->verify('pengelola', '081234567890', $code2));
    }

    /**
     * 5. Jeda kirim ulang: melempar OtpCooldownException dengan sisa detik.
     */
    public function test_jeda_kirim_ulang(): void
    {
        $this->service->issue('pengelola', '081234567890');

        try {
            // Permintaan kedua langsung tanpa menunggu jeda 60 detik
            $this->service->issue('pengelola', '081234567890');
            $this->fail('Harusnya melempar OtpCooldownException.');
        } catch (OtpCooldownException $e) {
            $this->assertSame('Mohon tunggu sebelum meminta kode verifikasi baru.', $e->getMessage());
            $this->assertGreaterThan(0, $e->getSecondsRemaining());
            $this->assertLessThanOrEqual(60, $e->getSecondsRemaining());
        }
    }

    /**
     * 6. Format kode salah: format bukan 6 digit ditolak dan tidak menghabiskan kuota.
     */
    public function test_format_salah(): void
    {
        $this->service->issue('pengelola', '081234567890');
        $code = $this->sender->lastCode();
        $this->assertNotNull($code);

        // Kurang dari 6 digit
        $this->assertFalse($this->service->verify('pengelola', '081234567890', '123'));
        // Lebih dari 6 digit
        $this->assertFalse($this->service->verify('pengelola', '081234567890', '1234567'));
        // Mengandung karakter non-angka
        $this->assertFalse($this->service->verify('pengelola', '081234567890', 'abcdef'));
        // String kosong
        $this->assertFalse($this->service->verify('pengelola', '081234567890', ''));

        // Format salah tidak memakan kuota percobaan, kode benar tetap bisa diverifikasi
        $this->assertTrue($this->service->verify('pengelola', '081234567890', $code));
    }

    /**
     * 7. Nomor berformat berbeda (08..., 628..., +62 8...) dianggap sama.
     */
    public function test_nomor_berformat_berbeda_dianggap_sama(): void
    {
        // Terbitkan dengan format lokal awalan 0
        $this->service->issue('pengelola', '081234567890');
        $code = $this->sender->lastCode();
        $this->assertNotNull($code);

        // Verifikasi dengan format internasional awalan +62 dengan spasi dan strip
        $this->assertTrue($this->service->verify('pengelola', '+62 812-3456-7890', $code));
    }

    /**
     * 8. issue() tidak mengembalikan kode (void/null). Kode hanya keluar lewat OtpSender.
     */
    public function test_issue_tidak_mengembalikan_kode(): void
    {
        $result = $this->service->issue('pengelola', '081234567890');

        $this->assertNull($result);
        $this->assertCount(1, $this->sender->messages);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $this->sender->lastCode());
    }

    /**
     * 9. Sender non-local gagal terang-terangan: LogOtpSender & NullOtpSender di luar 'local'
     * melempar OtpDeliveryException dan tidak mencatat kode ke log. Service meneruskan kegagalan.
     */
    public function test_sender_non_local_gagal_terang_terangan(): void
    {
        // Di lingkungan testing (bukan 'local')
        $this->assertFalse(app()->environment('local'));

        Log::spy();

        $logSender = new LogOtpSender();
        try {
            $logSender->send('pengelola', '6281234567890', '123456');
            $this->fail('Harusnya melempar OtpDeliveryException.');
        } catch (OtpDeliveryException $e) {
            $this->assertStringContainsString('hanya diizinkan pada environment \'local\'', $e->getMessage());
        }

        // Pastikan tidak ada kode yang dicatat ke log
        Log::shouldNotHaveReceived('info');

        $nullSender = new NullOtpSender();
        $this->expectException(OtpDeliveryException::class);
        $nullSender->send('pengelola', '6281234567890', '123456');
    }

    /**
     * 10. APP_KEY kosong -> lempar exception OtpConfigurationException.
     */
    public function test_app_key_kosong_lempar_exception(): void
    {
        config(['app.key' => '']);

        $this->expectException(OtpConfigurationException::class);
        $this->expectExceptionMessage('APP_KEY tidak terkonfigurasi.');

        $this->service->issue('pengelola', '081234567890');
    }

    /**
     * 11. Driver tak dikenal -> exception InvalidArgumentException.
     */
    public function test_driver_tak_dikenal_lempar_exception(): void
    {
        config(['metanoia.otp.driver' => 'telepati']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Driver OTP [telepati] tidak didukung.');

        app(OtpSender::class);
    }

    /**
     * 12. Kode tidak melar: terbitkan, maju 200 detik, salah, maju 150 detik -> ditolak.
     */
    public function test_kode_tidak_melar(): void
    {
        $this->service->issue('pengelola', '081234567890');
        $code = $this->sender->lastCode();
        $this->assertNotNull($code);

        // Maju 200 detik (masih tersisa 100 detik dari TTL 300)
        $this->travel(200)->seconds();

        // Verifikasi dengan kode yang salah
        $wrongCode = ($code === '111111') ? '222222' : '111111';
        $this->assertFalse($this->service->verify('pengelola', '081234567890', $wrongCode));

        // Maju 150 detik lagi (total 350 detik sejak awal, melebihi TTL 300 detik)
        $this->travel(150)->seconds();

        // Kode tidak boleh melar: harus tetap kedaluwarsa dan ditolak
        $this->assertFalse($this->service->verify('pengelola', '081234567890', $code));
        $this->assertNull(Cache::get('otp:data:pengelola:6281234567890'));
    }

    /**
     * 13. Salah dua kali lalu benar sukses.
     */
    public function test_salah_dua_kali_lalu_benar_sukses(): void
    {
        $this->service->issue('pengelola', '081234567890');
        $code = $this->sender->lastCode();
        $this->assertNotNull($code);

        $wrongCode = ($code === '111111') ? '222222' : '111111';

        // Salah pertama
        $this->assertFalse($this->service->verify('pengelola', '081234567890', $wrongCode));
        // Salah kedua
        $this->assertFalse($this->service->verify('pengelola', '081234567890', $wrongCode));
        // Ketiga dengan kode benar -> sukses
        $this->assertTrue($this->service->verify('pengelola', '081234567890', $code));

        // Sudah sekali pakai: verifikasi lagi harus gagal
        $this->assertFalse($this->service->verify('pengelola', '081234567890', $code));
    }

    /**
     * 14. Anggaran gagal per jam lintas penerbitan ulang.
     */
    public function test_anggaran_gagal_per_jam_lintas_penerbitan_ulang(): void
    {
        // Set konfigurasi 3 kegagalan per jam untuk keperluan tes
        config(['metanoia.otp.max_failures_per_hour' => 3]);

        // Terbitan 1: lakukan 2 verifikasi salah
        $this->service->issue('pengelola', '081234567890');
        $code1 = $this->sender->lastCode();
        $wrongCode = ($code1 === '111111') ? '222222' : '111111';

        $this->assertFalse($this->service->verify('pengelola', '081234567890', $wrongCode)); // Gagal 1
        $this->assertFalse($this->service->verify('pengelola', '081234567890', $wrongCode)); // Gagal 2

        // Maju 61 detik melewati cooldown jeda kirim ulang
        $this->travel(61)->seconds();

        // Terbitkan kode baru: anggaran gagal tidak boleh direset
        $this->service->issue('pengelola', '081234567890');
        $code2 = $this->sender->lastCode();
        $this->assertNotNull($code2);

        // Verifikasi salah ke-3 pada kode baru (mencapai batas maksimal 3 gagal per jam)
        $this->assertFalse($this->service->verify('pengelola', '081234567890', $wrongCode)); // Gagal 3

        // Panggilan verify berikutnya melempar OtpLockedException
        try {
            $this->service->verify('pengelola', '081234567890', $code2);
            $this->fail('Harusnya melempar OtpLockedException pada verify().');
        } catch (OtpLockedException $e) {
            $this->assertStringContainsString('Akun terkunci sementara', $e->getMessage());
        }

        // Panggilan issue berikutnya juga melempar OtpLockedException
        try {
            $this->service->issue('pengelola', '081234567890');
            $this->fail('Harusnya melempar OtpLockedException pada issue().');
        } catch (OtpLockedException $e) {
            $this->assertStringContainsString('Akun terkunci sementara', $e->getMessage());
        }

        // Maju melewati jendela 1 jam (3601 detik)
        $this->travel(3601)->seconds();

        // Kunci terbuka kembali, issue berjalan normal
        $this->service->issue('pengelola', '081234567890');
        $this->assertNotNull($this->sender->lastCode());
    }

    /**
     * 15. Jeda dan cache dibersihkan bila sender gagal.
     */
    public function test_jeda_dan_cache_dibersihkan_bila_sender_gagal(): void
    {
        $failingSender = new class implements OtpSender {
            public function send(string $role, string $telepon, string $kode): void
            {
                throw new OtpDeliveryException('Simulasi kegagalan gateway pengiriman');
            }
        };

        $serviceWithFailingSender = new OtpService($failingSender);

        try {
            $serviceWithFailingSender->issue('pengelola', '081234567890');
            $this->fail('Harusnya melempar OtpDeliveryException');
        } catch (OtpDeliveryException $e) {
            $this->assertSame('Simulasi kegagalan gateway pengiriman', $e->getMessage());
        }

        // Cache data OTP dan jeda cooldown harus bersih
        $this->assertNull(Cache::get('otp:data:pengelola:6281234567890'));
        $this->assertNull(Cache::get('otp:cooldown:pengelola:6281234567890'));

        // Karena cooldown bersih, issue dengan sender normal langsung bisa dilakukan
        $this->service->issue('pengelola', '081234567890');
        $this->assertNotNull($this->sender->lastCode());
    }

    /**
     * 16. Tiap exception bertipe.
     */
    public function test_tiap_exception_bertipe(): void
    {
        // 1. InvalidPhoneException
        try {
            $this->service->issue('pengelola', 'nomor-salah');
            $this->fail('Harusnya melempar InvalidPhoneException');
        } catch (InvalidPhoneException $e) {
            $this->assertInstanceOf(InvalidPhoneException::class, $e);
        }

        // 2. OtpCooldownException membawa sisa detik
        $this->service->issue('pengelola', '081234567890');
        try {
            $this->service->issue('pengelola', '081234567890');
            $this->fail('Harusnya melempar OtpCooldownException');
        } catch (OtpCooldownException $e) {
            $this->assertInstanceOf(OtpCooldownException::class, $e);
            $this->assertGreaterThan(0, $e->getSecondsRemaining());
        }

        // 3. OtpConfigurationException jika APP_KEY kosong
        config(['app.key' => '']);
        try {
            $this->service->issue('pengelola', '081299999999');
            $this->fail('Harusnya melempar OtpConfigurationException');
        } catch (OtpConfigurationException $e) {
            $this->assertInstanceOf(OtpConfigurationException::class, $e);
        }
        // Kembalikan APP_KEY testing
        config(['app.key' => 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=']);

        // 4. OtpLockedException
        config(['metanoia.otp.max_failures_per_hour' => 1]);
        $this->service->issue('pengelola', '081288888888');
        $this->service->verify('pengelola', '081288888888', '000000'); // 1 kali gagal = batas tercapai
        try {
            $this->service->verify('pengelola', '081288888888', '000000');
            $this->fail('Harusnya melempar OtpLockedException');
        } catch (OtpLockedException $e) {
            $this->assertInstanceOf(OtpLockedException::class, $e);
        }

        // 5. OtpDeliveryException
        $nullSender = new NullOtpSender();
        $this->expectException(OtpDeliveryException::class);
        $nullSender->send('pengelola', '6281234567890', '123456');
    }

    /**
     * 17. Role tak dikenal ditolak dengan InvalidArgumentException.
     */
    public function test_role_tak_dikenal_ditolak(): void
    {
        // Pada issue()
        try {
            $this->service->issue('alien', '081234567890');
            $this->fail('Harusnya melempar InvalidArgumentException pada issue');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('Role portal [alien] tidak valid.', $e->getMessage());
        }

        // Pada verify()
        try {
            $this->service->verify('alien', '081234567890', '123456');
            $this->fail('Harusnya melempar InvalidArgumentException pada verify');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('Role portal [alien] tidak valid.', $e->getMessage());
        }
    }

    /**
     * 18. Normalisasi nomor berbagai bentuk valid (0, 62, +62, 8, 00, +62 0812).
     */
    public function test_normalisasi_nomor_berbagai_bentuk_valid(): void
    {
        // Awalan lokal '08...'
        $this->assertSame('6281234567890', OtpService::normalizePhoneNumber('081234567890'));

        // Awalan internasional '628...'
        $this->assertSame('6281234567890', OtpService::normalizePhoneNumber('6281234567890'));

        // Awalan dengan plus '+628...'
        $this->assertSame('6281234567890', OtpService::normalizePhoneNumber('+6281234567890'));

        // Awalan '8...'
        $this->assertSame('6281234567890', OtpService::normalizePhoneNumber('81234567890'));

        // Awalan internasional '0062...'
        $this->assertSame('6281234567890', OtpService::normalizePhoneNumber('006281234567890'));

        // Awalan '008...'
        $this->assertSame('6281234567890', OtpService::normalizePhoneNumber('0081234567890'));

        // Awalan '+62 0812...'
        $this->assertSame('6281234567890', OtpService::normalizePhoneNumber('+62 081234567890'));

        // Awalan '+62-0812...' dengan tanda strip dan spasi
        $this->assertSame('6281234567890', OtpService::normalizePhoneNumber('+62 0812-3456-7890'));

        // Awalan '0062 0812...'
        $this->assertSame('6281234567890', OtpService::normalizePhoneNumber('0062 0812 3456 7890'));

        // Batas minimal panjang 10 digit (misal '081234567' -> '6281234567')
        $this->assertSame('6281234567', OtpService::normalizePhoneNumber('081234567'));

        // Batas maksimal panjang 15 digit (misal '628123456789012')
        $this->assertSame('628123456789012', OtpService::normalizePhoneNumber('628123456789012'));
    }

    /**
     * 19. Normalisasi nomor tidak valid melempar InvalidPhoneException.
     */
    public function test_normalisasi_nomor_tidak_valid_lempar_invalid_phone_exception(): void
    {
        // String kosong
        try {
            OtpService::normalizePhoneNumber('');
            $this->fail('Harusnya melempar InvalidPhoneException');
        } catch (InvalidPhoneException $e) {
            $this->assertInstanceOf(InvalidPhoneException::class, $e);
        }

        // Karakter non-digit saja
        try {
            OtpService::normalizePhoneNumber('abc-xyz');
            $this->fail('Harusnya melempar InvalidPhoneException');
        } catch (InvalidPhoneException $e) {
            $this->assertInstanceOf(InvalidPhoneException::class, $e);
        }

        // Kurang dari 10 digit (misal '08123' -> 628123 = 6 digit)
        try {
            OtpService::normalizePhoneNumber('08123');
            $this->fail('Harusnya melempar InvalidPhoneException');
        } catch (InvalidPhoneException $e) {
            $this->assertInstanceOf(InvalidPhoneException::class, $e);
        }

        // Lebih dari 15 digit (16 digit)
        try {
            OtpService::normalizePhoneNumber('6281234567890123');
            $this->fail('Harusnya melempar InvalidPhoneException');
        } catch (InvalidPhoneException $e) {
            $this->assertInstanceOf(InvalidPhoneException::class, $e);
        }

        // Nomor yang tidak diawali kode negara 62
        try {
            OtpService::normalizePhoneNumber('12345678901');
            $this->fail('Harusnya melempar InvalidPhoneException');
        } catch (InvalidPhoneException $e) {
            $this->assertInstanceOf(InvalidPhoneException::class, $e);
        }
    }

    /**
     * 20. Urutan di issue(): validasi config dan hash SEBELUM Cache::add jeda kirim ulang.
     */
    public function test_urutan_issue_validasi_config_dan_hash_sebelum_jeda_cooldown(): void
    {
        config(['app.key' => '']);

        try {
            $this->service->issue('pengelola', '081234567890');
            $this->fail('Harusnya melempar OtpConfigurationException');
        } catch (OtpConfigurationException $e) {
            $this->assertInstanceOf(OtpConfigurationException::class, $e);
        }

        // Pastikan jeda kirim ulang (cooldown) TIDAK disimpan karena validasi config gagal terlebih dahulu
        $this->assertNull(Cache::get('otp:cooldown:pengelola:6281234567890'));
    }
}
