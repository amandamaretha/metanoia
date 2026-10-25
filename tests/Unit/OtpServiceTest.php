<?php

namespace Tests\Unit;

use App\Exceptions\OtpDeliveryException;
use App\Services\LogOtpSender;
use App\Services\NullOtpSender;
use App\Services\OtpSender;
use App\Services\OtpService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class FakeOtpSender implements OtpSender
{
    /** @var array<int, array{role: string, telepon: string, kode: string}> */
    public array $messages = [];

    public function send(string $role, string $telepon, string $kode): void
    {
        $this->messages[] = [
            'role'    => $role,
            'telepon' => $telepon,
            'kode'    => $kode,
        ];
    }

    public function lastCode(): ?string
    {
        $last = end($this->messages);
        return $last ? $last['kode'] : null;
    }
}

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
     * 5. Jeda kirim ulang: permintaan sebelum jeda berakhir ditolak lewat exception.
     */
    public function test_jeda_kirim_ulang(): void
    {
        $this->service->issue('pengelola', '081234567890');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Mohon tunggu sebelum meminta kode verifikasi baru.');

        // Permintaan kedua langsung tanpa menunggu jeda 60 detik
        $this->service->issue('pengelola', '081234567890');
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
     * 10. APP_KEY kosong -> lempar exception RuntimeException.
     */
    public function test_app_key_kosong_lempar_exception(): void
    {
        config(['app.key' => '']);

        $this->expectException(RuntimeException::class);
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
}
