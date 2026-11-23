<?php

namespace Tests\Feature;

use App\Models\MonthlySchedule;
use App\Models\NotificationLog;
use App\Models\Shift;
use App\Models\ShiftSwap;
use App\Models\User;
use App\Modules\Attendance\Services\ShiftSwapService;
use App\Services\FcmService;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ShiftSwapNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $karyawan;
    protected User $rekan;
    protected User $admin;
    protected Shift $shiftPagi;
    protected Shift $shiftLibur;
    protected ShiftSwapService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        Http::fake([
            'https://fcm.googleapis.com/*' => Http::response(['name' => 'projects/fake/messages/123'], 200),
        ]);

        $this->admin = User::where('email', 'admin@msn.co.id')->firstOrFail();
        $this->karyawan = User::where('email', 'agus.teknisi@msn.co.id')->firstOrFail();
        $this->rekan = User::where('email', 'dedi.teknis@msn.co.id')->firstOrFail();

        $this->shiftPagi = Shift::where('code', 'P')->firstOrFail();
        $this->shiftLibur = Shift::where('code', 'L')->firstOrFail();

        $this->service = app(ShiftSwapService::class);
    }

    public function test_shift_swap_workflow_dispatches_notifications_at_every_step(): void
    {
        $d1 = Carbon::now('Asia/Jakarta')->addDays(3)->toDateString();
        $d2 = Carbon::now('Asia/Jakarta')->addDays(4)->toDateString();

        MonthlySchedule::updateOrCreate(['user_id' => $this->karyawan->id, 'date' => $d1], ['shift_id' => $this->shiftPagi->id]);
        MonthlySchedule::updateOrCreate(['user_id' => $this->rekan->id, 'date' => $d1], ['shift_id' => $this->shiftLibur->id]);
        MonthlySchedule::updateOrCreate(['user_id' => $this->karyawan->id, 'date' => $d2], ['shift_id' => $this->shiftLibur->id]);
        MonthlySchedule::updateOrCreate(['user_id' => $this->rekan->id, 'date' => $d2], ['shift_id' => $this->shiftPagi->id]);

        // Step 1: Karyawan creates swap request -> Rekan (target) receives notification
        $swap = $this->service->createRequest($this->karyawan, [
            'target_user_id' => $this->rekan->id,
            'requester_date' => $d1,
            'target_date' => $d2,
            'is_backup' => false,
            'reason' => 'Ada acara mendadak',
        ]);

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $this->rekan->id,
            'event_type' => 'shift_swap_requested',
        ]);

        // Step 2: Rekan (target) approves -> Karyawan (requester) receives notification
        $this->service->approveByTarget($swap, $this->rekan);

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $this->karyawan->id,
            'event_type' => 'shift_swap_target_approved',
        ]);

        // Step 3: Admin / Supervisor approves -> Both Karyawan & Rekan receive notification
        $this->service->approveBySupervisor($swap, $this->admin);

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $this->karyawan->id,
            'event_type' => 'shift_swap_approved',
        ]);

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $this->rekan->id,
            'event_type' => 'shift_swap_approved',
        ]);
    }

    public function test_shift_swap_rejection_by_target_notifies_requester(): void
    {
        $d1 = Carbon::now('Asia/Jakarta')->addDays(5)->toDateString();
        $d2 = Carbon::now('Asia/Jakarta')->addDays(6)->toDateString();

        MonthlySchedule::updateOrCreate(['user_id' => $this->karyawan->id, 'date' => $d1], ['shift_id' => $this->shiftPagi->id]);
        MonthlySchedule::updateOrCreate(['user_id' => $this->rekan->id, 'date' => $d1], ['shift_id' => $this->shiftLibur->id]);
        MonthlySchedule::updateOrCreate(['user_id' => $this->karyawan->id, 'date' => $d2], ['shift_id' => $this->shiftLibur->id]);
        MonthlySchedule::updateOrCreate(['user_id' => $this->rekan->id, 'date' => $d2], ['shift_id' => $this->shiftPagi->id]);

        $swap = $this->service->createRequest($this->karyawan, [
            'target_user_id' => $this->rekan->id,
            'requester_date' => $d1,
            'target_date' => $d2,
            'is_backup' => false,
            'reason' => 'Perlu tukar jadwal',
        ]);

        // Rekan rejects
        $this->service->reject($swap, $this->rekan, 'Maaf saya tidak bisa di hari tersebut.');

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $this->karyawan->id,
            'event_type' => 'shift_swap_rejected',
        ]);
    }

    public function test_shift_swap_rejection_by_supervisor_notifies_both(): void
    {
        $d1 = Carbon::now('Asia/Jakarta')->addDays(7)->toDateString();
        $d2 = Carbon::now('Asia/Jakarta')->addDays(8)->toDateString();

        MonthlySchedule::updateOrCreate(['user_id' => $this->karyawan->id, 'date' => $d1], ['shift_id' => $this->shiftPagi->id]);
        MonthlySchedule::updateOrCreate(['user_id' => $this->rekan->id, 'date' => $d1], ['shift_id' => $this->shiftLibur->id]);
        MonthlySchedule::updateOrCreate(['user_id' => $this->karyawan->id, 'date' => $d2], ['shift_id' => $this->shiftLibur->id]);
        MonthlySchedule::updateOrCreate(['user_id' => $this->rekan->id, 'date' => $d2], ['shift_id' => $this->shiftPagi->id]);

        $swap = $this->service->createRequest($this->karyawan, [
            'target_user_id' => $this->rekan->id,
            'requester_date' => $d1,
            'target_date' => $d2,
            'is_backup' => false,
            'reason' => 'Perlu tukar jadwal',
        ]);

        $this->service->approveByTarget($swap, $this->rekan);

        // Supervisor rejects
        $this->service->reject($swap, $this->admin, 'Kuota shift hari tersebut tidak mencukupi.');

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $this->karyawan->id,
            'event_type' => 'shift_swap_rejected',
        ]);

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $this->rekan->id,
            'event_type' => 'shift_swap_rejected',
        ]);
    }
}
