<?php

namespace Tests\Feature;

use App\Models\MasterMaterial;
use App\Models\Pengajuan;
use App\Models\User;
use App\Modules\Gomsn\Services\PengajuanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AtasanPengajuanFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure roles & permissions exist
        $permissions = [
            'gomsn.pengajuan.approve_atasan',
            'gomsn.pengajuan.approve_finance',
            'gomsn.pengajuan.approve_executive',
            'gomsn.lembur.approve',
        ];

        foreach ($permissions as $p) {
            Permission::findOrCreate($p, 'web');
        }

        $atasanRole = Role::findOrCreate('atasan_teknik', 'web');
        $atasanRole->givePermissionTo(['gomsn.pengajuan.approve_atasan', 'gomsn.lembur.approve']);

        $atasanNocRole = Role::findOrCreate('atasan_noc', 'web');
        $atasanNocRole->givePermissionTo(['gomsn.pengajuan.approve_atasan', 'gomsn.lembur.approve']);

        $financeRole = Role::findOrCreate('finance', 'web');
        $financeRole->givePermissionTo('gomsn.pengajuan.approve_finance');

        Role::findOrCreate('ceo', 'web');
        Role::findOrCreate('hrd', 'web');

        $karyawanRole = Role::findOrCreate('karyawan_teknik', 'web');
        $karyawanNocRole = Role::findOrCreate('karyawan_noc', 'web');
    }

    protected function createTestUser(string $name, string $role, string $code, string $division = 'Teknik'): User
    {
        $user = User::create([
            'name' => $name,
            'username' => strtolower(str_replace(' ', '_', $name)),
            'email' => strtolower(str_replace(' ', '.', $name)) . '@msn.net.id',
            'phone' => '0812' . rand(10000000, 99999999),
            'employee_code' => $code,
            'password' => bcrypt('Password#2026'),
            'division' => $division,
            'is_active' => true,
        ]);

        $user->assignRole($role);
        return $user;
    }

    public function test_atasan_self_submission_automatically_goes_directly_to_finance(): void
    {
        $atasan = $this->createTestUser('Ahmad Supervisor', 'atasan_teknik', 'KR8801');

        $response = $this->actingAs($atasan)->post(route('gomsn.pengajuan.store'), [
            'jenis' => 'DANA',
            'keterangan' => 'Operasional survey lapangan mandiri Atasan',
            'items' => [
                [
                    'keperluan_dana' => 'BBM & Tol',
                    'jumlah_dana' => 150000,
                ],
            ],
        ]);

        $response->assertRedirect();
        
        $pengajuan = Pengajuan::where('user_id', $atasan->id)->latest('id')->first();
        $this->assertNotNull($pengajuan);
        
        // HARUS langsung ke finance, TIDAK ke menunggu_atasan
        $this->assertEquals('menunggu_finance', $pengajuan->status);
        $this->assertEquals('finance', $pengajuan->current_approver_stage);
        
        // Memiliki catatan approval otomatis mandiri
        $this->assertDatabaseHas('pengajuan_approvals', [
            'pengajuan_id' => $pengajuan->id,
            'stage' => 'atasan',
            'action' => 'approved',
        ]);
    }

    public function test_karyawan_submission_goes_to_menunggu_atasan(): void
    {
        $karyawan = $this->createTestUser('Budi Teknisi', 'karyawan_teknik', 'KR8802');

        $response = $this->actingAs($karyawan)->post(route('gomsn.pengajuan.store'), [
            'jenis' => 'DANA',
            'keterangan' => 'Beli konektor RJ45 darurat',
            'items' => [
                [
                    'keperluan_dana' => 'Konektor RJ45',
                    'jumlah_dana' => 75000,
                ],
            ],
        ]);

        $response->assertRedirect();

        $pengajuan = Pengajuan::where('user_id', $karyawan->id)->latest('id')->first();
        $this->assertNotNull($pengajuan);
        
        // Karyawan biasa harus menunggu atasan
        $this->assertEquals('menunggu_atasan', $pengajuan->status);
        $this->assertEquals('atasan', $pengajuan->current_approver_stage);
    }

    public function test_atasan_approval_page_shows_karyawan_submissions_and_excludes_self(): void
    {
        $atasan = $this->createTestUser('Ahmad Supervisor', 'atasan_teknik', 'KR8801');
        $karyawan = $this->createTestUser('Budi Teknisi', 'karyawan_teknik', 'KR8802');

        // 1. Karyawan submit ke Atasan
        $service = app(PengajuanService::class);
        $pKaryawan = $service->createPengajuan($karyawan, [
            'jenis' => 'DANA',
            'atasan_id' => $atasan->id,
            'keperluan' => 'Kebutuhan tim teknisi',
        ], [
            ['deskripsi' => 'Item 1', 'jumlah' => 1, 'harga_satuan' => 50000],
        ]);
        $service->submitPengajuan($pKaryawan, $karyawan);

        // 2. Atasan self submit (langsung ke finance)
        $pAtasan = $service->createPengajuan($atasan, [
            'jenis' => 'DANA',
            'keperluan' => 'Kebutuhan mandiri atasan',
        ], [
            ['deskripsi' => 'Item Atasan', 'jumlah' => 1, 'harga_satuan' => 100000],
        ]);
        $service->submitPengajuan($pAtasan, $atasan, directToFinance: true);

        // 3. Atasan buka halaman approvals
        $resApprovals = $this->actingAs($atasan)->get(route('gomsn.pengajuan.approvals'));
        $resApprovals->assertOk();
        $resApprovals->assertSee($pKaryawan->nomor_pengajuan);
        $resApprovals->assertDontSee($pAtasan->nomor_pengajuan);

        // 4. Atasan approve pengajuan karyawan
        $resApprove = $this->actingAs($atasan)->post(route('gomsn.pengajuan.approve-atasan', $pKaryawan->id), [
            'notes' => 'Disetujui untuk operasional lapangan',
        ]);
        $resApprove->assertRedirect();
        
        $this->assertEquals('menunggu_finance', $pKaryawan->fresh()->status);
    }

    public function test_finance_only_sees_submissions_approved_by_atasan_and_blocks_unapproved(): void
    {
        $atasan = $this->createTestUser('Ahmad Supervisor', 'atasan_teknik', 'KR8801');
        $karyawan = $this->createTestUser('Budi Teknisi', 'karyawan_teknik', 'KR8802');
        $finance = $this->createTestUser('Siti Finance', 'finance', 'KR8803');

        $service = app(PengajuanService::class);

        // 1. Karyawan submission STILL menunggu_atasan (NOT approved by atasan)
        $pUnapproved = $service->createPengajuan($karyawan, [
            'jenis' => 'DANA',
            'keperluan' => 'Belum diapprove atasan',
        ], [
            ['deskripsi' => 'Item', 'jumlah' => 1, 'harga_satuan' => 20000],
        ]);
        $service->submitPengajuan($pUnapproved, $karyawan);

        // 2. Karyawan submission ALREADY approved by atasan
        $pApproved = $service->createPengajuan($karyawan, [
            'jenis' => 'DANA',
            'keperluan' => 'Sudah diapprove atasan',
        ], [
            ['deskripsi' => 'Item', 'jumlah' => 1, 'harga_satuan' => 30000],
        ]);
        $service->submitPengajuan($pApproved, $karyawan);
        $service->approveAtasan($pApproved, $atasan);

        // 3. Atasan direct submission (auto to finance)
        $pAtasan = $service->createPengajuan($atasan, [
            'jenis' => 'DANA',
            'keperluan' => 'Mandiri atasan',
        ], [
            ['deskripsi' => 'Item', 'jumlah' => 1, 'harga_satuan' => 40000],
        ]);
        $service->submitPengajuan($pAtasan, $atasan, directToFinance: true);

        // 4. Karyawan submission REJECTED by Atasan
        $pRejectedByAtasan = $service->createPengajuan($karyawan, [
            'jenis' => 'DANA',
            'keperluan' => 'Ditolak oleh atasan',
        ], [
            ['deskripsi' => 'Item', 'jumlah' => 1, 'harga_satuan' => 50000],
        ]);
        $service->submitPengajuan($pRejectedByAtasan, $karyawan);
        $service->rejectPengajuan($pRejectedByAtasan, $atasan, 'atasan', 'Tidak disetujui oleh atasan.');

        // Finance accesses the index
        $resFinance = $this->actingAs($finance)->get(route('gomsn.pengajuan.index'));
        $resFinance->assertOk();
        
        // Finance must see approved submission & atasan's direct submission
        $resFinance->assertSee($pApproved->nomor_pengajuan);
        $resFinance->assertSee($pAtasan->nomor_pengajuan);
        
        // Finance MUST NOT see unapproved submission still in menunggu_atasan
        $resFinance->assertDontSee($pUnapproved->nomor_pengajuan);

        // Finance MUST NOT see submission rejected by Atasan
        $resFinance->assertDontSee($pRejectedByAtasan->nomor_pengajuan);

        // Verify Finance Mockup KPI Cards and Filter Bar Elements
        $resFinance->assertSee('Total Pengajuan');
        $resFinance->assertSee('Disetujui');
        $resFinance->assertSee('Ditolak');
        $resFinance->assertSee('Total Dana');
        $resFinance->assertSee('Detail Pengajuan');
        $resFinance->assertSee('Metode Pencairan:');
        $resFinance->assertSee('Semua Jenis');
        $resFinance->assertSee('Semua Divisi');
        $resFinance->assertSee('Tampilkan');
        $resFinance->assertSee('Export');

        // Verify CSV / Excel Export
        $resExport = $this->actingAs($finance)->get(route('gomsn.pengajuan.index', ['export' => 'csv']));
        $resExport->assertOk();
        $this->assertTrue(str_contains($resExport->headers->get('content-type'), 'text/csv') || str_contains($resExport->headers->get('content-type'), 'vnd.ms-excel'));
    }

    public function test_submissions_only_appear_to_designated_atasan_and_not_other_atasans(): void
    {
        $atasanA = $this->createTestUser('Atasan Teknik Hendra', 'atasan_teknik', 'KR8810');
        $atasanB = $this->createTestUser('Atasan NOC Surya', 'atasan_teknik', 'KR8811');
        $karyawan = $this->createTestUser('Karyawan Dedi', 'karyawan_teknik', 'KR8812');

        // Karyawan membuat pengajuan yang ditujukan ke Atasan A
        $response = $this->actingAs($karyawan)->post(route('gomsn.pengajuan.store'), [
            'jenis' => 'DANA',
            'atasan_id' => $atasanA->id,
            'keterangan' => 'Pengajuan khusus untuk Atasan A',
            'items' => [
                [
                    'keperluan_dana' => 'Pembelian Patch Cord',
                    'jumlah_dana' => 120000,
                ],
            ],
        ]);
        $response->assertRedirect();

        $pengajuan = Pengajuan::where('user_id', $karyawan->id)->latest('id')->first();
        $this->assertNotNull($pengajuan);
        $this->assertEquals($atasanA->id, $pengajuan->atasan_id);

        // 1. Atasan A membuka halaman approvals -> HARUS MUNCUL
        $resAtasanA = $this->actingAs($atasanA)->get(route('gomsn.pengajuan.approvals'));
        $resAtasanA->assertOk();
        $resAtasanA->assertSee($pengajuan->nomor_pengajuan);

        // 2. Atasan B membuka halaman approvals -> TIDAK BOLEH MUNCUL
        $resAtasanB = $this->actingAs($atasanB)->get(route('gomsn.pengajuan.approvals'));
        $resAtasanB->assertOk();
        $resAtasanB->assertDontSee($pengajuan->nomor_pengajuan);

        // 3. Atasan B mencoba approve pengajuan Atasan A -> 403 Forbidden
        $resForbidden = $this->actingAs($atasanB)->post(route('gomsn.pengajuan.approve-atasan', $pengajuan->id), [
            'notes' => 'Mencoba approve yang bukan haknya',
        ]);
        $resForbidden->assertForbidden();

        // 4. Atasan A approve pengajuan miliknya -> Berhasil
        $resApprove = $this->actingAs($atasanA)->post(route('gomsn.pengajuan.approve-atasan', $pengajuan->id), [
            'notes' => 'Disetujui oleh Atasan yang dituju',
        ]);
        $resApprove->assertRedirect();
        $this->assertEquals('menunggu_finance', $pengajuan->fresh()->status);
    }

    public function test_overtime_automatically_routes_to_same_division_supervisor_and_blocks_atasan_self_overtime(): void
    {
        $atasanTeknik = $this->createTestUser('Atasan Teknik Wahyu', 'atasan_teknik', 'KR8820', 'Teknik');
        $atasanNoc = $this->createTestUser('Atasan NOC Dimas', 'atasan_noc', 'KR8821', 'NOC');
        $karyawanTeknik = $this->createTestUser('Karyawan FO Ilham', 'karyawan_teknik', 'KR8822', 'Teknik');

        // Create attendance for karyawan on today so checkAttendance passes
        $today = now('Asia/Jakarta')->toDateString();
        \App\Models\Attendance::create([
            'user_id' => $karyawanTeknik->id,
            'date' => $today,
            'status' => 'present',
            'clock_in_at' => "{$today} 08:00:00",
            'clock_out_at' => "{$today} 17:00:00",
            'lat' => -6.9,
            'lng' => 107.6,
        ]);

        // 1. Karyawan membuat pengajuan lembur tanpa memilih atasan
        $resLembur = $this->actingAs($karyawanTeknik)->post(route('gomsn.overtime.store'), [
            'tanggal' => $today,
            'jam_mulai' => '17:30',
            'jam_selesai' => '20:00',
            'alasan' => 'Penyambungan core kabel putus di Pasteur',
            'jenis_lembur' => 'Lembur Hari Kerja',
        ]);
        $resLembur->assertRedirect();

        $lembur = \App\Models\Lembur::where('user_id', $karyawanTeknik->id)->latest('id')->first();
        $this->assertNotNull($lembur);
        
        // Terarah otomatis ke Atasan yang satu divisi (Teknik), BUKAN ke NOC!
        $this->assertEquals($atasanTeknik->id, $lembur->atasan_id);

        // 2. Di akun Atasan Teknik -> Muncul card ringkasan lembur karyawan ini
        $resTeknik = $this->actingAs($atasanTeknik)->get(route('gomsn.overtime.index'));
        $resTeknik->assertOk();
        $resTeknik->assertSee($karyawanTeknik->name);
        $resTeknik->assertSee('Total Lembur');
        // Atasan tidak memiliki tombol ajukan lembur
        $resTeknik->assertDontSee('Ajukan Lembur');

        // Masuk ke halaman detail approval lembur karyawan
        $resDetailTeknik = $this->actingAs($atasanTeknik)->get(route('gomsn.overtime.index', ['user_id' => $karyawanTeknik->id]));
        $resDetailTeknik->assertOk();
        $resDetailTeknik->assertSee($lembur->nomor_lembur);

        // 3. Di akun Atasan NOC -> Lembur karyawan Teknik TIDAK BOLEH muncul
        $resNoc = $this->actingAs($atasanNoc)->get(route('gomsn.overtime.index'));
        $resNoc->assertOk();
        $resNoc->assertDontSee($karyawanTeknik->name);

        // 4. Jika Atasan mencoba mengakses form buat lembur -> Ditolak / Diredirect
        $resCreateAtasan = $this->actingAs($atasanTeknik)->get(route('gomsn.overtime.create'));
        $resCreateAtasan->assertRedirect(route('gomsn.overtime.index'));

        // 5. Atasan Teknik menyetujui lembur karyawannya -> Berhasil
        $resApprove = $this->actingAs($atasanTeknik)->post(route('gomsn.overtime.approve', $lembur->id));
        $resApprove->assertRedirect(route('gomsn.overtime.index'));
        $this->assertEquals('approved_atasan', $lembur->fresh()->status);
    }

    public function test_finance_cannot_create_pengajuan_for_self(): void
    {
        $finance = $this->createTestUser('Rini Finance', 'finance', 'KR8830');

        // 1. Tombol 'Buat Pengajuan Baru' tidak muncul di index Finance
        $resIndex = $this->actingAs($finance)->get(route('gomsn.pengajuan.index'));
        $resIndex->assertOk();
        $resIndex->assertDontSee('Buat Pengajuan Baru');

        // 2. Akses langsung GET ke create form diblokir & diredirect ke index dengan error flash
        $resCreate = $this->actingAs($finance)->get(route('gomsn.pengajuan.create'));
        $resCreate->assertRedirect(route('gomsn.pengajuan.index'));
        $resCreate->assertSessionHas('error');

        // 3. Request POST ke store diblokir & tidak membuat record pengajuan
        $resStore = $this->actingAs($finance)->post(route('gomsn.pengajuan.store'), [
            'jenis' => 'DANA',
            'keterangan' => 'Pengajuan self finance',
            'items' => [
                [
                    'keperluan_dana' => 'Bensin',
                    'jumlah_dana' => 50000,
                ],
            ],
        ]);
        $resStore->assertRedirect(route('gomsn.pengajuan.index'));
        $resStore->assertSessionHas('error');

        $this->assertDatabaseMissing('pengajuan', [
            'user_id' => $finance->id,
            'keperluan' => 'Pengajuan self finance',
        ]);
    }

    public function test_pilih_atasan_dropdown_only_contains_atasan_and_excludes_karyawan_finance_ceo_hrd(): void
    {
        $karyawan = $this->createTestUser('Budi Karyawan', 'karyawan_teknik', 'KR8901');
        $atasan = $this->createTestUser('Hendra Supervisor', 'atasan_teknik', 'KR8902');
        $finance = $this->createTestUser('Siti Finance', 'finance', 'KR8903');
        $ceo = $this->createTestUser('Yudi CEO', 'ceo', 'KR8904');
        $hrd = $this->createTestUser('Amelia HRD', 'hrd', 'KR8905');

        $response = $this->actingAs($karyawan)->get(route('gomsn.pengajuan.create'));
        $response->assertOk();

        // Must see Atasan in the dropdown options
        $response->assertSee('value="' . $atasan->id . '"', false);
        $response->assertSee('Hendra Supervisor');

        // MUST NOT see other non-atasan roles in the dropdown options
        $response->assertDontSee('value="' . $finance->id . '"', false);
        $response->assertDontSee('value="' . $ceo->id . '"', false);
        $response->assertDontSee('value="' . $hrd->id . '"', false);
        $response->assertDontSee('value="' . $karyawan->id . '"', false);
    }

    public function test_karyawan_can_edit_pengajuan_before_approved_by_atasan_and_finance(): void
    {
        $karyawan = $this->createTestUser('Budi Karyawan', 'karyawan_teknik', 'KR8910');
        $atasan = $this->createTestUser('Hendra Supervisor', 'atasan_teknik', 'KR8911');

        $service = app(PengajuanService::class);
        $pengajuan = $service->createPengajuan($karyawan, [
            'jenis' => 'DANA',
            'judul' => 'Operasional Awal',
            'keperluan' => 'Bensin & Tol',
            'atasan_id' => $atasan->id,
        ], [
            [
                'deskripsi' => 'Bensin Motor',
                'jumlah' => 1,
                'harga_satuan' => 50000,
            ],
        ]);
        $service->submitPengajuan($pengajuan, $karyawan);

        $this->assertEquals('menunggu_atasan', $pengajuan->fresh()->status);
        $this->assertEquals(50000, (float) $pengajuan->fresh()->nominal_total);

        // 1. Karyawan can access edit page
        $resEdit = $this->actingAs($karyawan)->get(route('gomsn.pengajuan.edit', $pengajuan));
        $resEdit->assertOk();
        $resEdit->assertSee('Edit Formulir Pengajuan');
        $resEdit->assertSee($pengajuan->nomor_pengajuan);

        // 2. Karyawan submits PUT update
        $resUpdate = $this->actingAs($karyawan)->put(route('gomsn.pengajuan.update', $pengajuan), [
            'jenis' => 'BELI',
            'keterangan' => 'Revisi Pengadaan Kabel UTP',
            'atasan_id' => $atasan->id,
            'items' => [
                [
                    'nama_barang' => 'Kabel UTP Cat 6',
                    'jumlah' => 2,
                    'harga_satuan' => 150000,
                ],
            ],
        ]);

        $resUpdate->assertRedirect(route('gomsn.pengajuan.index'));
        $resUpdate->assertSessionHas('success');

        $updated = $pengajuan->fresh();
        $this->assertEquals('BELI', $updated->jenis);
        $this->assertEquals('Revisi Pengadaan Kabel UTP', $updated->keperluan);
        $this->assertEquals(300000, (float) $updated->nominal_total);
        $this->assertEquals('menunggu_atasan', $updated->status);
        $this->assertCount(1, $updated->details);
        $this->assertEquals('Kabel UTP Cat 6', $updated->details->first()->deskripsi);
        $this->assertEquals(2, $updated->details->first()->jumlah);
    }

    public function test_karyawan_cannot_edit_pengajuan_after_approved_by_atasan_or_finance(): void
    {
        $karyawan = $this->createTestUser('Budi Karyawan', 'karyawan_teknik', 'KR8920');
        $atasan = $this->createTestUser('Hendra Supervisor', 'atasan_teknik', 'KR8921');

        $service = app(PengajuanService::class);
        $pengajuan = $service->createPengajuan($karyawan, [
            'jenis' => 'DANA',
            'judul' => 'Operasional Original',
            'keperluan' => 'Operasional Original',
            'atasan_id' => $atasan->id,
        ], [
            [
                'deskripsi' => 'Bensin Mobil',
                'jumlah' => 1,
                'harga_satuan' => 100000,
            ],
        ]);
        $service->submitPengajuan($pengajuan, $karyawan);

        // Atasan approves it
        $service->approveAtasan($pengajuan, $atasan, 'Approved by Hendra');
        $this->assertEquals('menunggu_finance', $pengajuan->fresh()->status);

        // Karyawan tries to access edit page -> redirect with error
        $resEdit = $this->actingAs($karyawan)->get(route('gomsn.pengajuan.edit', $pengajuan));
        $resEdit->assertRedirect(route('gomsn.pengajuan.index'));
        $resEdit->assertSessionHas('error');

        // Karyawan tries to submit update -> redirect with error and values remain unchanged
        $resUpdate = $this->actingAs($karyawan)->put(route('gomsn.pengajuan.update', $pengajuan), [
            'jenis' => 'BELI',
            'keterangan' => 'Hacked change',
            'atasan_id' => $atasan->id,
            'items' => [
                [
                    'nama_barang' => 'Hacked item',
                    'jumlah' => 10,
                    'harga_satuan' => 999999,
                ],
            ],
        ]);

        $resUpdate->assertRedirect(route('gomsn.pengajuan.index'));
        $resUpdate->assertSessionHas('error');

        $fresh = $pengajuan->fresh();
        $this->assertEquals('DANA', $fresh->jenis);
        $this->assertEquals('Operasional Original', $fresh->keperluan);
        $this->assertEquals(100000, (float) $fresh->nominal_total);
    }

    public function test_user_cannot_edit_other_users_pengajuan(): void
    {
        $karyawanA = $this->createTestUser('Karyawan A', 'karyawan_teknik', 'KR8930');
        $karyawanB = $this->createTestUser('Karyawan B', 'karyawan_teknik', 'KR8931');
        $atasan = $this->createTestUser('Hendra Supervisor', 'atasan_teknik', 'KR8932');

        $service = app(PengajuanService::class);
        $pengajuan = $service->createPengajuan($karyawanA, [
            'jenis' => 'DANA',
            'judul' => 'Operasional A',
            'keperluan' => 'Operasional A',
            'atasan_id' => $atasan->id,
        ], [
            [
                'deskripsi' => 'Konsumsi',
                'jumlah' => 1,
                'harga_satuan' => 50000,
            ],
        ]);
        $service->submitPengajuan($pengajuan, $karyawanA);

        // Karyawan B tries to edit Karyawan A's pengajuan -> 403 Forbidden
        $resEdit = $this->actingAs($karyawanB)->get(route('gomsn.pengajuan.edit', $pengajuan));
        $resEdit->assertStatus(403);

        $resUpdate = $this->actingAs($karyawanB)->put(route('gomsn.pengajuan.update', $pengajuan), [
            'jenis' => 'DANA',
            'keterangan' => 'Hijacked',
            'items' => [['keperluan_dana' => 'Lainnya', 'jumlah_dana' => 100000]],
        ]);
        $resUpdate->assertStatus(403);
    }

    public function test_atasan_can_edit_and_approve_subordinate_overtime(): void
    {
        $atasan = $this->createTestUser('Supervisor Wahyu', 'atasan_teknik', 'KR8940', 'Teknik');
        $karyawan = $this->createTestUser('Teknisi Budi', 'karyawan_teknik', 'KR8941', 'Teknik');

        $today = now('Asia/Jakarta')->toDateString();
        \App\Models\Attendance::create([
            'user_id' => $karyawan->id,
            'date' => $today,
            'status' => 'present',
            'clock_in_at' => "{$today} 08:00:00",
            'clock_out_at' => "{$today} 20:00:00",
            'lat' => -6.9,
            'lng' => 107.6,
        ]);

        $overtimeService = app(\App\Modules\Gomsn\Services\OvertimeService::class);
        $lembur = $overtimeService->createOvertime(
            user: $karyawan,
            tanggal: $today,
            jamMulai: '17:00',
            jamSelesai: '19:00',
            alasan: 'Perbaikan kabel di site B',
            jenisLembur: 'Lembur Hari Kerja',
            requireAttendance: false
        );

        $this->assertEquals(120, $lembur->durasi_menit);

        // 1. Atasan accesses index and sees the employee cards & summary stats
        $resIndex = $this->actingAs($atasan)->get(route('gomsn.overtime.index'));
        $resIndex->assertOk();
        $resIndex->assertSee('Teknisi Budi');
        $resIndex->assertSee('Total Lembur');

        // Atasan masuk ke detail approval lembur karyawan
        $resDetail = $this->actingAs($atasan)->get(route('gomsn.overtime.index', ['user_id' => $karyawan->id]));
        $resDetail->assertOk();
        $resDetail->assertSee($lembur->nomor_lembur);

        // 2. Atasan edits the overtime (adjusting times and reason)
        $resUpdate = $this->actingAs($atasan)->put(route('gomsn.overtime.update', $lembur), [
            'tanggal' => $today,
            'jenis_lembur' => 'Lembur Hari Libur',
            'jam_mulai' => '17:00',
            'jam_selesai' => '20:00',
            'alasan' => 'Disesuaikan oleh atasan: Perbaikan kabel dan splicer di site B',
        ]);
        $resUpdate->assertRedirect();
        $resUpdate->assertSessionHas('status');

        $fresh = $lembur->fresh();
        $this->assertEquals(180, $fresh->durasi_menit);
        $this->assertEquals('Lembur Hari Libur', $fresh->jenis_lembur);
        $this->assertEquals('Disesuaikan oleh atasan: Perbaikan kabel dan splicer di site B', $fresh->alasan);

        // 3. Atasan approves the edited overtime
        $resApprove = $this->actingAs($atasan)->post(route('gomsn.overtime.approve', $lembur));
        $resApprove->assertRedirect();
        $this->assertEquals('approved_atasan', $lembur->fresh()->status);
    }

    public function test_karyawan_can_upload_realization_proof_when_disbursed_by_finance(): void
    {
        Storage::fake('public');

        $karyawan = $this->createTestUser('Harry Setiono', 'karyawan_noc', 'KR31444');
        $atasan = $this->createTestUser('Ipin Aripin', 'atasan_noc', 'KR31440');
        $finance = $this->createTestUser('Siti Finance', 'finance', 'KR31401');

        $service = app(PengajuanService::class);
        $pengajuan = $service->createPengajuan($karyawan, [
            'jenis' => 'DANA',
            'judul' => 'Kasbon Operasional',
            'keperluan' => 'Operasional lapangan NOC',
            'atasan_id' => $atasan->id,
        ], [
            [
                'deskripsi' => 'Kasbon NOC',
                'jumlah' => 1,
                'harga_satuan' => 200000,
            ],
        ]);

        // Submit & approve by atasan
        $service->submitPengajuan($pengajuan, $karyawan);
        $service->approveAtasan($pengajuan->fresh(), $atasan, 'Approved');

        // Disbursed by finance
        $transferProof = UploadedFile::fake()->image('transfer.jpg');
        $path = $transferProof->store('gomsn/transfers', 'public');
        $service->disburseFinance($pengajuan->fresh(), $finance, $path);

        $freshPengajuan = $pengajuan->fresh();
        $this->assertEquals('dicairkan', $freshPengajuan->status);

        // Karyawan opens pengajuan index and views modal data
        $resIndex = $this->actingAs($karyawan)->get(route('gomsn.pengajuan.index'));
        $resIndex->assertOk();
        $resIndex->assertSee('Upload Bukti Foto Realisasi');

        // Karyawan submits realization receipt photo
        $receipt = UploadedFile::fake()->image('nota_struk.jpg');
        $resRealize = $this->actingAs($karyawan)->post(route('gomsn.pengajuan.realize', $freshPengajuan), [
            'nominal_realisasi' => 195000,
            'bukti_realisasi' => $receipt,
            'realisasi_notes' => 'Sisa uang kasbon Rp 5.000 telah dikembalikan',
        ]);

        $resRealize->assertRedirect();
        $resRealize->assertSessionHas('success');

        $completedPengajuan = $freshPengajuan->fresh();
        $this->assertEquals('selesai', $completedPengajuan->status);
        $this->assertEquals(195000, (float) $completedPengajuan->nominal_realisasi);
        $this->assertEquals(5000, (float) $completedPengajuan->selisih_realisasi);
        $this->assertNotNull($completedPengajuan->bukti_realisasi_path);
        $this->assertEquals('Sisa uang kasbon Rp 5.000 telah dikembalikan', $completedPengajuan->realisasi_notes);
        Storage::disk('public')->assertExists($completedPengajuan->bukti_realisasi_path);

        // Karyawan opens index again, now it displays completed realization
        $resAfter = $this->actingAs($karyawan)->get(route('gomsn.pengajuan.index'));
        $resAfter->assertOk();
        $resAfter->assertSee('Bukti Foto Realisasi Pembelian Telah Diunggah');

        // Finance opens index and views modal data, proof photo must appear in Finance modal
        $resFinance = $this->actingAs($finance)->get(route('gomsn.pengajuan.index'));
        $resFinance->assertOk();
        $resFinance->assertSee('Bukti Foto Realisasi Pembelian Telah Diunggah');
        $resFinance->assertSee('Foto Bukti Realisasi / Kwitansi Pembelian');
        $resFinance->assertSee(str_replace('/', '\/', $completedPengajuan->bukti_realisasi_path));
    }
}

