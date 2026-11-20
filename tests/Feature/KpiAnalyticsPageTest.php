<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KpiAnalyticsPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_kpi_analytics_page_renders_with_gomsn_theme_sections(): void
    {
        $user = User::where('username', 'teknisi1')->firstOrFail();

        $response = $this->actingAs($user)->get(route('gomsn.analytics.kpi'));

        $response->assertStatus(200);
        $response->assertSee('Analitik MTTR &amp; KPI Teknisi Lapangan', false);
        $response->assertSee('Peringkat KPI Teknisi Lapangan');
        $response->assertSee('Standar Minimal KPI');
        $response->assertSee('SLA 45%');
    }

    public function test_kpi_analytics_page_handles_inverted_date_range_safely(): void
    {
        $user = User::where('username', 'teknisi1')->firstOrFail();

        $response = $this->actingAs($user)->get(route('gomsn.analytics.kpi', [
            'start_date' => '2026-11-30',
            'end_date' => '2026-11-01',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('startDate', fn ($d) => $d->toDateString() === '2026-11-01');
        $response->assertViewHas('endDate', fn ($d) => $d->toDateString() === '2026-11-30');
    }
}
