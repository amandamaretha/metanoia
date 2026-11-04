<?php

namespace Tests\Feature;

use App\Models\Nasabah;
use App\Models\Petugas;
use App\Services\OtpSender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Support\FakeOtpSender;
use Tests\TestCase;

class PengaturanAkunPetugasTest extends TestCase
{
    use RefreshDatabase;

    protected Petugas $pengelolaUtama;

    protected Petugas $pengelolaDua;

    protected Petugas $bendahara;

    protected Petugas $ketua;

    protected FakeOtpSender $fakeSender;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fakeSender = new FakeOtpSender;
        $this->app->instance(OtpSender::class, $this->fakeSender);
        config(['metanoia.otp.driver' => 'fake']);

        $this->pengelolaUtama = new Petugas([
            'nama' => 'Pengelola Satu',
            'no_telp' => '6281200000001',
            'password' => 'secret123',
        ]);
        $this->pengelolaUtama->forceFill(['jabatan' => 'pengelola'])->save();

        $this->pengelolaDua = new Petugas([
            'nama' => 'Pengelola Dua',
            'no_telp' => '6281200000002',
            'password' => 'secret123',
        ]);
        $this->pengelolaDua->forceFill(['jabatan' => 'pengelola'])->save();

        $this->bendahara = new Petugas([
            'nama' => 'Bendahara Tiga',
            'no_telp' => '6281200000003',
            'password' => 'secret123',
        ]);
        $this->bendahara->forceFill(['jabatan' => 'bendahara'])->save();

