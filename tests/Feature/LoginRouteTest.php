<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

class LoginRouteTest extends TestCase
{
    public function test_halaman_login_utama_berhasil_dibuka(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee(route('login.show', config('metanoia.portals.pengelola')));
        $response->assertSee(route('login.show', config('metanoia.portals.bendahara')));
    }

    public function test_halaman_login_role_pengelola_berhasil_dibuka(): void
    {
        $response = $this->get(route('login.show', config('metanoia.portals.pengelola')));

        $response->assertStatus(200);
        $response->assertSee(config('metanoia.portal_labels.pengelola'));
    }

    public function test_halaman_login_role_bendahara_berhasil_dibuka(): void
    {
        $response = $this->get(route('login.show', config('metanoia.portals.bendahara')));

        $response->assertStatus(200);
        $response->assertSee(config('metanoia.portal_labels.bendahara'));
    }

    public function test_role_tidak_dikenal_menghasilkan_404(): void
    {
        $response = $this->get('/login/role-tak-dikenal');

        $response->assertStatus(404);
    }

    /**
     * Pengujian sementara login pengelola dengan mock-auth sebelum migrasi penuh ke verifikasi OTP.
     */
    #[Group('mock-auth')]
    public function test_post_login_pengelola_redirect_ke_dashboard(): void
    {
        $response = $this->post(route('login.attempt', config('metanoia.portals.pengelola')));

        $response->assertRedirect(route('pengelola.dashboard'));
    }

    public function test_post_login_bendahara_validasi_field_telepon(): void
    {
        $response = $this->post(route('login.attempt', config('metanoia.portals.bendahara')), []);

        $response->assertSessionHasErrors([
            config('metanoia.phone_field'),
            'password',
        ]);
    }
}
