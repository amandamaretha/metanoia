<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginRouteTest extends TestCase
{
    public function test_halaman_login_utama_berhasil_dibuka(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee(route('login.show', config('metanoia.portals.petugas')));
        $response->assertSee(route('login.show', config('metanoia.portals.nasabah')));
    }

    public function test_halaman_login_role_petugas_berhasil_dibuka(): void
    {
        $response = $this->get(route('login.show', config('metanoia.portals.petugas')));

        $response->assertStatus(200);
        $response->assertSee(config('metanoia.portal_labels.petugas'));
    }

    public function test_halaman_login_role_nasabah_berhasil_dibuka(): void
    {
        $response = $this->get(route('login.show', config('metanoia.portals.nasabah')));

        $response->assertStatus(200);
        $response->assertSee(config('metanoia.portal_labels.nasabah'));
    }

    public function test_role_tidak_dikenal_menghasilkan_404(): void
    {
        $response = $this->get('/login/role-tak-dikenal');

        $response->assertStatus(404);
    }

    public function test_role_lama_pengelola_dan_bendahara_menghasilkan_404(): void
    {
        $this->get('/login/pengelola')->assertStatus(404);
        $this->get('/login/bendahara')->assertStatus(404);
    }

    public function test_post_login_petugas_tanpa_data_menghasilkan_error_validasi(): void
    {
        $response = $this->post(route('login.attempt', config('metanoia.portals.petugas')), []);

        $response->assertSessionHasErrors(['telepon', 'password']);
    }

    public function test_post_login_nasabah_tanpa_data_menghasilkan_error_validasi(): void
    {
        $response = $this->post(route('login.attempt', config('metanoia.portals.nasabah')), []);

        $response->assertSessionHasErrors(['telepon', 'password']);
    }
}
