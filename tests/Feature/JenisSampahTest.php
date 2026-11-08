<?php

namespace Tests\Feature;

use App\Models\HargaSampah;
use App\Models\JenisSampah;
use App\Models\Petugas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JenisSampahTest extends TestCase
{
    use RefreshDatabase;

    protected Petugas $pengelola;

    protected Petugas $bendahara;

    protected Petugas $ketua;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pengelola = new Petugas([
            'nama' => 'Pengelola Test',
            'no_telp' => '6281111111111',
            'password' => 'secret123',
        ]);
        $this->pengelola->forceFill(['jabatan' => 'pengelola'])->save();

        $this->bendahara = new Petugas([
            'nama' => 'Bendahara Test',
            'no_telp' => '6282222222222',
            'password' => 'secret123',
        ]);
        $this->bendahara->forceFill(['jabatan' => 'bendahara'])->save();

        $this->ketua = new Petugas([
            'nama' => 'Ketua Test',
            'no_telp' => '6283333333333',
            'password' => 'secret123',
        ]);
        $this->ketua->forceFill(['jabatan' => 'ketua'])->save();
    }

    public function test_akses_detail_sampah_hanya_untuk_pengelola(): void
    {
        // Tamu tanpa login diarahkan ke login
        $this->get(route('pengelola.detail-sampah'))
            ->assertRedirect(route('login.show', 'petugas'));

        // Bendahara: 403 Forbidden
        $this->actingAs($this->bendahara, 'petugas')
            ->get(route('pengelola.detail-sampah'))
            ->assertStatus(403);

        // Ketua: 403 Forbidden
        $this->actingAs($this->ketua, 'petugas')
            ->get(route('pengelola.detail-sampah'))
            ->assertStatus(403);

        // Pengelola: 200 OK
        $this->actingAs($this->pengelola, 'petugas')
            ->get(route('pengelola.detail-sampah'))
            ->assertStatus(200);
    }

    public function test_tambah_produk_tanpa_gambar_berhasil(): void
    {
        $payload = [
            'nama' => 'Kardus Box',
            'kode' => 'KRD-01',
            'kategori' => 'Kertas',
            'deskripsi' => 'Kardus cokelat tebal dan bersih',
            'harga_sudah_dipilah' => '3.000',
            'harga_belum_dipilah' => '2.500',
            'harga_campuran' => '2.000',
        ];

        $response = $this->actingAs($this->pengelola, 'petugas')
            ->post(route('pengelola.detail-sampah.store'), $payload);

        $response->assertRedirect(route('pengelola.detail-sampah'));
        $response->assertSessionHas('success', 'Produk Berhasil Ditambahkan');

        $this->assertDatabaseHas('jenis_sampah', [
            'nama' => 'Kardus Box',
            'kode' => 'KRD-01',
            'kategori' => 'Kertas',
            'gambar' => null,
        ]);

        $jenisSampah = JenisSampah::where('kode', 'KRD-01')->firstOrFail();

        $this->assertDatabaseHas('harga_sampah', [
            'jenis_sampah_id' => $jenisSampah->id,
            'kondisi' => 'sudah_dipilah',
            'harga_per_kg' => 3000,
            'tanggal_berakhir' => null,
        ]);

        $this->assertDatabaseHas('harga_sampah', [
            'jenis_sampah_id' => $jenisSampah->id,
            'kondisi' => 'belum_dipilah',
            'harga_per_kg' => 2500,
            'tanggal_berakhir' => null,
        ]);

        $this->assertDatabaseHas('harga_sampah', [
            'jenis_sampah_id' => $jenisSampah->id,
            'kondisi' => 'campuran',
            'harga_per_kg' => 2000,
            'tanggal_berakhir' => null,
        ]);
    }

    public function test_tambah_produk_dengan_gambar_berhasil(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('botol.png', 200, 200)->size(500);

        $payload = [
            'nama' => 'Botol Bening',
            'kode' => 'BTL-01',
            'kategori' => 'Plastik',
            'deskripsi' => 'Botol air mineral bersih',
            'harga_sudah_dipilah' => '4000',
            'harga_belum_dipilah' => '3000',
            'harga_campuran' => '2000',
            'gambar' => $file,
        ];

        $response = $this->actingAs($this->pengelola, 'petugas')
            ->post(route('pengelola.detail-sampah.store'), $payload);

        $response->assertRedirect(route('pengelola.detail-sampah'));

        $jenisSampah = JenisSampah::where('kode', 'BTL-01')->firstOrFail();
        $this->assertNotNull($jenisSampah->gambar);
        Storage::disk('public')->assertExists($jenisSampah->gambar);
    }

    public function test_tambah_produk_kode_ganda_ditolak(): void
    {
        JenisSampah::create([
            'nama' => 'Koran Lama',
            'kode' => 'KRN-01',
            'kategori' => 'Kertas',
        ]);

        $payload = [
            'nama' => 'Koran Bekas Baru',
            'kode' => 'KRN-01', // Kode ganda
            'kategori' => 'Kertas',
            'harga_sudah_dipilah' => '1500',
            'harga_belum_dipilah' => '1200',
            'harga_campuran' => '1000',
        ];

        $response = $this->actingAs($this->pengelola, 'petugas')
            ->from(route('pengelola.detail-sampah.tambah'))
            ->post(route('pengelola.detail-sampah.store'), $payload);

        $response->assertRedirect(route('pengelola.detail-sampah.tambah'));
        $response->assertSessionHasErrors(['kode']);
    }

    public function test_validasi_harga_harus_bilangan_bulat_positif(): void
    {
        $payload = [
            'nama' => 'Besi Tua',
            'kode' => 'BSI-01',
            'kategori' => 'Logam',
            'harga_sudah_dipilah' => '0', // 0 tidak valid
            'harga_belum_dipilah' => '-500', // negatif tidak valid
            'harga_campuran' => 'bukan_angka', // bukan integer
        ];

        $response = $this->actingAs($this->pengelola, 'petugas')
            ->post(route('pengelola.detail-sampah.store'), $payload);

        $response->assertSessionHasErrors([
            'harga_sudah_dipilah',
            'harga_belum_dipilah',
            'harga_campuran',
        ]);
    }

    public function test_ubah_harga_membuat_riwayat_baru_dan_menutup_baris_lama(): void
    {
        $jenisSampah = JenisSampah::create([
            'nama' => 'Aluminium',
            'kode' => 'ALM-01',
            'kategori' => 'Logam',
        ]);

        $hargaSudahLama = HargaSampah::create([
            'jenis_sampah_id' => $jenisSampah->id,
            'kondisi' => 'sudah_dipilah',
            'harga_per_kg' => 10000,
            'tanggal_mulai' => '2026-01-01',
            'tanggal_berakhir' => null,
        ]);

        $hargaBelumLama = HargaSampah::create([
            'jenis_sampah_id' => $jenisSampah->id,
            'kondisi' => 'belum_dipilah',
            'harga_per_kg' => 8000,
            'tanggal_mulai' => '2026-01-01',
            'tanggal_berakhir' => null,
        ]);

        $hargaCampurLama = HargaSampah::create([
            'jenis_sampah_id' => $jenisSampah->id,
            'kondisi' => 'campuran',
            'harga_per_kg' => 6000,
            'tanggal_mulai' => '2026-01-01',
            'tanggal_berakhir' => null,
        ]);

        // Ubah hanya harga sudah_dipilah menjadi 12.000, harga lain tetap sama
        $payload = [
            'nama' => 'Aluminium Super',
            'kode' => 'ALM-01',
            'kategori' => 'Logam Ringan',
            'deskripsi' => 'Aluminium murni tanpa karat',
            'harga_sudah_dipilah' => '12.000',
            'harga_belum_dipilah' => '8.000',
            'harga_campuran' => '6.000',
        ];

        $response = $this->actingAs($this->pengelola, 'petugas')
            ->put(route('pengelola.detail-sampah.update', $jenisSampah->id), $payload);

        $response->assertRedirect(route('pengelola.detail-sampah'));
        $response->assertSessionHas('success', 'Produk Berhasil di ubah!');

        // Baris lama untuk sudah_dipilah harus ditutup tanggal_berakhir-nya
        $this->assertNotNull($hargaSudahLama->fresh()->tanggal_berakhir);

        // Baris baru dibuat untuk sudah_dipilah
        $this->assertDatabaseHas('harga_sampah', [
            'jenis_sampah_id' => $jenisSampah->id,
            'kondisi' => 'sudah_dipilah',
            'harga_per_kg' => 12000,
            'tanggal_berakhir' => null,
        ]);

        // Harga belum_dipilah dan campuran yang tidak berubah tetap terbuka dan tidak menduplikasi baris
        $this->assertNull($hargaBelumLama->fresh()->tanggal_berakhir);
        $this->assertNull($hargaCampurLama->fresh()->tanggal_berakhir);
        $this->assertSame(1, HargaSampah::where('jenis_sampah_id', $jenisSampah->id)->where('kondisi', 'belum_dipilah')->count());
        $this->assertSame(1, HargaSampah::where('jenis_sampah_id', $jenisSampah->id)->where('kondisi', 'campuran')->count());
        $this->assertSame(2, HargaSampah::where('jenis_sampah_id', $jenisSampah->id)->where('kondisi', 'sudah_dipilah')->count());
    }

    public function test_ubah_gambar_menghapus_gambar_lama(): void
    {
        Storage::fake('public');

        $oldFile = UploadedFile::fake()->image('gambar_lama.jpg')->size(300);
        $oldPath = $oldFile->store('sampah', 'public');

        $jenisSampah = JenisSampah::create([
            'nama' => 'Plastik PET',
            'kode' => 'PET-01',
            'kategori' => 'Plastik',
            'gambar' => $oldPath,
        ]);

        Storage::disk('public')->assertExists($oldPath);

        $newFile = UploadedFile::fake()->image('gambar_baru.png')->size(400);

        $payload = [
            'nama' => 'Plastik PET Bening',
            'kode' => 'PET-01',
            'kategori' => 'Plastik',
            'harga_sudah_dipilah' => '3000',
            'harga_belum_dipilah' => '2500',
            'harga_campuran' => '2000',
            'gambar' => $newFile,
        ];

        $response = $this->actingAs($this->pengelola, 'petugas')
            ->put(route('pengelola.detail-sampah.update', $jenisSampah->id), $payload);

        $response->assertRedirect(route('pengelola.detail-sampah'));

        // Gambar lama terhapus dari storage
        Storage::disk('public')->assertMissing($oldPath);

        // Gambar baru tersimpan
        $fresh = $jenisSampah->fresh();
        $this->assertNotNull($fresh->gambar);
        $this->assertNotSame($oldPath, $fresh->gambar);
        Storage::disk('public')->assertExists($fresh->gambar);
    }

    /**
     * Helper untuk mengekstrak elemen form dari HTML yang dirender.
     *
     * @return array<string, array{tag: string, type: string, required: bool, value: string}>
     */
    protected function extractFormInputs(string $html, string $formActionSubstring = ''): array
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);

        $forms = $xpath->query('//form');
        $targetForm = null;
        foreach ($forms as $form) {
            $action = $form->getAttribute('action');
            if ($formActionSubstring === '' || str_contains($action, $formActionSubstring)) {
                $targetForm = $form;
                break;
            }
        }

        $inputs = [];
        if ($targetForm) {
            $elements = $xpath->query('.//input | .//select | .//textarea', $targetForm);
            foreach ($elements as $el) {
                $name = $el->getAttribute('name');
                if (! $name) {
                    continue;
                }
                $inputs[$name] = [
                    'tag' => $el->tagName,
                    'type' => $el->getAttribute('type') ?: ($el->tagName === 'textarea' ? 'textarea' : 'text'),
                    'required' => $el->hasAttribute('required'),
                    'value' => $el->getAttribute('value'),
                ];
            }
        }

        return $inputs;
    }

    /**
     * 1. REPRODUKSI: Form-driven HTTP test isi semua kolom wajib.
     */
    public function test_reproduksi_form_driven_tambah_produk_semua_kolom_wajib_terisi(): void
    {
        // 1. Render GET halaman tambah produk
        $getResponse = $this->actingAs($this->pengelola, 'petugas')
            ->get(route('pengelola.detail-sampah.tambah'));

        $getResponse->assertStatus(200);

        // 2. Ekstrak semua input dari HTML
        $formInputs = $this->extractFormInputs($getResponse->getContent(), 'detail-sampah');
        $this->assertArrayHasKey('nama', $formInputs);
        $this->assertArrayHasKey('kode', $formInputs);
        $this->assertArrayHasKey('kategori', $formInputs);
        $this->assertArrayHasKey('deskripsi', $formInputs);
        $this->assertArrayHasKey('harga_sudah_dipilah', $formInputs);
        $this->assertArrayHasKey('harga_belum_dipilah', $formInputs);
        $this->assertArrayHasKey('harga_campuran', $formInputs);
        $this->assertArrayHasKey('gambar', $formInputs);

        $this->assertTrue($formInputs['nama']['required']);
        $this->assertTrue($formInputs['kode']['required']);
        $this->assertTrue($formInputs['kategori']['required']);
        $this->assertTrue($formInputs['harga_sudah_dipilah']['required']);
        $this->assertTrue($formInputs['harga_belum_dipilah']['required']);
        $this->assertTrue($formInputs['harga_campuran']['required']);
        $this->assertFalse($formInputs['deskripsi']['required']);

        // 3. Isi seperti pengguna (semua kolom wajib terisi)
        $payload = [
            'nama' => 'Kardus Gelombang Super',
            'kode' => 'KRD-SUP-01',
            'kategori' => 'Kertas',
            'deskripsi' => 'Kardus gelombang cokelat tebal dan bersih',
            'harga_sudah_dipilah' => '3.500',
            'harga_belum_dipilah' => '2.500',
            'harga_campuran' => '1.500',
        ];

        // 4. Kirim
        $postResponse = $this->actingAs($this->pengelola, 'petugas')
            ->post(route('pengelola.detail-sampah.store'), $payload);

        // 5. Hasil
        $postResponse->assertRedirect(route('pengelola.detail-sampah'));
        $this->assertDatabaseHas('jenis_sampah', [
            'kode' => 'KRD-SUP-01',
            'nama' => 'Kardus Gelombang Super',
        ]);
    }

    /**
     * 1. REPRODUKSI: Form-driven HTTP test dengan satu kolom wajib kosong.
     */
    public function test_reproduksi_form_driven_tambah_produk_satu_kolom_wajib_kosong(): void
    {
        // 1. Render GET halaman tambah produk
        $getResponse = $this->actingAs($this->pengelola, 'petugas')
            ->get(route('pengelola.detail-sampah.tambah'));

        $getResponse->assertStatus(200);

        // 2. Ekstrak input
        $formInputs = $this->extractFormInputs($getResponse->getContent(), 'detail-sampah');
        $this->assertTrue($formInputs['nama']['required']);

        // 3. Kolom wajib 'nama' sengaja dikosongkan
        $payload = [
            'nama' => '',
            'kode' => 'KRD-EMPTY-01',
            'kategori' => 'Kertas',
            'deskripsi' => 'Kardus tanpa nama',
            'harga_sudah_dipilah' => '3.000',
            'harga_belum_dipilah' => '2.000',
            'harga_campuran' => '1.000',
        ];

        // 4. Kirim
        $postResponse = $this->actingAs($this->pengelola, 'petugas')
            ->from(route('pengelola.detail-sampah.tambah'))
            ->post(route('pengelola.detail-sampah.store'), $payload);

        // 5. Hasil: redirect kembali dengan error validasi
        $postResponse->assertRedirect(route('pengelola.detail-sampah.tambah'));
        $postResponse->assertSessionHasErrors(['nama']);
        $this->assertDatabaseMissing('jenis_sampah', [
            'kode' => 'KRD-EMPTY-01',
        ]);
    }

    /**
     * 5. TES: Form-driven tambah produk berhasil simpan dan redirect dengan flash.
     */
    public function test_form_driven_tambah_produk_berhasil_tersimpan_dan_redirect(): void
    {
        $getResponse = $this->actingAs($this->pengelola, 'petugas')
            ->get(route('pengelola.detail-sampah.tambah'));

        $getResponse->assertStatus(200);
        $this->assertStringContainsString('novalidate', $getResponse->getContent());

        $payload = [
            'nama' => 'Koran Bekas Koran',
            'kode' => 'KRN-FD-01',
            'kategori' => 'Kertas',
            'deskripsi' => 'Koran bekas cetakan',
            'harga_sudah_dipilah' => '2.500',
            'harga_belum_dipilah' => '1.800',
            'harga_campuran' => '1.200',
        ];

        $postResponse = $this->actingAs($this->pengelola, 'petugas')
            ->post(route('pengelola.detail-sampah.store'), $payload);

        $postResponse->assertRedirect(route('pengelola.detail-sampah'));
        $postResponse->assertSessionHas('success', 'Produk Berhasil Ditambahkan');

        // Mengikuti redirect dan memastikan flash tampil di halaman daftar
        $listResponse = $this->actingAs($this->pengelola, 'petugas')
            ->get(route('pengelola.detail-sampah'));
        $listResponse->assertStatus(200);
        $listResponse->assertSee('Produk Berhasil Ditambahkan');
        $listResponse->assertSee('KRN-FD-01');
        $listResponse->assertSee('Koran Bekas Koran');
    }

    /**
     * 5. TES: Form-driven ubah produk berhasil simpan dan redirect dengan flash.
     */
    public function test_form_driven_ubah_produk_berhasil_tersimpan_dan_redirect(): void
    {
        $jenisSampah = JenisSampah::create([
            'nama' => 'Besi Pipa',
            'kode' => 'BSI-FD-01',
            'kategori' => 'Logam',
            'deskripsi' => 'Pipa besi bekas',
        ]);

        HargaSampah::create([
            'jenis_sampah_id' => $jenisSampah->id,
            'kondisi' => 'sudah_dipilah',
            'harga_per_kg' => 7000,
            'tanggal_mulai' => now()->toDateString(),
        ]);
        HargaSampah::create([
            'jenis_sampah_id' => $jenisSampah->id,
            'kondisi' => 'belum_dipilah',
            'harga_per_kg' => 5000,
            'tanggal_mulai' => now()->toDateString(),
        ]);
        HargaSampah::create([
            'jenis_sampah_id' => $jenisSampah->id,
            'kondisi' => 'campuran',
            'harga_per_kg' => 4000,
            'tanggal_mulai' => now()->toDateString(),
        ]);

        $getResponse = $this->actingAs($this->pengelola, 'petugas')
            ->get(route('pengelola.detail-sampah.ubah', $jenisSampah->id));

        $getResponse->assertStatus(200);
        $this->assertStringContainsString('novalidate', $getResponse->getContent());

        $payload = [
            'nama' => 'Besi Pipa Tebal',
            'kode' => 'BSI-FD-01',
            'kategori' => 'Logam Berat',
            'deskripsi' => 'Pipa besi tebal tanpa karat',
            'harga_sudah_dipilah' => '8.500',
            'harga_belum_dipilah' => '6.000',
            'harga_campuran' => '4.500',
        ];

        $putResponse = $this->actingAs($this->pengelola, 'petugas')
            ->put(route('pengelola.detail-sampah.update', $jenisSampah->id), $payload);

        $putResponse->assertRedirect(route('pengelola.detail-sampah'));
        $putResponse->assertSessionHas('success', 'Produk Berhasil di ubah!');

        // Flash tampil di halaman daftar
        $listResponse = $this->actingAs($this->pengelola, 'petugas')
            ->get(route('pengelola.detail-sampah'));
        $listResponse->assertStatus(200);
        $listResponse->assertSee('Produk Berhasil di ubah!');
        $listResponse->assertSee('Besi Pipa Tebal');
    }

    /**
     * 5. TES: Satu kolom kosong -> pesan bahasa Indonesia dan banner error tampil.
     */
    public function test_form_driven_satu_kolom_kosong_pesan_indonesia_dan_banner_tampil(): void
    {
        // 1. Tambah: kolom nama kosong
        $payloadTambah = [
            'nama' => '',
            'kode' => 'TST-01',
            'kategori' => 'Plastik',
            'harga_sudah_dipilah' => '3.000',
            'harga_belum_dipilah' => '2.000',
            'harga_campuran' => '1.000',
        ];

        $tambahPost = $this->actingAs($this->pengelola, 'petugas')
            ->from(route('pengelola.detail-sampah.tambah'))
            ->post(route('pengelola.detail-sampah.store'), $payloadTambah);

        $tambahPost->assertRedirect(route('pengelola.detail-sampah.tambah'));

        $tambahPage = $this->actingAs($this->pengelola, 'petugas')
            ->get(route('pengelola.detail-sampah.tambah'));

        // Banner error satu kalimat di atas form
        $tambahPage->assertSee('Periksa kembali isian Anda.');
        // Pesan berbahasa Indonesia
        $tambahPage->assertSee('wajib diisi');

        // 2. Ubah: kolom kategori kosong
        $jenisSampah = JenisSampah::create([
            'nama' => 'Gelas Plastik',
            'kode' => 'GLS-01',
            'kategori' => 'Plastik',
        ]);

        $payloadUbah = [
            'nama' => 'Gelas Plastik Bersih',
            'kode' => 'GLS-01',
            'kategori' => '', // kosong
            'harga_sudah_dipilah' => '2.000',
            'harga_belum_dipilah' => '1.500',
            'harga_campuran' => '1.000',
        ];

        $ubahPut = $this->actingAs($this->pengelola, 'petugas')
            ->from(route('pengelola.detail-sampah.ubah', $jenisSampah->id))
            ->put(route('pengelola.detail-sampah.update', $jenisSampah->id), $payloadUbah);

        $ubahPut->assertRedirect(route('pengelola.detail-sampah.ubah', $jenisSampah->id));

        $ubahPage = $this->actingAs($this->pengelola, 'petugas')
            ->get(route('pengelola.detail-sampah.ubah', $jenisSampah->id));

        $ubahPage->assertSee('Periksa kembali isian Anda.');
        $ubahPage->assertSee('wajib diisi');
    }

    /**
     * 5. TES: Harga "1500,50", "1.50", "abc", "0" ditolak.
     */
    public function test_form_driven_harga_invalid_ditolak(): void
    {
        $invalidPrices = ['1500,50', '1.50', 'abc', '0'];

        foreach ($invalidPrices as $harga) {
            $payload = [
                'nama' => 'Item Uji Harga',
                'kode' => 'HRG-' . rand(100, 999),
                'kategori' => 'Uji',
                'harga_sudah_dipilah' => $harga,
                'harga_belum_dipilah' => '2.000',
                'harga_campuran' => '1.000',
            ];

            $response = $this->actingAs($this->pengelola, 'petugas')
                ->from(route('pengelola.detail-sampah.tambah'))
                ->post(route('pengelola.detail-sampah.store'), $payload);

            $response->assertRedirect(route('pengelola.detail-sampah.tambah'));
            $response->assertSessionHasErrors(['harga_sudah_dipilah']);
        }
    }

    /**
     * 5. TES: Gambar > 2 MB ditolak.
     */
    public function test_form_driven_gambar_lebih_dari_2mb_ditolak(): void
    {
        Storage::fake('public');

        // File berukuran 2500 KB (> 2048 KB)
        $largeFile = UploadedFile::fake()->image('gambar_besar.jpg')->size(2500);

        $payload = [
            'nama' => 'Item Gambar Besar',
            'kode' => 'GBR-01',
            'kategori' => 'Plastik',
            'harga_sudah_dipilah' => '3.000',
            'harga_belum_dipilah' => '2.000',
            'harga_campuran' => '1.000',
            'gambar' => $largeFile,
        ];

        $response = $this->actingAs($this->pengelola, 'petugas')
            ->from(route('pengelola.detail-sampah.tambah'))
            ->post(route('pengelola.detail-sampah.store'), $payload);

        $response->assertRedirect(route('pengelola.detail-sampah.tambah'));
        $response->assertSessionHasErrors(['gambar']);
    }

    /**
     * 5. TES: Berkas berekstensi gambar tapi isinya bukan gambar ditolak.
     */
    public function test_form_driven_berkas_berekstensi_gambar_bukan_gambar_ditolak(): void
    {
        Storage::fake('public');

        // Berkas teks dengan nama 'palsu.png' tapi MIME 'text/plain'
        $fakeImage = UploadedFile::fake()->create('palsu.png', 100, 'text/plain');

        $payload = [
            'nama' => 'Item Gambar Palsu',
            'kode' => 'PLS-01',
            'kategori' => 'Plastik',
            'harga_sudah_dipilah' => '3.000',
            'harga_belum_dipilah' => '2.000',
            'harga_campuran' => '1.000',
            'gambar' => $fakeImage,
        ];

        $response = $this->actingAs($this->pengelola, 'petugas')
            ->from(route('pengelola.detail-sampah.tambah'))
            ->post(route('pengelola.detail-sampah.store'), $payload);

        $response->assertRedirect(route('pengelola.detail-sampah.tambah'));
        $response->assertSessionHasErrors(['gambar']);
    }

    /**
     * 5. TES KONTRAK: Gagal bila nama field di Blade berbeda dari aturan FormRequest.
     */
    public function test_kontrak_nama_field_di_blade_sesuai_aturan_form_request(): void
    {
        // 1. Uji Kontrak Form Tambah Produk vs StoreJenisSampahRequest
        $getTambah = $this->actingAs($this->pengelola, 'petugas')
            ->get(route('pengelola.detail-sampah.tambah'));
        $tambahInputs = $this->extractFormInputs($getTambah->getContent(), 'detail-sampah');

        // Kecualikan atribut internal Laravel
        unset($tambahInputs['_token'], $tambahInputs['_method']);
        $bladeTambahFieldNames = array_keys($tambahInputs);
        sort($bladeTambahFieldNames);

        $storeRequestRules = (new \App\Http\Requests\StoreJenisSampahRequest)->rules();
        $storeRuleKeys = array_keys($storeRequestRules);
        sort($storeRuleKeys);

        $this->assertEquals(
            $storeRuleKeys,
            $bladeTambahFieldNames,
            'Nama field form di Blade Tambah Produk berbeda dari aturan StoreJenisSampahRequest!'
        );

        // 2. Uji Kontrak Form Ubah Produk vs UpdateJenisSampahRequest
        $dummy = JenisSampah::create([
            'nama' => 'Uji Kontrak',
            'kode' => 'KTR-01',
            'kategori' => 'Uji',
        ]);

        $getUbah = $this->actingAs($this->pengelola, 'petugas')
            ->get(route('pengelola.detail-sampah.ubah', $dummy->id));
        $ubahInputs = $this->extractFormInputs($getUbah->getContent(), 'detail-sampah');

        unset($ubahInputs['_token'], $ubahInputs['_method']);
        $bladeUbahFieldNames = array_keys($ubahInputs);
        sort($bladeUbahFieldNames);

        $updateRequestRules = (new \App\Http\Requests\UpdateJenisSampahRequest)->rules();
        $updateRuleKeys = array_keys($updateRequestRules);
        sort($updateRuleKeys);

        $this->assertEquals(
            $updateRuleKeys,
            $bladeUbahFieldNames,
            'Nama field form di Blade Ubah Produk berbeda dari aturan UpdateJenisSampahRequest!'
        );
    }

    public function test_hapus_produk_berhasil_jika_belum_ada_setoran(): void
    {
        $dummy = JenisSampah::create([
            'nama' => 'Sampah Hapus',
            'kode' => 'HPS-01',
            'kategori' => 'Plastik',
        ]);

        $response = $this->actingAs($this->pengelola, 'petugas')
            ->delete(route('pengelola.detail-sampah.destroy', $dummy->id));

        $response->assertRedirect(route('pengelola.detail-sampah'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('jenis_sampah', ['id' => $dummy->id]);
    }

    public function test_hapus_produk_ditolak_jika_memiliki_setoran(): void
    {
        $dummy = JenisSampah::create([
            'nama' => 'Sampah Setoran',
            'kode' => 'STR-01',
            'kategori' => 'Kertas',
        ]);

        $nasabah = \App\Models\Nasabah::create([
            'nama' => 'Nasabah Uji Hapus',
            'nik' => '3201019999990001',
            'alamat' => 'Alamat Uji',
            'no_telp' => '6281999999998',
            'password' => 'secret123',
        ]);

        \App\Models\Setoran::create([
            'nasabah_id' => $nasabah->id,
            'petugas_id' => $this->pengelola->id,
            'jenis_sampah_id' => $dummy->id,
            'kondisi' => 'sudah_dipilah',
            'berat' => 1.5,
            'harga_per_kg' => 3000,
            'total_nilai' => 4500,
            'tanggal' => now(),
        ]);

        $response = $this->actingAs($this->pengelola, 'petugas')
            ->delete(route('pengelola.detail-sampah.destroy', $dummy->id));

        $response->assertRedirect(route('pengelola.detail-sampah'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('jenis_sampah', ['id' => $dummy->id]);
    }
}
