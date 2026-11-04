<?php

namespace Tests\Feature;

use App\Models\JenisSampah;
use App\Models\Nasabah;
use App\Models\Nota;
use App\Models\Petugas;
use App\Models\Setoran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NasabahManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Petugas $pengelola;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pengelola = new Petugas([
            'nama' => 'Pengelola Utama',
            'no_telp' => '6281999999999',
            'password' => 'secret123',
        ]);
        $this->pengelola->forceFill(['jabatan' => 'pengelola'])->save();
    }

    /**
     * 1. Calon nasabah dapat mendaftar akun baru dan diarahkan ke login nasabah.
     */
    public function test_calon_nasabah_dapat_mendaftar_akun_baru(): void
    {
        $this->get(route('nasabah.register'))->assertStatus(200);

        $payload = [
            'nama' => 'Ahmad Subarjo',
            'nik' => '3201012345670001',
            'alamat' => 'Jl. Kebon Jeruk No. 12',
            'telepon' => '081234567891',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post(route('nasabah.register.submit'), $payload);

        $response->assertRedirect(route('login.show', 'nasabah'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('nasabah', [
            'nama' => 'Ahmad Subarjo',
            'nik' => '3201012345670001',
            'alamat' => 'Jl. Kebon Jeruk No. 12',
            'no_telp' => '6281234567891', // Dinormalisasi ke format 62
            'status' => 'aktif',
            'saldo' => 0,
        ]);

        $nasabah = Nasabah::where('nik', '3201012345670001')->firstOrFail();
        $this->assertTrue(Hash::check('password123', $nasabah->password));
    }

    /**
     * 2. Pendaftaran dengan NIK ganda atau nomor telepon ganda ditolak.
     */
    public function test_pendaftaran_dengan_nik_atau_telepon_ganda_ditolak(): void
    {
        Nasabah::create([
            'nama' => 'Nasabah Eksisting',
            'nik' => '3201012345670002',
            'alamat' => 'Jl. Mawar 5',
            'no_telp' => '6281234567892',
            'password' => 'secret123',
        ]);

        // Coba daftar dengan NIK yang sama
        $responseNik = $this->from(route('nasabah.register'))->post(route('nasabah.register.submit'), [
            'nama' => 'Nasabah Baru 1',
            'nik' => '3201012345670002',
            'alamat' => 'Jl. Lain',
            'telepon' => '081299990001',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $responseNik->assertRedirect(route('nasabah.register'));
        $responseNik->assertSessionHasErrors(['nik']);

        // Coba daftar dengan nomor telepon yang sama (format 08...)
        $responseTelp = $this->from(route('nasabah.register'))->post(route('nasabah.register.submit'), [
            'nama' => 'Nasabah Baru 2',
            'nik' => '3201012345679999',
            'alamat' => 'Jl. Lain Lagi',
            'telepon' => '081234567892',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $responseTelp->assertRedirect(route('nasabah.register'));
        $responseTelp->assertSessionHasErrors(['telepon']);
    }

    /**
     * 3. Akses riwayat dan nota orang lain ditolak 403.
     */
    public function test_akses_riwayat_dan_nota_orang_lain_ditolak_403(): void
    {
        $nasabahA = Nasabah::create([
            'nama' => 'Nasabah A',
            'nik' => '3201012345670003',
            'alamat' => 'Jl. Anggrek',
            'no_telp' => '6281300000003',
            'password' => 'secret123',
        ]);

        $nasabahB = Nasabah::create([
            'nama' => 'Nasabah B',
            'nik' => '3201012345670004',
            'alamat' => 'Jl. Melati',
            'no_telp' => '6281300000004',
            'password' => 'secret123',
        ]);

        $sampah = JenisSampah::create([
            'nama' => 'Botol Kaca',
            'kode' => 'KCA-01',
            'kategori' => 'Kaca',
        ]);

        $setoranB = Setoran::create([
            'nasabah_id' => $nasabahB->id,
            'petugas_id' => $this->pengelola->id,
            'jenis_sampah_id' => $sampah->id,
            'kondisi' => 'sudah_dipilah',
            'berat' => 5.0,
            'harga_per_kg' => 1000,
            'total_nilai' => 5000,
            'tanggal' => now(),
        ]);

        $notaB = Nota::create([
            'setoran_id' => $setoranB->id,
            'nomor' => 'NOTA-B-001',
            'tanggal' => now(),
        ]);

        // Nasabah A mencoba membuka nota milik Nasabah B -> 403 Forbidden
        $this->actingAs($nasabahA, 'nasabah')
            ->get(route('nasabah.nota', $notaB->id))
            ->assertStatus(403);

        // Nasabah B membuka notanya sendiri -> 200 OK
        $this->actingAs($nasabahB, 'nasabah')
            ->get(route('nasabah.nota', $notaB->id))
            ->assertStatus(200)
            ->assertSee('NOTA-B-001');

        // Nasabah A melihat daftar riwayatnya sendiri -> tidak memuat nota B
        $this->actingAs($nasabahA, 'nasabah')
            ->get(route('nasabah.riwayat'))
            ->assertStatus(200)
            ->assertDontSee('NOTA-B-001');
    }

    /**
     * 4. Pencarian nama nasabah dengan wildcard LIKE di-escape dengan benar.
     */
    public function test_pencarian_nama_nasabah_dengan_escape_wildcard_like(): void
    {
        Nasabah::create([
            'nama' => 'Budi % Spesial',
            'nik' => '3201012345670005',
            'alamat' => 'Alamat 1',
            'no_telp' => '6281400000005',
            'password' => 'secret123',
        ]);

        Nasabah::create([
            'nama' => 'Budi _ Garis',
            'nik' => '3201012345670006',
            'alamat' => 'Alamat 2',
            'no_telp' => '6281400000006',
            'password' => 'secret123',
        ]);

        Nasabah::create([
            'nama' => 'Budi Biasa',
            'nik' => '3201012345670007',
            'alamat' => 'Alamat 3',
            'no_telp' => '6281400000007',
            'password' => 'secret123',
        ]);

        // Cari 'Budi %': hanya menemukan 'Budi % Spesial'
        $responsePercent = $this->actingAs($this->pengelola, 'petugas')
            ->get(route('pengelola.nasabah', ['cari' => 'Budi %']));

        $responsePercent->assertStatus(200);
        $responsePercent->assertSee('Budi % Spesial');
        $responsePercent->assertDontSee('Budi Biasa');
        $responsePercent->assertDontSee('Budi _ Garis');

        // Cari 'Budi _': hanya menemukan 'Budi _ Garis'
        $responseUnderscore = $this->actingAs($this->pengelola, 'petugas')
            ->get(route('pengelola.nasabah', ['cari' => 'Budi _']));

        $responseUnderscore->assertStatus(200);
        $responseUnderscore->assertSee('Budi _ Garis');
        $responseUnderscore->assertDontSee('Budi Biasa');
        $responseUnderscore->assertDontSee('Budi % Spesial');
    }

    /**
     * 5. Hapus nasabah yang memiliki setoran atau mutasi saldo ditolak.
     */
    public function test_hapus_nasabah_bertransaksi_ditolak(): void
    {
        $nasabah = Nasabah::create([
            'nama' => 'Nasabah Transaksi',
            'nik' => '3201012345670008',
            'alamat' => 'Jl. Dahlia',
            'no_telp' => '6281500000008',
            'password' => 'secret123',
        ]);

        $sampah = JenisSampah::create([
            'nama' => 'Kaleng Minuman',
            'kode' => 'KLG-01',
            'kategori' => 'Logam',
        ]);

        $setoran = Setoran::create([
            'nasabah_id' => $nasabah->id,
            'petugas_id' => $this->pengelola->id,
            'jenis_sampah_id' => $sampah->id,
            'kondisi' => 'sudah_dipilah',
            'berat' => 2.0,
            'harga_per_kg' => 12000,
            'total_nilai' => 24000,
            'tanggal' => now(),
        ]);

        // Coba hapus nasabah yang punya setoran
        $response = $this->actingAs($this->pengelola, 'petugas')
            ->delete(route('pengelola.nasabah.destroy', $nasabah->id));

        $response->assertRedirect(route('pengelola.nasabah'));
        $response->assertSessionHas('error');

        // Nasabah tetap ada di database
        $this->assertDatabaseHas('nasabah', ['id' => $nasabah->id]);

        // Nasabah baru tanpa transaksi bisa dihapus
        $nasabahPolos = Nasabah::create([
            'nama' => 'Nasabah Kosong',
            'nik' => '3201012345670009',
            'alamat' => 'Jl. Cempaka',
            'no_telp' => '6281500000009',
            'password' => 'secret123',
        ]);

        $responseHapus = $this->actingAs($this->pengelola, 'petugas')
            ->delete(route('pengelola.nasabah.destroy', $nasabahPolos->id));

        $responseHapus->assertRedirect(route('pengelola.nasabah'));
        $responseHapus->assertSessionHas('success');
        $this->assertDatabaseMissing('nasabah', ['id' => $nasabahPolos->id]);
    }

    /**
     * 6. Saldo tidak bisa diubah lewat form edit nasabah.
     */
    public function test_saldo_tidak_bisa_diubah_lewat_form_edit_nasabah(): void
    {
        $nasabah = Nasabah::create([
            'nama' => 'Nasabah Saldo Aman',
            'nik' => '3201012345670010',
            'alamat' => 'Jl. Flamboyan',
            'no_telp' => '6281600000010',
            'password' => 'secret123',
        ]);
        $nasabah->forceFill(['saldo' => 50000])->save();

        // Kirim perubahan form dengan payload saldo palsu 999.999.999
        $payload = [
            'nama' => 'Nasabah Saldo Aman Terupdate',
            'alamat' => 'Jl. Flamboyan Baru No. 10',
            'no_telp' => '081600000010',
            'status' => 'aktif',
            'saldo' => '999999999', // Usaha manipulasi saldo
        ];

        $response = $this->actingAs($this->pengelola, 'petugas')
            ->put(route('pengelola.nasabah.update', $nasabah->id), $payload);

        $response->assertRedirect(route('pengelola.nasabah'));
        $response->assertSessionHas('success');

        $fresh = $nasabah->fresh();
        $this->assertSame('Nasabah Saldo Aman Terupdate', $fresh->nama);
        $this->assertSame('Jl. Flamboyan Baru No. 10', $fresh->alamat);
        // Saldo tetap 50.000, tidak berubah
        $this->assertSame(50000, $fresh->saldo);
    }
}
