<?php

namespace Tests\Feature;

use App\Models\Odp;
use App\Models\Olt;
use App\Models\Pon;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NetworkOdpModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $staffUser;
    protected Olt $olt;
    protected Pon $pon;
    protected Odp $odp;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'atasan_teknik', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'teknisi', 'guard_name' => 'web']);

        $this->adminUser = User::factory()->create([
            'email' => 'admin.network@test.local',
            'employee_code' => 'KRNET01',
            'password_changed_at' => Carbon::now(),
            'is_active' => true,
        ]);
        $this->adminUser->assignRole('super_admin');

        $this->staffUser = User::factory()->create([
            'email' => 'staff.teknisi@test.local',
            'employee_code' => 'KRSTAFF01',
            'password_changed_at' => Carbon::now(),
            'is_active' => true,
        ]);
        $this->staffUser->assignRole('teknisi');

        $this->olt = Olt::create([
            'nama_olt' => 'OLT MSN',
            'kode' => 'MSN',
            'lokasi' => 'Bandung',
            'is_active' => true,
        ]);

        $this->pon = Pon::create([
            'olt_id' => $this->olt->id,
            'nama_pon' => 'PON 3 / MS SUFIA',
            'port_max' => 8,
            'port_terisi' => 0,
        ]);

        $this->odp = Odp::create([
            'pon_id' => $this->pon->id,
            'nama_odp' => 'JEMBATAN TOL',
            'port_max' => 8,
            'port_terpakai' => 0,
            'latitude' => -6.92976,
            'longitude' => 107.5933,
        ]);
    }

    public function test_can_view_network_pon_index(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('gomsn.network.index', ['olt_id' => $this->olt->id]));

        $response->assertStatus(200);
        $response->assertSee('Infrastruktur Jaringan FTTH');
        $response->assertSee('Tambah PON Baru');
        $response->assertSee('PON 3 / MS SUFIA');
    }

    public function test_staff_cannot_create_pon_gets_403(): void
    {
        $response = $this->actingAs($this->staffUser)->post(route('gomsn.network.pons.store', $this->olt), [
            'nama_pon' => 'PON ILLEGAL',
            'port_max' => 8,
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_create_and_manage_pon(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('gomsn.network.pons.store', $this->olt), [
            'nama_pon' => 'PON TEST UNIT',
            'port_max' => 8,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pons', [
            'olt_id' => $this->olt->id,
            'nama_pon' => 'PON TEST UNIT',
            'port_max' => 8,
        ]);
    }

    public function test_admin_can_view_and_manage_odp(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('gomsn.network.pons.show', [
            'olt' => $this->olt->id,
            'pon' => $this->pon->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Tambah ODP Baru');
        $response->assertSee('JEMBATAN TOL');

        // Add ODP
        $addResponse = $this->actingAs($this->adminUser)->post(route('gomsn.network.odps.store', $this->pon), [
            'nama_odp' => 'ODP TEST UNIT',
            'port_max' => 8,
            'latitude' => -6.91234,
            'longitude' => 107.61234,
        ]);

        $addResponse->assertRedirect();
        $this->assertDatabaseHas('odps', [
            'pon_id' => $this->pon->id,
            'nama_odp' => 'ODP TEST UNIT',
        ]);
    }

    public function test_staff_cannot_create_odp_gets_403(): void
    {
        $response = $this->actingAs($this->staffUser)->post(route('gomsn.network.odps.store', $this->pon), [
            'nama_odp' => 'ODP ILLEGAL',
            'port_max' => 8,
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_add_customer_and_record_history(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('gomsn.network.odps.show', [
            'olt' => $this->olt->id,
            'pon' => $this->pon->id,
            'odp' => $this->odp->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Tambah User Baru');

        // Add customer
        $addCust = $this->actingAs($this->adminUser)->post(route('gomsn.network.customers.store', $this->odp), [
            'nomor_internet' => 'TEST-NET-999',
            'nama_user' => 'Customer Test Unit',
            'keterangan' => 'Unit testing connection',
        ]);

        $addCust->assertRedirect();
        $this->assertDatabaseHas('odp_customers', [
            'odp_id' => $this->odp->id,
            'nomor_internet' => 'TEST-NET-999',
            'nama_user' => 'Customer Test Unit',
        ]);

        $this->assertDatabaseHas('odp_user_histories', [
            'odp_id' => $this->odp->id,
            'nomor_internet' => 'TEST-NET-999',
            'aksi' => 'tambah',
        ]);
    }

    public function test_staff_can_view_but_cannot_add_customer(): void
    {
        $response = $this->actingAs($this->staffUser)->get(route('gomsn.network.odps.show', [
            'olt' => $this->olt->id,
            'pon' => $this->pon->id,
            'odp' => $this->odp->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Mode Monitoring');
        $response->assertDontSee('Tambah User Baru ke ODP');

        $addCust = $this->actingAs($this->staffUser)->post(route('gomsn.network.customers.store', $this->odp), [
            'nomor_internet' => 'ILLEGAL-999',
            'nama_user' => 'Customer Illegal',
        ]);

        $addCust->assertStatus(403);
    }
}
