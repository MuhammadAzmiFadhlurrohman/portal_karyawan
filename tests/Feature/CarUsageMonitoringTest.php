<?php

namespace Tests\Feature;

use App\Models\Pengajuan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CarUsageMonitoringTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'super_admin']);
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'hrd']);
        Role::firstOrCreate(['name' => 'karyawan']);
        Role::firstOrCreate(['name' => 'finance']);
    }

    public function test_regular_employee_cannot_access_car_usage_monitoring(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole('karyawan');

        $response = $this->actingAs($employee)->get(route('gomsn.car-usage.index'));
        $response->assertStatus(403);
    }

    public function test_hrd_can_access_car_usage_monitoring_and_see_approved_car_requests(): void
    {
        $hrd = User::factory()->create(['name' => 'Amelia HRD', 'division' => 'HRD']);
        $hrd->assignRole('hrd');

        $driver = User::factory()->create(['name' => 'Rahmat Teknisi', 'division' => 'Teknisi']);
        $driver->assignRole('karyawan');

        // 1. Approved car fuel request (should appear)
        $approvedCar = Pengajuan::create([
            'user_id' => $driver->id,
            'nomor_pengajuan' => 'PJ-202611-0001',
            'jenis' => 'DANA',
            'tipe_pengajuan' => 'dana',
            'kategori' => 'operasional',
            'judul' => 'BBM Mobil Operasional Sigra Cilegon',
            'keperluan' => 'Patroli kabel FO dan perbaikan ODP',
            'nominal_total' => 250000,
            'status' => 'ditransfer',
            'transfer_at' => Carbon::now('Asia/Jakarta'),
            'created_at' => Carbon::now('Asia/Jakarta'),
        ]);

        // 2. Pending car fuel request not yet approved (should NOT appear)
        $pendingCar = Pengajuan::create([
            'user_id' => $driver->id,
            'nomor_pengajuan' => 'PJ-202611-0002',
            'jenis' => 'DANA',
            'tipe_pengajuan' => 'dana',
            'kategori' => 'operasional',
            'judul' => 'Bensin Mobil Avanza',
            'keperluan' => 'Survey jaringan baru',
            'nominal_total' => 150000,
            'status' => 'menunggu_atasan',
            'created_at' => Carbon::now('Asia/Jakarta'),
        ]);

        // 3. Approved request but NOT related to car/fuel (should NOT appear)
        $otherApproved = Pengajuan::create([
            'user_id' => $driver->id,
            'nomor_pengajuan' => 'PJ-202611-0003',
            'jenis' => 'DANA',
            'tipe_pengajuan' => 'dana',
            'kategori' => 'operasional',
            'judul' => 'Pembelian Patch Cord dan SFP',
            'keperluan' => 'Penggantian modul OLT',
            'nominal_total' => 500000,
            'status' => 'ditransfer',
            'transfer_at' => Carbon::now('Asia/Jakarta'),
            'created_at' => Carbon::now('Asia/Jakarta'),
        ]);

        $response = $this->actingAs($hrd)->get(route('gomsn.car-usage.index'));
        $response->assertStatus(200);
        $response->assertSee('BBM Mobil Operasional Sigra Cilegon');
        $response->assertSee('PJ-202611-0001');
        $response->assertDontSee('PJ-202611-0002');
        $response->assertDontSee('PJ-202611-0003');
    }

    public function test_admin_can_export_car_usage_to_csv(): void
    {
        $admin = User::factory()->create(['name' => 'Admin Utama']);
        $admin->assignRole('admin');

        $driver = User::factory()->create(['name' => 'Budi Driver', 'division' => 'Umum']);
        $driver->assignRole('karyawan');

        Pengajuan::create([
            'user_id' => $driver->id,
            'nomor_pengajuan' => 'PJ-202611-0099',
            'jenis' => 'DANA',
            'tipe_pengajuan' => 'dana',
            'kategori' => 'operasional',
            'judul' => 'Biaya Bensin Mobil Ertiga',
            'keperluan' => 'Antar logistik ke cabang',
            'nominal_total' => 200000,
            'status' => 'selesai',
            'transfer_at' => Carbon::now('Asia/Jakarta'),
            'created_at' => Carbon::now('Asia/Jakarta'),
        ]);

        $response = $this->actingAs($admin)->get(route('gomsn.car-usage.index', ['export' => 'csv']));
        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type') ?? '', 'text/csv'));
    }
}
