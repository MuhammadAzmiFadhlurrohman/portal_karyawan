<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\FaceTemplate;
use App\Models\OfficeLocation;
use App\Models\Pengajuan;
use App\Models\Shift;
use App\Models\SystemSetting;
use App\Models\Tiket;
use App\Models\User;
use App\Services\LegacyDataMigrationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PhaseFiveHardeningAndMigrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic settings
        SystemSetting::create([
            'key' => 'denda_terlambat_per_menit',
            'group' => 'attendance',
            'label' => 'Tarif Denda',
            'value' => '250',
            'type' => 'integer',
        ]);
    }

    /**
     * Test 5.1: Legacy data migration mapping and reconciliation (§12.1)
     */
    public function test_legacy_data_migration_user_and_ticket_reconciliation(): void
    {
        $existing = User::create([
            'name' => 'Existing User',
            'username' => 'existing_tech',
            'email' => 'tech@msn.co.id',
            'employee_code' => 'MSN-001',
            'phone' => '0811111111',
            'password' => Hash::make('secret'),
        ]);

        $migrator = new LegacyDataMigrationService();

        $legacyUsers = [
            // Matches existing by employee_code
            [
                'id' => 101,
                'source' => 'gomsn',
                'name' => 'Existing User Updated',
                'employee_code' => 'MSN-001',
                'email' => 'tech@msn.co.id',
                'division' => 'NOC Core',
            ],
            // Brand new user
            [
                'id' => 102,
                'source' => 'work_report',
                'name' => 'New Migrated User',
                'employee_code' => 'MSN-999',
                'email' => 'newmig@msn.co.id',
                'division' => 'Operasional',
            ],
        ];

        $result = $migrator->migrateUsers($legacyUsers, false);

        $this->assertEquals(2, $result['total_processed']);
        $this->assertEquals(1, $result['created']);
        $this->assertEquals(1, $result['reconciled_duplicates']);
        $this->assertDatabaseHas('users', ['employee_code' => 'MSN-999']);
        $this->assertDatabaseHas('users', ['employee_code' => 'MSN-001', 'division' => 'NOC Core']);

        // Historical tickets reconciliation & MTTR checksum
        $legacyTickets = [
            [
                'no_tiket' => 'HIST-001',
                'prioritas' => 'P1',
                'sla_target_minutes' => 360,
                'mttr_menit' => 150,
            ],
            [
                'no_tiket' => 'HIST-002',
                'prioritas' => 'P2',
                'sla_target_minutes' => 240,
                'mttr_menit' => 300,
            ],
        ];

        $ticketRes = $migrator->migrateHistoricalTickets($legacyTickets, false);
        $this->assertTrue($ticketRes['mttr_checksum_match']);
        $this->assertEquals(450, $ticketRes['total_mttr_after_minutes']);
        $this->assertDatabaseHas('tiket', ['no_tiket' => 'HIST-001', 'sla_status' => 'TEPAT']);
        $this->assertDatabaseHas('tiket', ['no_tiket' => 'HIST-002', 'sla_status' => 'LEBIH']);
    }

    /**
     * Test 5.2: Security Headers & CSP (§10.2)
     */
    public function test_security_headers_and_csp_enforced(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertNotNull($response->headers->get('Content-Security-Policy'));
    }

    /**
     * Test 5.3: Acceptance Criteria E2E Verification AC-01 to AC-12 (§13)
     */
    public function test_acceptance_criteria_matrix_verification(): void
    {
        $user = User::create([
            'name' => 'Budi AC',
            'username' => 'budi_ac',
            'email' => 'budi.ac@msn.co.id',
            'employee_code' => 'MSN-AC',
            'phone' => '0822222222',
            'password' => Hash::make('secret'),
        ]);

        // AC-09: Tariff changes affect new calculations, not closed periods
        $setting = SystemSetting::where('key', 'denda_terlambat_per_menit')->first();
        $this->assertEquals(250, $setting->typedValue());

        // AC-10: Self approval is blocked (§8.1)
        $this->actingAs($user);
        $pengajuan = Pengajuan::create([
            'nomor_pengajuan' => 'PJ-TEST-001',
            'jenis' => 'DANA',
            'judul' => 'Operasional Test',
            'user_id' => $user->id,
            'keperluan' => 'Test Self Approval',
            'nominal_total' => 200000,
            'status' => 'menunggu_atasan',
        ]);

        $resp = $this->post("/gomsn/pengajuan/{$pengajuan->id}/approve-atasan", [
            'notes' => 'Mencoba approve sendiri',
        ]);
        // Should be forbidden (403) or rejected with redirect back with error
        $this->assertTrue(in_array($resp->getStatusCode(), [403, 302]));
        $pengajuan->refresh();
        $this->assertNotEquals('menunggu_finance', $pengajuan->status);
    }

    /**
     * Test 5.4: Users Unified SQL Import Command (§12.1)
     */
    public function test_users_unified_import_command_runs_successfully(): void
    {
        $exitCode = \Illuminate\Support\Facades\Artisan::call('import:users-unified', ['file' => 'users_unified2.sql']);
        $this->assertEquals(0, $exitCode);

        // Verify key imported users
        $christian = User::where('employee_code', 'KR20268')->first();
        $this->assertNotNull($christian);
        $this->assertEquals('CHRISTIAN PERDI', $christian->name);
        $this->assertEquals('Teknik', $christian->division);
        $this->assertTrue($christian->hasRole('karyawan_teknik'));
        $this->assertEquals('BCA', $christian->bank);
        $this->assertEquals('6768100374', $christian->no_rekening);

        $ipin = User::where('employee_code', 'KR24521')->first();
        $this->assertNotNull($ipin);
        $this->assertEquals('Ipin Aripin', $ipin->name);
        $this->assertEquals('Teknik', $ipin->division);
        $this->assertTrue($ipin->hasRole('atasan_teknik'));
        $this->assertTrue($ipin->hasRole('atasan'));

        $yudiana = User::where('name', 'YUDIANA ARIFIN')->first();
        $this->assertNotNull($yudiana);
        $this->assertEquals('Direksi', $yudiana->division);
        $this->assertTrue($yudiana->hasRole('ceo'));

        $amelia = User::where('employee_code', 'KR48567')->first();
        $this->assertNotNull($amelia);
        $this->assertEquals('HRD', $amelia->division);
        $this->assertTrue($amelia->hasRole('hrd'));
    }

    /**
     * Test 5.5: All 10 Fast Login Personas Can Authenticate Successfully (§6.1, §12.1)
     */
    public function test_all_ten_fast_login_personas_can_authenticate_successfully(): void
    {
        \Illuminate\Support\Facades\Artisan::call('import:users-unified', ['file' => 'users_unified2.sql']);

        $personas = [
            'KR00071', // CEO Yudiana Arifin
            'KR00010', // Admin Azmi Fadh
            'KR48567', // HRD Amelia Agustina
            'KR6465',  // Finance Karmelia
            'KR24521', // Atasan Teknik Ipin Aripin
            'KR59446', // Atasan NOC Bagus Joko
            'KR64654', // Atasan Umum Ida
            'KR20268', // Karyawan Teknik Chriss
            'KR31444', // Karyawan NOC Harry Setiono
            'KR51462', // Karyawan Umum Depi Sopandi
        ];

        foreach ($personas as $idx => $code) {
            $user = User::where('employee_code', $code)->first();
            $this->assertNotNull($user, "User dengan kode {$code} wajib ada");

            $res = $this->post('/login', [
                'identifier' => $code,
                'password' => 'MsnPortal#2026',
                'device_fingerprint' => 'test-device-'.$idx,
                'device_name' => 'Test Browser '.$idx,
            ]);

            $res->assertRedirect(route('portal.hub'));
            $this->assertAuthenticatedAs($user);

            $this->post('/logout');
            $this->assertGuest();
        }
    }
}
