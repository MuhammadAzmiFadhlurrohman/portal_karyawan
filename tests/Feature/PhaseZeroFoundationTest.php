<?php

namespace Tests\Feature;

use App\Models\ApprovalDelegation;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\SystemSettingService;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class PhaseZeroFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_user_can_login_with_any_of_four_unique_identifiers(): void
    {
        $identifiers = [
            'MSN-002',            // employee_code
            'admin',              // username
            'admin@msn.co.id',    // email
            '08112000002',        // phone
        ];

        foreach ($identifiers as $idx => $identifier) {
            $response = $this->post('/login', [
                'identifier' => $identifier,
                'password' => 'MsnPortal#2026',
                'device_fingerprint' => 'trusted-device-'.($idx % 2),
                'device_name' => 'Trusted Test Browser',
            ]);

            $response->assertRedirect(route('portal.hub'));
            $this->assertAuthenticated();

            $this->post('/logout');
            $this->assertGuest();
        }
    }

    public function test_third_device_triggers_pending_status_and_requires_otp(): void
    {
        $user = User::where('username', 'teknisi1')->firstOrFail();

        // Device 1 (auto-approved)
        $this->post('/login', [
            'identifier' => $user->username,
            'password' => 'MsnPortal#2026',
            'device_fingerprint' => 'device-fp-1',
        ])->assertRedirect(route('portal.hub'));
        $this->post('/logout');

        // Device 2 (auto-approved)
        $this->post('/login', [
            'identifier' => $user->username,
            'password' => 'MsnPortal#2026',
            'device_fingerprint' => 'device-fp-2',
        ])->assertRedirect(route('portal.hub'));
        $this->post('/logout');

        // Device 3 (exceeds max 2 devices -> pending + OTP redirect)
        $response = $this->post('/login', [
            'identifier' => $user->username,
            'password' => 'MsnPortal#2026',
            'device_fingerprint' => 'device-fp-3',
        ]);

        $response->assertRedirect(route('auth.device.otp'));
        $otpCode = session('demo_otp_code');
        $this->assertNotNull($otpCode);

        $this->post('/auth/device-otp', [
            'otp_code' => $otpCode,
        ])->assertRedirect(route('portal.hub'));
    }

    public function test_audit_log_is_strictly_append_only(): void
    {
        $log = AuditLogger::record('test.immutable', null, ['a' => 1], ['a' => 2], 'Testing immutability');

        $this->expectException(LogicException::class);
        $log->update(['action' => 'tampered']);
    }

    public function test_system_settings_respect_effective_from_date_ac09(): void
    {
        $pastDate = Carbon::parse('2026-09-15 10:00:00');
        $futureDate = Carbon::parse('2026-12-01 00:00:00');

        SystemSettingService::set(
            key: 'attendance.late_fee_per_minute',
            value: 250,
            reason: 'Tarif awal September',
            effectiveFrom: Carbon::parse('2026-09-01 00:00:00'),
            type: 'decimal'
        );

        SystemSettingService::set(
            key: 'attendance.late_fee_per_minute',
            value: 500,
            reason: 'Penyesuaian tarif Desember',
            effectiveFrom: Carbon::parse('2026-12-01 00:00:00'),
            type: 'decimal'
        );

        $this->assertSame(250.0, SystemSettingService::get('attendance.late_fee_per_minute', 0, $pastDate));
        $this->assertSame(500.0, SystemSettingService::get('attendance.late_fee_per_minute', 0, $futureDate));
    }

    public function test_supervisor_delegation_resolves_effective_approver(): void
    {
        $ceo = User::where('employee_code', 'MSN-001')->firstOrFail();
        $hrd = User::where('employee_code', 'MSN-003')->firstOrFail();
        $managerTeknis = User::where('employee_code', 'MSN-004')->firstOrFail();

        // Manager Teknis reports to CEO, and CEO delegated attendance approvals to HRD
        $this->assertSame($ceo->id, $managerTeknis->supervisor_id);
        $effectiveApprover = $managerTeknis->resolveEffectiveApprover('attendance');
        $this->assertNotNull($effectiveApprover);
        $this->assertSame($hrd->id, $effectiveApprover->id);

        // If delegation is deactivated, falls back to direct supervisor (CEO)
        ApprovalDelegation::query()->where('delegator_id', $ceo->id)->update(['is_active' => false]);
        $this->assertSame($ceo->id, $managerTeknis->fresh()->resolveEffectiveApprover('attendance')?->id);
    }

    public function test_rbac_blocks_unauthorized_access_to_system_settings(): void
    {
        $teknisi = User::where('username', 'teknisi1')->firstOrFail();
        $admin = User::where('username', 'admin')->firstOrFail();

        $this->actingAs($teknisi)
            ->get(route('portal.settings'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('portal.settings'))
            ->assertOk();
    }
}
