<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonalKpiCardsVisualTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_dashboard_renders_donut_charts_and_stacked_progress_bar_for_personal_kpi(): void
    {
        $user = User::where('username', 'teknisi1')->firstOrFail();

        $response = $this->actingAs($user)->get(route('portal.hub'));

        $response->assertStatus(200);

        // Card 1: Pengajuan Saya
        $response->assertSee('Pengajuan Saya');
        $response->assertSee('kpi-donut-ring');
        $response->assertSee('Nominal Disetujui');
        $response->assertSee('Dana &amp; Pengadaan Barang', false);

        // Card 2: Lembur Saya
        $response->assertSee('Lembur Saya');
        $response->assertSee('Durasi Terverifikasi');
        $response->assertSee('Surat Perintah Lembur');

        // Card 3: Absensi Saya (Stacked Progress Bar)
        $response->assertSee('Absensi Saya');
        $response->assertSee('Total Hadir');
        $response->assertSee('Total Potongan');
        $response->assertSee('Tepat Waktu:');
        $response->assertSee('Tidak Hadir:');
        $response->assertSee('Akumulasi Potongan');
        $response->assertSee('Denda &amp; Koreksi Absen', false);

        // Legends
        $response->assertSee('Disetujui');
        $response->assertSee('Ditolak');
        $response->assertSee('Menunggu');
    }
}
