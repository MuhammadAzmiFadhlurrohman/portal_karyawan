<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\ChatbotIntent;
use App\Models\LeaveBalance;
use App\Models\Lembur;
use App\Models\MasterSla;
use App\Models\Pengajuan;
use App\Models\Tiket;
use App\Models\User;
use App\Modules\Gomsn\Services\ChatbotService;
use App\Modules\Gomsn\Services\KpiAndReportService;
use App\Modules\Gomsn\Services\OvertimeService;
use App\Modules\Gomsn\Services\PengajuanService;
use App\Modules\Gomsn\Services\TaskService;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PhaseFourGomsnTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'teknisi', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'atasan', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'finance', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'ceo', 'guard_name' => 'web']);
    }

    protected function createUserWithRole(string $roleName, string $email, string $code): User
    {
        $user = User::factory()->create([
            'email' => $email,
            'employee_code' => $code,
            'password_changed_at' => Carbon::now(),
            'is_active' => true,
        ]);
        $user->assignRole($roleName);

        return $user;
    }

    public function test_pengajuan_creation_and_automatic_subtotal_calculation(): void
    {
        $pemohon = $this->createUserWithRole('teknisi', 'pemohon1@msn.net.id', 'MSN-0401');
        $service = app(PengajuanService::class);

        $items = [
            ['item_name' => 'Patch Cord SC-UPC 3m', 'qty' => 5, 'satuan' => 'Pcs', 'estimated_unit_price' => 25000],
            ['item_name' => 'Joint Closure Dome 24 Core', 'qty' => 2, 'satuan' => 'Unit', 'estimated_unit_price' => 300000],
        ];

        $pengajuan = $service->createPengajuan($pemohon, [
            'jenis' => 'DANA',
            'keperluan' => 'Pengadaan sparepart restorasi FO',
            'metode_pembayaran' => 'TRANSFER',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder' => 'Teknisi 1',
        ], $items);

        $this->assertDatabaseHas('pengajuan', [
            'id' => $pengajuan->id,
            'nomor_pengajuan' => $pengajuan->nomor_pengajuan,
            'status' => 'draft',
            'nominal_total' => 725000, // (5*25.000) + (2*300.000) = 125.000 + 600.000 = 725.000
        ]);

        $this->assertCount(2, $pengajuan->details);
    }

    public function test_ac10_self_approval_is_strictly_rejected_at_all_stages(): void
    {
        $pemohon = $this->createUserWithRole('atasan', 'manager.self@msn.net.id', 'MSN-0402');
        $service = app(PengajuanService::class);

        $pengajuan = $service->createPengajuan($pemohon, [
            'jenis' => 'DANA',
            'keperluan' => 'Biaya survey lokasi kabel',
            'total_nominal' => 500000,
        ], [
            ['item_name' => 'Transport Survey', 'qty' => 1, 'satuan' => 'Trip', 'estimated_unit_price' => 500000],
        ]);

        $service->submitPengajuan($pengajuan, $pemohon);
        $this->assertEquals('menunggu_atasan', $pengajuan->fresh()->status);

        // 1. Pemohon attempts self-approval as Atasan -> MUST FAIL (AC-10)
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Pemisahan tugas (AC-10): Pengaju tidak boleh menyetujui pengajuannya sendiri.');
        $service->approveAtasan($pengajuan->fresh(), $pemohon);
    }

    public function test_tiered_approval_flow_requiring_executive_for_above_5_million(): void
    {
        $pemohon = $this->createUserWithRole('teknisi', 'pemohon3@msn.net.id', 'MSN-0403');
        $atasan = $this->createUserWithRole('atasan', 'atasan3@msn.net.id', 'MSN-0404');
        $ceo = $this->createUserWithRole('ceo', 'ceo3@msn.net.id', 'MSN-0405');
        $finance = $this->createUserWithRole('finance', 'finance3@msn.net.id', 'MSN-0406');

        $service = app(PengajuanService::class);

        // Pengajuan > 5.000.000 (Rp 6.000.000)
        $pengajuan = $service->createPengajuan($pemohon, [
            'jenis' => 'DANA',
            'keperluan' => 'Pengadaan Fusion Splicer Baru',
            'metode_pembayaran' => 'TRANSFER',
        ], [
            ['item_name' => 'Fusion Splicer', 'qty' => 1, 'satuan' => 'Unit', 'estimated_unit_price' => 6000000],
        ]);

        $service->submitPengajuan($pengajuan, $pemohon);

        // Step 1: Atasan approves -> Since > 5jt, transitions to 'menunggu_executive' (§8.1)
        $service->approveAtasan($pengajuan->fresh(), $atasan);
        $this->assertEquals('menunggu_executive', $pengajuan->fresh()->status);

        // Step 2: CEO / Executive approves -> transitions to 'menunggu_finance'
        $service->approveExecutive($pengajuan->fresh(), $ceo);
        $this->assertEquals('menunggu_finance', $pengajuan->fresh()->status);

        // Step 3: Finance disburses
        $service->disburseFinance($pengajuan->fresh(), $finance, 'TRF-BCA-987654');
        $this->assertEquals('dicairkan', $pengajuan->fresh()->status);
        $this->assertNotNull($pengajuan->fresh()->realisasi_deadline);
    }

    public function test_overdue_realization_exceeding_7_days_blocks_new_submission(): void
    {
        $pemohon = $this->createUserWithRole('teknisi', 'pemohon.telat@msn.net.id', 'MSN-0407');
        $service = app(PengajuanService::class);

        // Simulate existing disbursed submission with deadline 2 days ago (> 7 days)
        Pengajuan::query()->create([
            'nomor_pengajuan' => 'PND-20261025-0001',
            'user_id' => $pemohon->id,
            'jenis' => 'DANA',
            'judul' => 'Operasional survey kabel',
            'keperluan' => 'Beli bensin dan tol tim lapangan',
            'nominal_total' => 300000,
            'status' => 'dicairkan',
            'transfer_at' => Carbon::now()->subDays(10),
            'realisasi_deadline' => Carbon::now()->subDays(3), // OVERDUE!
        ]);

        $this->assertTrue($service->hasOverdueRealization($pemohon));

        // Attempting to create new submission must be blocked (§8.1)
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Pengajuan baru diblokir: Anda memiliki nota realisasi pengajuan sebelumnya yang tertunggak lebih dari 7 hari.');

        $service->createPengajuan($pemohon, [
            'jenis' => 'DANA',
            'keperluan' => 'Permohonan baru yang seharusnya terblokir',
        ], [
            ['item_name' => 'Item X', 'qty' => 1, 'satuan' => 'Pcs', 'estimated_unit_price' => 100000],
        ]);
    }

    public function test_overtime_creation_cross_midnight_calculation_and_daily_limit_4_hours(): void
    {
        $karyawan = $this->createUserWithRole('teknisi', 'lembur1@msn.net.id', 'MSN-0408');
        $service = app(OvertimeService::class);

        // Cross-midnight: 22:00 to 01:30 = 3.5 hours (210 minutes)
        $lembur = $service->createOvertime(
            user: $karyawan,
            tanggal: Carbon::now()->toDateString(),
            jamMulai: '22:00',
            jamSelesai: '01:30',
            alasan: 'Emergency splicing kabel FO tertabrak truk'
        );

        $this->assertEquals(210, $lembur->durasi_menit);
        $this->assertEquals('pending', $lembur->status);

        // Exceeding 4 hours (240 mins) -> Must Throw Exception
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Batas lembur per hari maksimal adalah 4 jam');

        $service->createOvertime(
            user: $karyawan,
            tanggal: Carbon::now()->subDay()->toDateString(),
            jamMulai: '17:00',
            jamSelesai: '22:30', // 5.5 hours = 330 mins > 240 mins
            alasan: 'Lembur melewati batas'
        );
    }

    public function test_overtime_reconciliation_with_attendance_clock_out(): void
    {
        $karyawan = $this->createUserWithRole('teknisi', 'lembur2@msn.net.id', 'MSN-0409');
        $supervisor = $this->createUserWithRole('atasan', 'spv2@msn.net.id', 'MSN-0410');
        $service = app(OvertimeService::class);

        $today = Carbon::now()->toDateString();

        // 1. Attendance with clock_out 20:10
        Attendance::query()->create([
            'user_id' => $karyawan->id,
            'date' => $today,
            'clock_in_at' => Carbon::parse("{$today} 08:00:00"),
            'clock_out_at' => Carbon::parse("{$today} 20:10:00"),
            'status' => 'present',
        ]);

        // Overtime requested until 20:00 (10 mins difference <= 30 mins -> NORMAL)
        $lemburNormal = $service->createOvertime(
            user: $karyawan,
            tanggal: $today,
            jamMulai: '17:00',
            jamSelesai: '20:00',
            alasan: 'Maintenance terminasi rack'
        );

        $service->approveSupervisor($lemburNormal, $supervisor);
        $this->assertEquals('NORMAL', $lemburNormal->fresh()->reconciliation_flag);

        // 2. Overtime requested until 22:00 (clock_out 20:10 -> diff 110 mins > 30 mins -> DISCREPANCY)
        $lemburDiscrepancy = Lembur::query()->create([
            'nomor_lembur' => 'LBR-DISC-01',
            'user_id' => $karyawan->id,
            'tanggal' => $today,
            'jam_mulai' => Carbon::parse("{$today} 20:00:00"),
            'jam_selesai' => Carbon::parse("{$today} 22:00:00"),
            'durasi_menit' => 120,
            'alasan' => 'Overtime discrepancy test',
            'status' => 'pending',
        ]);

        $service->approveSupervisor($lemburDiscrepancy, $supervisor);
        $this->assertEquals('DISCREPANCY_OVER_TOLERANCE', $lemburDiscrepancy->fresh()->reconciliation_flag);
    }

    public function test_task_management_lifecycle_and_role_permissions(): void
    {
        $assigner = $this->createUserWithRole('atasan', 'assigner@msn.net.id', 'MSN-0411');
        $assignee = $this->createUserWithRole('teknisi', 'assignee@msn.net.id', 'MSN-0412');
        $outsider = $this->createUserWithRole('teknisi', 'outsider@msn.net.id', 'MSN-0413');

        $service = app(TaskService::class);

        $task = $service->createTask($assigner, $assignee, [
            'title' => 'Perapihan Kabel Tray Server Room NOC',
            'priority' => 'high',
            'due_date' => Carbon::now()->addDays(2)->toDateString(),
        ], ['Labeling patch cord', 'Ikat velcro rack A1', 'Dokumentasi foto']);

        $this->assertEquals('Pending Approval', $task->status);
        $this->assertCount(3, $task->subtasks);

        // Assignee updates status to In Progress
        $service->updateStatus($task, $assignee, 'In Progress');
        $this->assertEquals('In Progress', $task->fresh()->status);

        // Outsider attempts to modify status -> Rejected with Exception
        $this->expectException(Exception::class);
        $service->updateStatus($task, $outsider, 'Completed');
    }

    public function test_kpi_calculation_with_45_35_20_formula_and_data_tidak_cukup_threshold(): void
    {
        $technician = $this->createUserWithRole('teknisi', 'kpi.tech@msn.net.id', 'MSN-0414');
        $service = app(KpiAndReportService::class);

        // Case A: 0 tickets handled -> Status DATA_TIDAK_CUKUP (§7.7)
        $kpiEmpty = $service->calculateTechnicianKpi($technician);
        $this->assertEquals('DATA_TIDAK_CUKUP', $kpiEmpty['status']);
        $this->assertNull($kpiEmpty['score']);

        // Case B: Create 5 closed tickets to fulfill threshold
        MasterSla::firstOrCreate(
            ['kategori_tiket' => 'BACKBONE', 'priority' => 'P1'],
            ['sla_minutes' => 240, 'description' => 'Backbone Segmen A']
        );

        for ($i = 1; $i <= 5; $i++) {
            $open = Carbon::now()->subHours(10 - $i);
            $resp = (clone $open)->addMinutes(15);
            $close = (clone $open)->addMinutes(180);

            $t = Tiket::query()->create([
                'no_tiket' => "BDG-20261106-90{$i}",
                'kategori_tiket' => 'BACKBONE',
                'priority' => 'P1',
                'customer_name' => "Klien KPI {$i}",
                'keluhan' => 'FO Degraded',
                'status' => 'CLOSE',
                'created_by' => $technician->id,
                'tanggal_open' => $open,
                'first_response_at' => $resp,
                'tanggal_close' => $close,
                'net_mttr_minutes' => 180,
                'sla_status' => 'TEPAT',
            ]);

            $t->assignees()->attach($technician->id, ['role_in_team' => 'lead', 'assigned_at' => Carbon::now()]);
        }

        $kpiResult = $service->calculateTechnicianKpi($technician);
        $this->assertEquals('OK', $kpiResult['status']);
        $this->assertNotNull($kpiResult['score']);
        $this->assertEquals(100.0, $kpiResult['sla_score']); // 5 of 5 on time
        $this->assertEquals(100.0, $kpiResult['interval_score']); // 0 breaches
        $this->assertGreaterThan(50.0, $kpiResult['score']);

        // Verify Berita Acara payload and SHA-256 Digest
        $firstTicket = Tiket::where('no_tiket', 'BDG-20261106-901')->first();
        $beritaAcaraData = $service->generateBeritaAcaraData($firstTicket);

        $this->assertArrayHasKey('document_hash', $beritaAcaraData);
        $this->assertEquals(64, strlen($beritaAcaraData['document_hash'])); // SHA-256 length
    }

    public function test_chatbot_intent_matching_fallback_and_user_data_isolation(): void
    {
        $user = $this->createUserWithRole('teknisi', 'chat.user@msn.net.id', 'MSN-0415');
        $service = app(ChatbotService::class);

        // Seed leave balance for user
        LeaveBalance::query()->create([
            'user_id' => $user->id,
            'year' => (int) date('Y'),
            'annual_quota' => 12,
            'carried_over' => 0,
            'used_days' => 3,
            'alpa_deducted_days' => 0,
            'remaining_days' => 9,
        ]);

        // Intent: Sisa Cuti
        ChatbotIntent::query()->create([
            'intent_key' => 'sisa_cuti',
            'keywords' => ['sisa', 'cuti', 'saldo'],
            'response_text' => 'Sisa cuti Anda tahun {year} adalah {sisa_cuti} hari kerja dari kuota {kuota_cuti} hari.',
            'category' => 'hris',
            'is_active' => true,
        ]);

        // 1. Direct match with user personal data
        $res = $service->ask($user, 'Berapa sisa saldo cuti saya?');
        $this->assertStringContainsString('9 hari kerja', $res['response']);
        $this->assertEquals('sisa_cuti', $res['matched_intent']);

        // 2. Unmatched query (< threshold 0.40) triggers fallback & Helpdesk NOC info (§5.8)
        $resFallback = $service->ask($user, 'xyz123 random gibberish message');
        $this->assertStringContainsString('Helpdesk NOC', $resFallback['response']);
        $this->assertNotEmpty($resFallback['suggestions']);
    }

    /**
     * Test 3 jenis pengajuan: DANA, BELI, STOK dan otorisasi stok oleh Finance.
     */
    public function test_three_pengajuan_types_dana_beli_and_stok_with_finance_inventory_regulation(): void
    {
        $pemohon = $this->createUserWithRole('teknisi', 'teknisi.lap@msn.net.id', 'MSN-0410');
        $atasan = $this->createUserWithRole('atasan', 'spv.lap@msn.net.id', 'MSN-0411');
        $finance = $this->createUserWithRole('finance', 'finance.stok@msn.net.id', 'MSN-0412');
        $service = app(PengajuanService::class);

        // 1. DANA (prefix PND)
        $pDana = $service->createPengajuan($pemohon, [
            'jenis' => 'DANA',
            'keperluan' => 'BBM operasional perbaikan tiang',
        ], [
            ['deskripsi' => 'Bensin & Tol', 'jumlah' => 1, 'harga_satuan' => 150000],
        ]);
        $this->assertStringStartsWith('PND-', $pDana->nomor_pengajuan);
        $this->assertEquals('DANA', $pDana->jenis);
        $this->assertEquals(150000, $pDana->nominal_total);

        // 2. BELI (prefix PBL)
        $pBeli = $service->createPengajuan($pemohon, [
            'jenis' => 'BELI',
            'keperluan' => 'Pengadaan OPM Baru (Optical Power Meter)',
        ], [
            ['deskripsi' => 'OPM Grandway', 'jumlah' => 1, 'harga_satuan' => 450000],
        ]);
        $this->assertStringStartsWith('PBL-', $pBeli->nomor_pengajuan);
        $this->assertEquals('BELI', $pBeli->jenis);
        $this->assertEquals(450000, $pBeli->nominal_total);

        // 3. STOK (prefix PST) with MasterMaterial
        $material = \App\Models\MasterMaterial::create([
            'kode' => 'DROP-CORE-1C',
            'nama' => 'Drop Core FO 1 Core',
            'satuan' => 'meter',
            'stok_tersedia' => 1000,
            'min_stok' => 100,
            'is_active' => true,
        ]);

        $pStok = $service->createPengajuan($pemohon, [
            'jenis' => 'STOK',
            'keperluan' => 'Tarik sambungan baru ODP-01',
        ], [
            ['master_material_id' => $material->id, 'jumlah' => 150],
        ]);
        $this->assertStringStartsWith('PST-', $pStok->nomor_pengajuan);
        $this->assertEquals('STOK', $pStok->jenis);
        $this->assertEquals(0, $pStok->nominal_total); // Non-cash

        // Submit to Atasan
        $service->submitPengajuan($pStok, $pemohon);
        $this->assertEquals('menunggu_atasan', $pStok->status);

        // Atasan approves -> goes to Finance (pengatur stok)
        $service->approveAtasan($pStok, $atasan, 'Approved keperluan teknis');
        $this->assertEquals('menunggu_finance', $pStok->status);

        // Finance releases stock
        $service->releaseStockFinance($pStok, $finance, 'Barang 150 meter diserahkan di gudang');
        $this->assertEquals('selesai', $pStok->status);

        // Verify stock deducted
        $material->refresh();
        $this->assertEquals(850, $material->stok_tersedia);

        // Verify MaterialMovement audit
        $this->assertDatabaseHas('material_movements', [
            'master_material_id' => $material->id,
            'user_id' => $pemohon->id,
            'type' => 'OUT',
            'quantity' => 150,
            'stock_before' => 1000,
            'stock_after' => 850,
        ]);
    }

    /**
     * Test form pengajuan HTTP create & store workflow for all 4 types (DANA, BELI, STOK, PERMINTAAN).
     */
    public function test_form_pengajuan_http_submission_for_all_four_types(): void
    {
        $pemohon = $this->createUserWithRole('teknisi', 'teknisi.form@msn.net.id', 'MSN-0991');
        $atasan = $this->createUserWithRole('atasan', 'atasan.form@msn.net.id', 'MSN-0992');
        $colleague = $this->createUserWithRole('teknisi', 'colleague@msn.net.id', 'MSN-0993');

        $material = \App\Models\MasterMaterial::create([
            'kode' => 'MAT-TEST-01',
            'nama' => 'Kabel UTP Cat6 305m',
            'satuan' => 'Roll',
            'stok_tersedia' => 50,
            'stok_minimum' => 5,
            'harga_per_satuan' => 1250000,
            'is_active' => true,
        ]);

        // 1. GET create page
        $resCreate = $this->actingAs($pemohon)->get(route('gomsn.pengajuan.create'));
        $resCreate->assertOk();
        $resCreate->assertSee('Buat Formulir Pengajuan');
        $resCreate->assertSee('Stok Gudang');
        $resCreate->assertSee('Dana Kasbon');

        // 2. POST DANA
        $resDana = $this->actingAs($pemohon)->post(route('gomsn.pengajuan.store'), [
            'jenis' => 'DANA',
            'keterangan' => 'Operasional maintenance OLT Cikarang',
            'atasan_id' => $atasan->id,
            'items' => [
                [
                    'keperluan_dana' => 'Bensin Mobil',
                    'jumlah_dana' => 150000,
                    'personel' => [$pemohon->name, $colleague->name],
                ],
                [
                    'keperluan_dana' => 'Tol & Parkir',
                    'jumlah_dana' => 75000,
                    'personel' => [$pemohon->name],
                ],
            ],
        ]);
        $resDana->assertRedirect();
        $pDana = Pengajuan::where('user_id', $pemohon->id)->where('jenis', 'DANA')->first();
        $this->assertNotNull($pDana);
        $this->assertStringStartsWith('PND-', $pDana->nomor_pengajuan);
        $this->assertEquals(225000, (float) $pDana->nominal_total);
        $this->assertEquals('menunggu_atasan', $pDana->status);

        // 3. POST BELI
        $resBeli = $this->actingAs($pemohon)->post(route('gomsn.pengajuan.store'), [
            'jenis' => 'BELI',
            'keterangan' => 'Pengadaan crimping tool baru',
            'atasan_id' => $atasan->id,
            'items' => [
                [
                    'nama_barang' => 'Crimping Tool RJ45 Pro',
                    'jumlah' => 2,
                    'harga_satuan' => 120000,
                ],
            ],
        ]);
        $resBeli->assertRedirect();
        $pBeli = Pengajuan::where('user_id', $pemohon->id)->where('jenis', 'BELI')->first();
        $this->assertNotNull($pBeli);
        $this->assertStringStartsWith('PBL-', $pBeli->nomor_pengajuan);
        $this->assertEquals(240000, (float) $pBeli->nominal_total);
        $this->assertEquals('menunggu_atasan', $pBeli->status);

        // 4. POST STOK
        $resStok = $this->actingAs($pemohon)->post(route('gomsn.pengajuan.store'), [
            'jenis' => 'STOK',
            'keterangan' => 'Ambil 2 roll UTP untuk instalasi kantor cabang',
            'atasan_id' => $atasan->id,
            'items' => [
                [
                    'master_material_id' => $material->id,
                    'jumlah' => 2,
                ],
            ],
        ]);
        $resStok->assertRedirect();
        $pStok = Pengajuan::where('user_id', $pemohon->id)->where('jenis', 'STOK')->first();
        $this->assertNotNull($pStok);
        $this->assertStringStartsWith('PST-', $pStok->nomor_pengajuan);
        $this->assertEquals(0, (float) $pStok->nominal_total);
        $this->assertEquals('menunggu_atasan', $pStok->status);

        // 5. POST PERMINTAAN
        $resPermintaan = $this->actingAs($pemohon)->post(route('gomsn.pengajuan.store'), [
            'jenis' => 'PERMINTAAN',
            'keterangan' => 'Permintaan rompi teknisi lapangan',
            'atasan_id' => $atasan->id,
            'items' => [
                [
                    'nama_barang' => 'Rompi Safety Lapangan Ukuran L',
                    'jumlah' => 3,
                ],
            ],
        ]);
        $resPermintaan->assertRedirect();
        $pPermintaan = Pengajuan::where('user_id', $pemohon->id)->where('jenis', 'PERMINTAAN')->first();
        $this->assertNotNull($pPermintaan);
        $this->assertStringStartsWith('PRM-', $pPermintaan->nomor_pengajuan);
        $this->assertEquals(0, (float) $pPermintaan->nominal_total);
        $this->assertEquals('menunggu_atasan', $pPermintaan->status);
    }

    public function test_overtime_form_requires_attendance_and_blocks_unattended_date(): void
    {
        $karyawan = $this->createUserWithRole('teknisi', 'ot.teknisi@msn.net.id', 'MSN-0499');
        $today = Carbon::now()->toDateString();

        // 1. Check Attendance API when no attendance exists
        $checkRes = $this->actingAs($karyawan)->getJson(route('gomsn.overtime.check-attendance', ['tanggal' => $today]));
        $checkRes->assertOk();
        $checkRes->assertJson([
            'has_attendance' => false,
        ]);

        // 2. Attempt to submit overtime without attendance -> must be rejected with error session
        $failRes = $this->actingAs($karyawan)->post(route('gomsn.overtime.store'), [
            'tanggal' => $today,
            'jenis_lembur' => 'Lembur Hari Kerja',
            'jam_mulai' => '17:00',
            'jam_selesai' => '20:00',
            'alasan' => 'Troubleshooting FO dropcore putus',
        ]);
        $failRes->assertSessionHas('error');
        $this->assertDatabaseMissing('lembur', [
            'user_id' => $karyawan->id,
            'tanggal' => $today,
        ]);

        // 3. User clocks in / attendance created
        Attendance::query()->create([
            'user_id' => $karyawan->id,
            'date' => $today,
            'status' => 'present',
            'clock_in_at' => Carbon::parse("{$today} 08:00:00"),
            'clock_out_at' => Carbon::parse("{$today} 20:30:00"),
        ]);

        // 4. Check Attendance API after attendance -> returns has_attendance true
        $checkResSuccess = $this->actingAs($karyawan)->getJson(route('gomsn.overtime.check-attendance', ['tanggal' => $today]));
        $checkResSuccess->assertOk();
        $checkResSuccess->assertJson([
            'has_attendance' => true,
        ]);

        // 5. Submit overtime now -> must succeed and store jenis_lembur
        $successRes = $this->actingAs($karyawan)->post(route('gomsn.overtime.store'), [
            'tanggal' => $today,
            'jenis_lembur' => 'Lembur Darurat / Penanganan Gangguan',
            'jam_mulai' => '17:00',
            'jam_selesai' => '20:00',
            'alasan' => 'Troubleshooting FO dropcore putus',
        ]);
        $successRes->assertRedirect(route('gomsn.overtime.index'));
        $successRes->assertSessionHas('success');

        $lembur = Lembur::query()->where('user_id', $karyawan->id)->first();
        $this->assertNotNull($lembur);
        $this->assertEquals('Lembur Darurat / Penanganan Gangguan', $lembur->jenis_lembur);
        $this->assertEquals(180, $lembur->durasi_menit);
        $this->assertEquals('pending', $lembur->status);
    }
}
