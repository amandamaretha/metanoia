<?php

namespace Tests\Feature\Api;

use App\Models\Nasabah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('login:api:nasabah:6281234567890|127.0.0.1');
        RateLimiter::clear('login:api:nasabah:6281299998888|127.0.0.1');
    }

    /**
     * 1. Register sukses mengembalikan 201 dan tidak login otomatis.
     */
    public function test_register_sukses(): void
    {
        $payload = [
            'nama' => 'Nasabah Baru API',
            'nik' => '3201010101010001',
            'no_telp' => '081299998888',
            'alamat' => 'Jl. Merdeka No. 10',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Pendaftaran nasabah berhasil.',
            ]);

        $this->assertDatabaseHas('nasabah', [
            'nik' => '3201010101010001',
            'no_telp' => '6281299998888',
            'status' => 'aktif',
            'saldo' => 0,
        ]);

        $this->assertFalse(auth('sanctum')->check());
    }

    /**
     * 2. Register gagal jika NIK duplikat.
     */
    public function test_register_validasi_gagal_nik_duplikat(): void
    {
        Nasabah::create([
            'nama' => 'Nasabah Lama',
            'nik' => '3201010101010001',
            'no_telp' => '6281200000001',
            'alamat' => 'Alamat Asli',
            'password' => Hash::make('password123'),
        ]);

        $payload = [
            'nama' => 'Nasabah Duplikat NIK',
            'nik' => '3201010101010001',
            'no_telp' => '081299990000',
            'alamat' => 'Jl. Lain No. 20',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nik']);
    }

    /**
     * 3. Register gagal jika nomor telepon duplikat.
     */
    public function test_register_validasi_gagal_no_telp_duplikat(): void
    {
        Nasabah::create([
            'nama' => 'Nasabah Lama',
            'nik' => '3201010101010001',
            'no_telp' => '6281299998888',
            'alamat' => 'Alamat Asli',
            'password' => Hash::make('password123'),
        ]);

        $payload = [
            'nama' => 'Nasabah Duplikat Telp',
            'nik' => '3201010101010002',
            'no_telp' => '081299998888', // Format lokal dinormalisasi menjadi 6281299998888
            'alamat' => 'Jl. Lain No. 20',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['no_telp']);
    }

    /**
     * 4. Register gagal jika konfirmasi password tidak cocok.
     */
    public function test_register_validasi_gagal_password_tidak_cocok(): void
    {
        $payload = [
            'nama' => 'Nasabah Uji',
            'nik' => '3201010101010003',
            'no_telp' => '081299997777',
            'alamat' => 'Jl. Beda No. 5',
            'password' => 'password123',
            'password_confirmation' => 'passwordBeda',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * 5. Login sukses mengembalikan 200 dengan token dan data nasabah TANPA nik/alamat.
     */
    public function test_login_sukses_response_tanpa_nik_dan_alamat(): void
    {
        $nasabah = Nasabah::create([
            'nama' => 'Nasabah Uji Login',
            'nik' => '3201010101019999',
            'no_telp' => '6281234567890',
            'alamat' => 'Jl. Rahasia No. 1',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->postJson('/api/login', [
            'no_telp' => '081234567890',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'token_type',
                'nasabah' => [
                    'id',
                    'nama',
                    'no_telp',
                    'saldo',
                    'status',
                ],
            ]);

        $responseData = $response->json();
        $this->assertEquals('Bearer', $responseData['token_type']);
        $this->assertEquals($nasabah->id, $responseData['nasabah']['id']);
        $this->assertEquals('Nasabah Uji Login', $responseData['nasabah']['nama']);
        $this->assertEquals('6281234567890', $responseData['nasabah']['no_telp']);

        // Pastikan nik dan alamat TIDAK ADA pada respons login
        $this->assertArrayNotHasKey('nik', $responseData['nasabah']);
        $this->assertArrayNotHasKey('alamat', $responseData['nasabah']);
    }

    /**
     * 6. Nasabah berstatus non-aktif TETAP boleh login.
     */
    public function test_login_sukses_untuk_nasabah_non_aktif(): void
    {
        $nasabah = Nasabah::create([
            'nama' => 'Nasabah Non Aktif',
            'nik' => '3201010101018888',
            'no_telp' => '6281234567890',
            'alamat' => 'Jl. Kenanga No. 8',
            'password' => Hash::make('secret123'),
        ]);
        $nasabah->status = 'tidak_aktif';
        $nasabah->save();

        $response = $this->postJson('/api/login', [
            'no_telp' => '6281234567890',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'status' => 'tidak_aktif',
            ]);
    }

    /**
     * 7. Login gagal jika nomor atau password salah.
     */
    public function test_login_gagal_kredensial_salah(): void
    {
        Nasabah::create([
            'nama' => 'Nasabah Asli',
            'nik' => '3201010101017777',
            'no_telp' => '6281234567890',
            'alamat' => 'Jl. Mawar No. 3',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->postJson('/api/login', [
            'no_telp' => '6281234567890',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['no_telp']);
    }

    /**
     * 8. Rate limiting login 5 percobaan per menit.
     */
    public function test_login_rate_limiting(): void
    {
        $phone = '6281234567890';
        $throttleKey = "login:api:nasabah:{$phone}|127.0.0.1";

        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($throttleKey, 60);
        }

        $response = $this->postJson('/api/login', [
            'no_telp' => $phone,
            'password' => 'anypassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['no_telp']);
    }

    /**
     * 9. Logout mencabut token yang dipakai saja.
     */
    public function test_logout_sukses_mencabut_token(): void
    {
        $nasabah = Nasabah::create([
            'nama' => 'Nasabah Logout Test',
            'nik' => '3201010101016666',
            'no_telp' => '6281234567890',
            'alamat' => 'Jl. Melati No. 4',
            'password' => Hash::make('secret123'),
        ]);

        $token1 = $nasabah->createToken('device-1')->plainTextToken;
        $token2 = $nasabah->createToken('device-2')->plainTextToken;

        // Logout menggunakan token1
        $response = $this->withHeader('Authorization', 'Bearer '.$token1)
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Berhasil logout.',
            ]);

        // Token 1 sudah terhapus
        $this->assertDatabaseMissing('personal_access_tokens', [
            'name' => 'device-1',
        ]);

        // Token 2 masih ada
        $this->assertDatabaseHas('personal_access_tokens', [
            'name' => 'device-2',
        ]);
    }

    /**
     * 10. Endpoint /me dengan token memuat nik dan alamat pemilik akun.
     */
    public function test_me_dengan_token_memuat_nik_dan_alamat_pemilik(): void
    {
        $nasabah = Nasabah::create([
            'nama' => 'Nasabah Pemilik Akun',
            'nik' => '3201010101015555',
            'no_telp' => '6281234567890',
            'alamat' => 'Jl. Pahlawan No. 99',
            'password' => Hash::make('secret123'),
        ]);

        $token = $nasabah->createToken('mobile')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJson([
                'id' => $nasabah->id,
                'nama' => 'Nasabah Pemilik Akun',
                'no_telp' => '6281234567890',
                'saldo' => 0,
                'status' => 'aktif',
                'nik' => '3201010101015555',
                'alamat' => 'Jl. Pahlawan No. 99',
            ]);
    }

    /**
     * 11. Endpoint /me tanpa token mengembalikan status 401 Unauthenticated.
     */
    public function test_me_tanpa_token_menghasilkan_401(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401);
    }
}
