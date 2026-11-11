<?php

namespace Tests\Feature;

use App\Models\JointClosure;
use App\Models\MasterMaterial;
use App\Models\Tiket;
use App\Models\User;
use App\Modules\Ticketing\Services\ChronologyService;
use App\Modules\Ticketing\Services\HandoverService;
use App\Modules\Ticketing\Services\StopClockService;
use App\Modules\Ticketing\Services\TechnicalAssetService;
use App\Modules\Ticketing\Services\TicketDispatchService;
use App\Modules\Ticketing\Services\TicketSequenceService;
use App\Modules\Ticketing\Services\TicketStateMachine;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseThreeTicketingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * AC-04: Dua Helpdesk membuka tiket bersamaan -> Nomor tiket unik berurutan, tanpa duplikat.
     */
    public function test_ac04_ticket_sequences_are_unique_and_sequential(): void
    {
        $sequenceService = app(TicketSequenceService::class);
        $date = '2026-11-06';

        $num1 = $sequenceService->generate('BDG', $date);
        $num2 = $sequenceService->generate('BDG', $date);
        $num3 = $sequenceService->generate('BDG', $date);
        $brd1 = $sequenceService->generate('BRD', $date);

        $this->assertSame('BDG-20261106-001', $num1);
        $this->assertSame('BDG-20261106-002', $num2);
        $this->assertSame('BDG-20261106-003', $num3);
        $this->assertSame('BRD-20261106-001', $brd1);
    }

    /**
     * AC-05: Teknisi di luar tim mengakses tiket yang sudah di-dispatch -> Hanya baca; aksi tulis ditolak (403).
     */
    public function test_ac05_non_assigned_technician_write_is_forbidden_403(): void
    {
        $helpdesk = User::query()->where('username', 'helpdesk')->firstOrFail();
        $teknisi1 = User::query()->where('username', 'teknisi1')->firstOrFail();

        // Create second technician (teknisi2)
        $teknisi2 = User::query()->create([
            'employee_code' => 'MSN-099',
            'username' => 'teknisi2',
            'name' => 'Asep Kurnia',
            'email' => 'asep.teknisi@msn.co.id',
            'phone' => '08112000099',
            'division' => 'Operasional Jaringan',
            'position' => 'Teknisi FO',
            'is_active' => true,
            'password' => bcrypt('MsnPortal#2026'),
        ]);
        $teknisi2->assignRole('teknisi');

        // Create Backbone ticket
        $tiket = Tiket::query()->create([
            'no_tiket' => 'BDG-20261106-010',
            'kategori_tiket' => 'BACKBONE',
            'priority' => 'P2',
            'status' => 'OPEN',
            'sla_target_minutes' => 360,
            'backbone_segment' => 'BB-BDG-SRE-01',
            'status_link_impact' => 'TOTAL_DOWN',
            'wilayah' => 'Soreang',
            'tipe_penanganan' => 'Splicing',
            'tanggal_open' => Carbon::now('Asia/Jakarta'),
            'created_by' => $helpdesk->id,
        ]);

        // Dispatch to teknisi1 only
        $dispatchService = app(TicketDispatchService::class);
        $dispatchService->dispatch($tiket, $teknisi1->id, [], $helpdesk);

        // 1. teknisi1 (assigned) can add chronology
        $this->actingAs($teknisi1)
            ->post(route('tickets.chronologies.store', $tiket), [
                'category' => 'IZIN',
                'message' => 'Izin lokasi Soreang sudah diperoleh',
            ])
            ->assertRedirect();

        // 2. teknisi2 (outside team) attempting to write receives 403 Forbidden (AC-05)
        $this->actingAs($teknisi2)
            ->post(route('tickets.chronologies.store', $tiket), [
                'category' => 'OTDR',
                'message' => 'Mencoba submit tanpa izin tim',
            ])
            ->assertStatus(403);

        // 3. teknisi2 attempting to use materials receives 403 Forbidden (AC-05)
        $mat = MasterMaterial::firstOrFail();
        $this->actingAs($teknisi2)
            ->post(route('tickets.materials.use', $tiket), [
                'material_id' => $mat->id,
                'quantity' => 2,
            ])
            ->assertStatus(403);
    }

    /**
     * AC-06: Closing awal Backbone tanpa Joint Closure/Manuver -> Ditolak dengan daftar prasyarat yang kurang.
     */
    public function test_ac06_backbone_closing_awal_rejected_without_joint_closure_or_maneuver(): void
    {
        $helpdesk = User::query()->where('username', 'helpdesk')->firstOrFail();
        $teknisi = User::query()->where('username', 'teknisi1')->firstOrFail();

        $tiket = Tiket::query()->create([
            'no_tiket' => 'BDG-20261106-020',
            'kategori_tiket' => 'BACKBONE',
            'priority' => 'P2',
            'status' => 'PROSES',
            'sla_target_minutes' => 360,
            'backbone_segment' => 'BB-BDG-CBR-02',
            'status_link_impact' => 'TOTAL_DOWN',
            'wilayah' => 'Cibiru',
            'tipe_penanganan' => 'Splicing Cut',
            'problem_temuan' => 'Kabel FO 24C putus',
            'action_taken' => 'Penyambungan core',
            'has_link_up' => true,
            'tanggal_open' => Carbon::now('Asia/Jakarta')->subHours(2),
            'first_response_at' => Carbon::now('Asia/Jakarta')->subHours(2),
            'created_by' => $helpdesk->id,
        ]);

        $tiket->assignees()->attach($teknisi->id, [
            'role_in_team' => 'lead',
            'assigned_by' => $helpdesk->id,
            'assigned_at' => now(),
        ]);

        // Add chronology photo OTDR
        $chronService = app(ChronologyService::class);
        $chronService->addChronology($tiket, $teknisi, 'OTDR', 'Hasil ukur OTDR normal', photoPath: 'fake/path.jpg');

        $assetService = app(TechnicalAssetService::class);

        // 1. Without repair point and without joint closure/maneuver: rejected
        $prereqs = $assetService->checkClosingPrerequisites($tiket);
        $this->assertFalse($prereqs['passed']);
        $this->assertContains('Wajib mencatat minimal 1 Titik Perbaikan atau 1 Joint Closure.', $prereqs['missing']);
        $this->assertContains('Wajib mencatat minimal 1 Joint Closure atau 1 Manuver Core (AC-06).', $prereqs['missing']);

        // 2. Now add Joint Closure
        JointClosure::query()->create([
            'tiket_id' => $tiket->id,
            'nama_closure' => 'JC-CBR-01',
            'kapasitas_core' => 24,
        ]);

        $prereqs2 = $assetService->checkClosingPrerequisites($tiket);
        $this->assertTrue($prereqs2['passed'], 'Prerequisites should pass once Joint Closure is recorded');
    }

    /**
     * AC-07: Stop Clock > 120 menit tanpa persetujuan -> Menunggu approval Manager/Helpdesk sebelum closing awal.
     */
    public function test_ac07_stop_clock_exceeding_120_mins_blocks_closing_until_approved(): void
    {
        $helpdesk = User::query()->where('username', 'helpdesk')->firstOrFail();
        $teknisi = User::query()->where('username', 'teknisi1')->firstOrFail();

        $tiket = Tiket::query()->create([
            'no_tiket' => 'BRD-20261106-030',
            'kategori_tiket' => 'BROADBAND',
            'priority' => 'P2',
            'status' => 'PROSES',
            'sla_target_minutes' => 240,
            'customer_id' => 'CUST-8812',
            'customer_name' => 'PT Mandiri Sejahtera',
            'customer_phone' => '081234567890',
            'problem_temuan' => 'ONT Red LOS',
            'action_taken' => 'Ganti Patchcord',
            'redaman_sesudah' => -18.50,
            'tanggal_open' => Carbon::now()->subHours(4),
            'first_response_at' => Carbon::now()->subHours(4),
            'created_by' => $helpdesk->id,
        ]);

        $tiket->assignees()->attach($teknisi->id, [
            'role_in_team' => 'lead',
            'assigned_by' => $helpdesk->id,
            'assigned_at' => now(),
        ]);

        $chronService = app(ChronologyService::class);
        $chronService->addChronology($tiket, $teknisi, 'SELESAI', 'Penanganan selesai', photoPath: 'fake.jpg');

        $stopClockService = app(StopClockService::class);
        $startTime = Carbon::now()->subMinutes(180); // 3 hours ago
        $endTime = Carbon::now()->subMinutes(10);   // 170 mins duration > 120 mins!

        $sc = $stopClockService->start(
            $tiket,
            'IZIN_AKSES',
            'Menunggu security gedung buka pintu',
            'evidence.jpg',
            $teknisi,
            $startTime
        );
        $stopClockService->end($sc, $teknisi, $endTime);

        $stateMachine = app(TicketStateMachine::class);

        // Attempting closing awal must throw exception because Stop Clock (170 min) is unapproved (AC-07)
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Terdapat Stop Clock berdurasi lebih dari 120 menit yang belum disetujui');
        $stateMachine->submitClosingAwal($tiket, $teknisi);
    }

    /**
     * AC-08: Tiket ditutup dengan MTTR bersih melebihi SLA -> sla_status = LEBIH, tampil di dashboard stripe merah.
     */
    public function test_ac08_ticket_closed_with_mttr_exceeding_sla_marked_lebih_and_red_stripe(): void
    {
        $helpdesk = User::query()->where('username', 'helpdesk')->firstOrFail();
        $teknisi = User::query()->where('username', 'teknisi1')->firstOrFail();

        $openTime = Carbon::now()->subMinutes(300); // 300 mins ago
        $closeTime = Carbon::now();

        $tiket = Tiket::query()->create([
            'no_tiket' => 'BRD-20261106-040',
            'kategori_tiket' => 'BROADBAND',
            'priority' => 'P2',
            'status' => 'PENDING_VERIFIKASI',
            'sla_target_minutes' => 240, // Target SLA is 240m
            'total_stop_clock_minutes' => 20, // 300 - 20 = 280m net MTTR (> 240m)
            'customer_id' => 'CUST-5511',
            'customer_name' => 'CV Abadi Perkasa',
            'customer_phone' => '081234567891',
            'problem_temuan' => 'Kabel dropcore putus tertimpa dahan',
            'action_taken' => 'Tarik dropcore baru 80m',
            'redaman_sesudah' => -19.20,
            'closing_awal_by' => $teknisi->id,
            'closing_awal_at' => $closeTime,
            'tanggal_open' => $openTime,
            'created_by' => $helpdesk->id,
        ]);

        $stateMachine = app(TicketStateMachine::class);
        $closed = $stateMachine->verifyAndClose($tiket, $helpdesk, $closeTime);

        $this->assertSame('CLOSE', $closed->status);
        $this->assertSame(280, $closed->net_mttr_minutes);
        $this->assertSame('LEBIH', $closed->sla_status, 'SLA status must be LEBIH when MTTR exceeds target (AC-08)');
        $this->assertSame('red', $closed->stripeColor(), 'Stripe color must be red for LEBIH / overdue ticket (AC-08, §11)');
    }

    /**
     * AC-11: Layanan IMS tidak tersedia -> Tiket Broadband dapat dibuat dengan input manual, ditandai belum tervalidasi.
     */
    public function test_ac11_ims_unavailable_fallback_broadband_ticket_marked_unvalidated(): void
    {
        $helpdesk = User::query()->where('username', 'helpdesk')->firstOrFail();

        // Submit broadband ticket with manual fallback
        $response = $this->actingAs($helpdesk)->post(route('tickets.broadband.store'), [
            'customer_id' => 'CID-OFFLINE-999',
            'customer_name' => 'Klinik Sehat Soreang',
            'customer_phone' => '082219998888',
            'customer_address' => 'Jl. Raya Soreang No. 12',
            'customer_odp' => 'ODP-SRE-05',
            'priority' => 'P2',
            'notes' => 'Koneksi terputus tiba-tiba',
            'is_manual_fallback' => 1,
        ]);

        $response->assertRedirect();

        $tiket = Tiket::query()->where('customer_id', 'CID-OFFLINE-999')->firstOrFail();
        $this->assertStringStartsWith('BRD-', $tiket->no_tiket);
        $this->assertFalse($tiket->is_ims_validated, 'Ticket must be marked as not IMS-validated when created in manual fallback (AC-11)');
    }

    /**
     * Test 30-minute interval compliance and breach detection (§7.3).
     */
    public function test_interval_compliance_and_breach_detection(): void
    {
        $helpdesk = User::query()->where('username', 'helpdesk')->firstOrFail();
        $teknisi = User::query()->where('username', 'teknisi1')->firstOrFail();

        $t0 = Carbon::now()->subMinutes(90);

        $tiket = Tiket::query()->create([
            'no_tiket' => 'BRD-20261106-050',
            'kategori_tiket' => 'BROADBAND',
            'priority' => 'P2',
            'status' => 'OPEN',
            'sla_target_minutes' => 240,
            'customer_name' => 'Bpk. Ahmad',
            'customer_phone' => '081234567892',
            'tanggal_open' => $t0,
            'created_by' => $helpdesk->id,
        ]);

        $chronService = app(ChronologyService::class);

        // 1. First response at T+10m -> Transitions OPEN to PROSES
        $t1 = $t0->copy()->addMinutes(10);
        $chronService->addChronology($tiket, $teknisi, 'IZIN', 'Tiba di lokasi', createdAt: $t1);
        $tiket->refresh();
        $this->assertSame('PROSES', $tiket->status);
        $this->assertNotNull($tiket->first_response_at);

        // 2. Second update at T+55m (45 minutes later > 30m) -> Breach recorded!
        $t2 = $t1->copy()->addMinutes(45);
        $chronService->addChronology($tiket, $teknisi, 'OTDR', 'Tracing kabel dilakukan', createdAt: $t2);

        $this->assertSame(1, $tiket->breaches()->count());
        $breach = $tiket->breaches()->first();
        $this->assertSame(45, $breach->elapsed_minutes);
    }

    /**
     * Test material atomic decrement and inventory movements (§7.6).
     */
    public function test_material_atomic_usage_and_inventory_movements(): void
    {
        $helpdesk = User::query()->where('username', 'helpdesk')->firstOrFail();
        $teknisi = User::query()->where('username', 'teknisi1')->firstOrFail();

        $mat = MasterMaterial::query()->where('kode', 'JC-24C')->firstOrFail();
        $initialStock = $mat->stok_tersedia;

        $tiket = Tiket::query()->create([
            'no_tiket' => 'BDG-20261106-060',
            'kategori_tiket' => 'BACKBONE',
            'priority' => 'P2',
            'status' => 'PROSES',
            'sla_target_minutes' => 360,
            'backbone_segment' => 'BB-TEST-01',
            'wilayah' => 'Bandung',
            'tipe_penanganan' => 'Joint Closure Replacement',
            'tanggal_open' => now(),
            'created_by' => $helpdesk->id,
        ]);

        $assetService = app(TechnicalAssetService::class);
        $result = $assetService->useMaterial($tiket, $mat->id, 2, $teknisi);

        $mat->refresh();
        $this->assertSame($initialStock - 2, $mat->stok_tersedia);
        $this->assertSame('OUT', $result['movement']->type);
        $this->assertSame(2, $result['movement']->quantity);
        $this->assertSame($initialStock, $result['movement']->stock_before);
        $this->assertSame($initialStock - 2, $result['movement']->stock_after);
    }

    /**
     * Test shift handover creation, auto-summary, and auto-dispatch of recipient (§7.5).
     */
    public function test_shift_handover_adds_recipient_to_assignees_and_escalation(): void
    {
        $helpdesk = User::query()->where('username', 'helpdesk')->firstOrFail();
        $teknisi1 = User::query()->where('username', 'teknisi1')->firstOrFail();

        $teknisi2 = User::query()->create([
            'employee_code' => 'MSN-088',
            'username' => 'teknisi_malam',
            'name' => 'Joko Shift Malam',
            'email' => 'joko.malam@msn.co.id',
            'phone' => '08112000088',
            'division' => 'Operasional Jaringan',
            'position' => 'Teknisi FO',
            'is_active' => true,
            'password' => bcrypt('MsnPortal#2026'),
        ]);
        $teknisi2->assignRole('teknisi');

        $tiket = Tiket::query()->create([
            'no_tiket' => 'BDG-20261106-070',
            'kategori_tiket' => 'BACKBONE',
            'priority' => 'P1',
            'status' => 'PROSES',
            'sla_target_minutes' => 180,
            'backbone_segment' => 'BB-TEST-02',
            'wilayah' => 'Soreang',
            'tipe_penanganan' => 'Repair FO',
            'tanggal_open' => now(),
            'created_by' => $helpdesk->id,
        ]);

        $handoverService = app(HandoverService::class);
        $handover = $handoverService->createHandover(
            $tiket,
            'Shift Siang',
            'Shift Malam',
            $teknisi1,
            $teknisi2,
            'Splicing core 1-12 selesai, lanjutkan core 13-24'
        );

        // Assert recipient is automatically added to ticket assignees (§7.5)
        $this->assertTrue($tiket->assignees()->where('user_id', $teknisi2->id)->exists());
        $this->assertStringContainsString('Ringkasan Sistem:', $handover->auto_summary);

        // Test acknowledge
        $handoverService->acknowledge($handover, $teknisi2);
        $this->assertNotNull($handover->fresh()->acknowledged_at);
    }
}
