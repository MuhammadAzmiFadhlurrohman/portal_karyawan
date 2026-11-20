<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaDashboardBannerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_dashboard_renders_pwa_install_banner_with_gomsn_theme(): void
    {
        $user = User::where('username', 'teknisi1')->firstOrFail();

        $response = $this->actingAs($user)->get(route('portal.hub'));

        $response->assertStatus(200);
        $response->assertSee('pwa-install-banner');
        $response->assertSee('Aplikasi Mobile PT MSN');
        $response->assertSee('Pasang aplikasi untuk akses cepat, praktis, dan notifikasi langsung di HP.');
        $response->assertSee('Pasang Aplikasi');
        $response->assertSee('manifest.webmanifest');
    }
}
