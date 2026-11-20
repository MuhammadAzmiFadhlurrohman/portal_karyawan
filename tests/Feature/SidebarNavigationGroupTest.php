<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarNavigationGroupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_kpi_through_audit_log_belong_to_lainnya_not_pekerjaan_tiketing(): void
    {
        $admin = User::where('email', 'admin@msn.co.id')->firstOrFail();

        $response = $this->actingAs($admin)->get(route('portal.hub'));
        $response->assertOk();

        $html = $response->getContent();

        $tiketingStart = strpos($html, 'id="sb-menu-tiketing"');
        $this->assertNotFalse($tiketingStart);

        $lainnyaHeaderPos = strpos($html, 'LAINNYA', $tiketingStart);
        $this->assertNotFalse($lainnyaHeaderPos);

        $lainnyaMenuPos = strpos($html, 'id="sb-menu-lainnya"', $lainnyaHeaderPos);
        $this->assertNotFalse($lainnyaMenuPos);

        $navEndPos = strpos($html, '</nav>', $lainnyaMenuPos);
        $this->assertNotFalse($navEndPos);

        $tiketingSection = substr($html, $tiketingStart, $lainnyaHeaderPos - $tiketingStart);
        $lainnyaSection = substr($html, $lainnyaMenuPos, $navEndPos - $lainnyaMenuPos);

        // PEKERJAAN & TIKETING only retains Tiket Gangguan and OLT, PON & ODP
        $this->assertStringContainsString('Tiket Gangguan', $tiketingSection);
        $this->assertStringContainsString('OLT, PON &amp; ODP', str_replace('OLT, PON & ODP', 'OLT, PON &amp; ODP', $tiketingSection));
        $this->assertStringNotContainsString('Laporan KPI', $tiketingSection);
        $this->assertStringNotContainsString('Perangkat Saya', $tiketingSection);
        $this->assertStringNotContainsString('Data Karyawan', $tiketingSection);
        $this->assertStringNotContainsString('Pengaturan', $tiketingSection);
        $this->assertStringNotContainsString('Audit Log', $tiketingSection);

        // LAINNYA holds Laporan KPI, Perangkat Saya, Data Karyawan, Pengaturan, Audit Log
        $this->assertStringContainsString('LAINNYA', substr($html, $lainnyaHeaderPos, 20));
        $this->assertStringContainsString('Laporan KPI', $lainnyaSection);
        $this->assertStringContainsString('Perangkat Saya', $lainnyaSection);
        $this->assertStringContainsString('Data Karyawan', $lainnyaSection);
        $this->assertStringContainsString('Pengaturan', $lainnyaSection);
        $this->assertStringContainsString('Audit Log', $lainnyaSection);
    }
}