        $this->ketua = new Petugas([
            'nama' => 'Ketua Empat',
            'no_telp' => '6281200000004',
            'password' => 'secret123',
        ]);
        $this->ketua->forceFill(['jabatan' => 'ketua'])->save();
    }

    public function test_akses_pengaturan_dan_profil_terbuka_untuk_semua_jabatan_petugas(): void
    {
        // Pengelola
        $this->actingAs($this->pengelolaUtama, 'petugas');
        $this->get(route('pengelola.pengaturan'))->assertOk()->assertSee('Pengaturan');

        // Bendahara
        $this->actingAs($this->bendahara, 'petugas');
        $this->get(route('pengelola.pengaturan'))->assertOk();

        // Ketua
        $this->actingAs($this->ketua, 'petugas');
        $this->get(route('pengelola.pengaturan'))->assertOk();

        // Tamu (belum login) -> redirect
        auth('petugas')->logout();
        $this->get(route('pengelola.pengaturan'))->assertRedirect(route('login.show', ['role' => 'petugas']));
    }

    public function test_akses_tambah_dan_kelola_akun_ditolak_untuk_non_pengelola(): void
    {
        // Bendahara mencoba akses -> 403
        $this->actingAs($this->bendahara, 'petugas');
        $this->get(route('pengelola.petugas.tambah'))->assertStatus(403);
        $this->get(route('pengelola.petugas.index'))->assertStatus(403);
        $this->post(route('pengelola.petugas.store'), [])->assertStatus(403);

        // Ketua mencoba akses -> 403
        $this->actingAs($this->ketua, 'petugas');
        $this->get(route('pengelola.petugas.tambah'))->assertStatus(403);
        $this->get(route('pengelola.petugas.index'))->assertStatus(403);

        // Pengelola -> 200
        $this->actingAs($this->pengelolaUtama, 'petugas');
        $this->get(route('pengelola.petugas.tambah'))->assertOk();
        $this->get(route('pengelola.petugas.index'))->assertOk();
    }

    public function test_pengelola_berhasil_menambahkan_akun_petugas_baru(): void
    {
        $this->actingAs($this->pengelolaUtama, 'petugas');

        $response = $this->post(route('pengelola.petugas.store'), [
            'nama' => 'Petugas Baru Metanoia',
            'no_telp' => '081299998888',
            'jabatan' => 'bendahara',
            'password' => 'password123',
            'konfirmasi_password' => 'password123',
        ]);

        $response->assertRedirect(route('pengelola.pengaturan'));
        $response->assertSessionHas('success', 'Anda berhasil menambahkan akun!');

        $petugasBaru = Petugas::where('nama', 'Petugas Baru Metanoia')->first();
        $this->assertNotNull($petugasBaru);
        $this->assertEquals('6281299998888', $petugasBaru->no_telp);
        $this->assertEquals('bendahara', $petugasBaru->jabatan);
        $this->assertTrue(Hash::check('password123', $petugasBaru->password));
    }

    public function test_tambah_petugas_ditolak_jika_nomor_telepon_sudah_terdaftar_di_petugas_atau_nasabah(): void
    {
        $this->actingAs($this->pengelolaUtama, 'petugas');

        // Daftarkan nasabah dengan nomor tertentu
        Nasabah::create([
            'nama' => 'Nasabah Existing',
            'nik' => '3201010101019999',
            'alamat' => 'Jl. Merdeka No. 10',
            'no_telp' => '6281277777777',
            'password' => 'secret123',
        ]);

        // Coba tambah petugas dengan nomor yang sama dengan nasabah
        $response = $this->from(route('pengelola.petugas.tambah'))->post(route('pengelola.petugas.store'), [
            'nama' => 'Petugas Kloning',
            'no_telp' => '081277777777',
            'jabatan' => 'pengelola',
            'password' => 'password123',
            'konfirmasi_password' => 'password123',
        ]);

        $response->assertRedirect(route('pengelola.petugas.tambah'));
        $response->assertSessionHasErrors('no_telp');

        // Coba tambah petugas dengan nomor yang sama dengan petugas lain
        $responsePetugas = $this->from(route('pengelola.petugas.tambah'))->post(route('pengelola.petugas.store'), [
            'nama' => 'Petugas Kloning 2',
            'no_telp' => '081200000003', // Nomor bendahara
            'jabatan' => 'pengelola',
            'password' => 'password123',
            'konfirmasi_password' => 'password123',
        ]);

        $responsePetugas->assertRedirect(route('pengelola.petugas.tambah'));
        $responsePetugas->assertSessionHasErrors('no_telp');
    }

    public function test_tambah_petugas_ditolak_jika_konfirmasi_password_tidak_cocok(): void
    {
        $this->actingAs($this->pengelolaUtama, 'petugas');

        $response = $this->from(route('pengelola.petugas.tambah'))->post(route('pengelola.petugas.store'), [
            'nama' => 'Petugas Password Salah',
            'no_telp' => '081255554444',
            'jabatan' => 'bendahara',
            'password' => 'password123',
            'konfirmasi_password' => 'passwordBeda',
        ]);

        $response->assertRedirect(route('pengelola.petugas.tambah'));
        $response->assertSessionHasErrors('password');
    }

    public function test_kelola_akun_pencarian_nama_petugas(): void
    {
        $this->actingAs($this->pengelolaUtama, 'petugas');

        $response = $this->get(route('pengelola.petugas.index', ['search' => 'Bendahara']));
        $response->assertOk();
        $response->assertSee('Bendahara Tiga');
        $response->assertDontSee('Ketua Empat');
    }

    public function test_kelola_akun_edit_nama_nomor_dan_jabatan(): void
    {
        $this->actingAs($this->pengelolaUtama, 'petugas');

        $response = $this->put(route('pengelola.petugas.update', $this->bendahara), [
            'nama' => 'Bendahara Tiga Revisi',
            'no_telp' => '081299991111',
            'jabatan' => 'pengelola',
        ]);

        $response->assertRedirect(route('pengelola.petugas.index'));
        $this->bendahara->refresh();
        $this->assertEquals('Bendahara Tiga Revisi', $this->bendahara->nama);
        $this->assertEquals('6281299991111', $this->bendahara->no_telp);
        $this->assertEquals('pengelola', $this->bendahara->jabatan);
    }

    public function test_tolak_menghapus_diri_sendiri(): void
    {
        $this->actingAs($this->pengelolaUtama, 'petugas');

        $response = $this->delete(route('pengelola.petugas.destroy', $this->pengelolaUtama));
        $response->assertSessionHasErrors('error');
        $this->assertNotSoftDeleted('petugas', ['id' => $this->pengelolaUtama->id]);
    }

    public function test_tolak_menghapus_pengelola_terakhir(): void
    {
        $this->actingAs($this->pengelolaUtama, 'petugas');

        // Hapus pengelolaDua terlebih dahulu
        $this->delete(route('pengelola.petugas.destroy', $this->pengelolaDua));
        $this->assertSoftDeleted('petugas', ['id' => $this->pengelolaDua->id]);

        // Sekarang hanya tersisa 1 pengelola aktif: pengelolaUtama.
        // Ubah konteks login ke bendahara sementara untuk mencoba menghapus pengelolaUtama (simulasi)
        // Namun destroy dibatasi middleware jabatan:pengelola. Jika pengelolaUtama mencoba menghapus akun pengelola lain yang merupakan satu-satunya, tolak.
        // Buat pengelola dummy ke-3 lalu jadikan 1
        $pengelolaTiga = new Petugas([
            'nama' => 'Pengelola Tiga',
            'no_telp' => '6281200000099',
            'password' => 'secret123',
        ]);
        $pengelolaTiga->forceFill(['jabatan' => 'pengelola'])->save();

        // Sekarang ada pengelolaUtama dan pengelolaTiga.
        // Hapus pengelolaTiga
        $this->delete(route('pengelola.petugas.destroy', $pengelolaTiga));
        $this->assertSoftDeleted('petugas', ['id' => $pengelolaTiga->id]);

        // Sekarang pengelolaUtama adalah satu-satunya pengelola aktif yang tersisa.
        // Jika ada usaha menghapus pengelolaUtama (walaupun dicek diri sendiri juga), kita uji coba jika ada method yang memanggil dengan 1 pengelola tersisa:
        $pengelolaSisa = Petugas::where('jabatan', 'pengelola')->get();
        $this->assertCount(1, $pengelolaSisa);

        $response = $this->delete(route('pengelola.petugas.destroy', $this->pengelolaUtama));
        $response->assertSessionHasErrors('error');
        $this->assertNotSoftDeleted('petugas', ['id' => $this->pengelolaUtama->id]);
    }

    public function test_tolak_menurunkan_jabatan_pengelola_terakhir(): void
    {
        $this->actingAs($this->pengelolaUtama, 'petugas');

        // Hapus pengelolaDua sehingga hanya tersisa pengelolaUtama sebagai pengelola tunggal
        $this->pengelolaDua->delete();

        $this->assertEquals(1, Petugas::where('jabatan', 'pengelola')->count());

        // Coba turunkan jabatan pengelolaUtama menjadi bendahara
        $response = $this->put(route('pengelola.petugas.update', $this->pengelolaUtama), [
            'nama' => 'Pengelola Utama',
            'no_telp' => $this->pengelolaUtama->no_telp,
            'jabatan' => 'bendahara', // Turunkan jabatan
        ]);

        $response->assertSessionHasErrors('jabatan');
        $this->pengelolaUtama->refresh();
        $this->assertEquals('pengelola', $this->pengelolaUtama->jabatan);
    }

    public function test_ubah_profil_request_otp_ganti_telepon_sukses_dan_tolak_nomor_lama_salah(): void
    {
        $this->actingAs($this->pengelolaUtama, 'petugas');

        // 1. Nomor lama salah -> ditolak
        $responseWrongOld = $this->postJson(route('pengaturan.profil.request-otp'), [
            'telepon_lama' => '081299999999',
            'telepon_baru' => '081288887777',
        ]);
        $responseWrongOld->assertStatus(422);
        $responseWrongOld->assertJsonFragment(['field' => 'telepon_lama']);

        // 2. Nomor baru milik akun lain -> ditolak
        $responseDuplicate = $this->postJson(route('pengaturan.profil.request-otp'), [
            'telepon_lama' => '081200000001',
            'telepon_baru' => '081200000003', // Nomor bendahara
        ]);
        $responseDuplicate->assertStatus(422);
        $responseDuplicate->assertJsonFragment(['field' => 'telepon_baru']);

        // 3. Valid -> OTP terkirim
        $responseValid = $this->postJson(route('pengaturan.profil.request-otp'), [
            'telepon_lama' => '081200000001',
            'telepon_baru' => '081288887777',
        ]);
        $responseValid->assertOk();
        $responseValid->assertJsonFragment(['success' => true]);
        $this->assertEquals('6281288887777', end($this->fakeSender->messages)['telepon']);
    }

    public function test_ubah_profil_verifikasi_otp_salah_dan_benar(): void
    {
        $this->actingAs($this->pengelolaUtama, 'petugas');

        // Request OTP
        $this->postJson(route('pengaturan.profil.request-otp'), [
            'telepon_lama' => '081200000001',
            'telepon_baru' => '081288887777',
        ]);
        $correctCode = $this->fakeSender->lastCode();

        // Kode salah -> ditolak
        $responseWrong = $this->postJson(route('pengaturan.profil.verify-otp'), [
            'kode' => '000000',
        ]);
        $responseWrong->assertStatus(422);

        // Kode benar -> sukses
        $responseCorrect = $this->postJson(route('pengaturan.profil.verify-otp'), [
            'kode' => $correctCode,
        ]);
        $responseCorrect->assertOk();
        $responseCorrect->assertJsonFragment(['success' => true, 'message' => 'Verifikasi Berhasil!']);
    }

    public function test_simpan_perubahan_profil_hanya_mengubah_nomor_jika_telah_terverifikasi_otp(): void
    {
        $this->actingAs($this->pengelolaUtama, 'petugas');

        // 1. Coba ubah nomor langsung tanpa verifikasi OTP -> abort 422
        $responseDirect = $this->post(route('pengaturan.profil.update'), [
            'nama' => 'Pengelola Baru Nama',
            'telepon' => '081288887777',
        ]);
        $responseDirect->assertStatus(422);

        // 2. Lakukan alur lengkap OTP
        $this->postJson(route('pengaturan.profil.request-otp'), [
            'telepon_lama' => '081200000001',
            'telepon_baru' => '081288887777',
        ]);
        $this->postJson(route('pengaturan.profil.verify-otp'), [
            'kode' => $this->fakeSender->lastCode(),
        ]);

        // 3. Tekan "Simpan Perubahan"
        $responseSave = $this->post(route('pengaturan.profil.update'), [
            'nama' => 'Pengelola Satu Terupdate',
            'telepon' => '6281288887777',
        ]);
        $responseSave->assertRedirect(route('pengelola.pengaturan'));
        $responseSave->assertSessionHas('success');

        $this->pengelolaUtama->refresh();
        $this->assertEquals('Pengelola Satu Terupdate', $this->pengelolaUtama->nama);
        $this->assertEquals('6281288887777', $this->pengelolaUtama->no_telp);
    }

    public function test_ubah_password_petugas_dan_dapat_login_kembali(): void
    {
        $this->actingAs($this->bendahara, 'petugas');

        // Password lama salah -> error
        $responseWrong = $this->from(route('pengelola.pengaturan'))->post(route('pengaturan.password.update'), [
            'password_lama' => 'wrongpass',
            'password_baru' => 'newpassword123',
            'konfirmasi_password' => 'newpassword123',
        ]);
        $responseWrong->assertRedirect(route('pengelola.pengaturan'));
        $responseWrong->assertSessionHasErrors('password_lama');

        // Password baru dan konfirmasi tidak cocok -> error
        $responseMismatch = $this->from(route('pengelola.pengaturan'))->post(route('pengaturan.password.update'), [
            'password_lama' => 'secret123',
            'password_baru' => 'newpassword123',
            'konfirmasi_password' => 'mismatch123',
        ]);
        $responseMismatch->assertRedirect(route('pengelola.pengaturan'));
        $responseMismatch->assertSessionHasErrors('password_baru');

        // Berhasil ubah password
        $responseSuccess = $this->from(route('pengelola.pengaturan'))->post(route('pengaturan.password.update'), [
            'password_lama' => 'secret123',
            'password_baru' => 'newpassword123',
            'konfirmasi_password' => 'newpassword123',
        ]);
        $responseSuccess->assertRedirect(route('pengelola.pengaturan'));
        $responseSuccess->assertSessionHas('success');

        // Logout dan login dengan password baru
        auth('petugas')->logout();
        $loginRes = $this->post(route('login.attempt', ['role' => 'petugas']), [
            'telepon' => $this->bendahara->no_telp,
            'password' => 'newpassword123',
        ]);
        $loginRes->assertRedirect(route('bendahara.transaksi'));
        $this->assertAuthenticatedAs($this->bendahara, 'petugas');
    }
}
