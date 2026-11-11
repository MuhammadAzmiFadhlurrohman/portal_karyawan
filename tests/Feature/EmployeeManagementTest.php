<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'karyawan', 'guard_name' => 'web']);
    }

    protected function getOrCreateAdmin(): User
    {
        $admin = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'super_admin']);
        })->first();

        if (! $admin) {
            $admin = User::create([
                'employee_code' => 'ADMIN999',
                'username' => 'test_admin_unique',
                'name' => 'Super Admin Test',
                'email' => 'admintest999@ptmsn.co.id',
                'phone' => '089999999999',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);
            $admin->assignRole('admin');
        }

        return $admin;
    }

    public function test_non_admin_cannot_access_employee_page(): void
    {
        $karyawan = User::create([
            'employee_code' => 'KR88888',
            'username' => 'test_karyawan_unique',
            'name' => 'Karyawan Test',
            'email' => 'karyawan888@ptmsn.co.id',
            'phone' => '088888888888',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $karyawan->assignRole('karyawan');

        $response = $this->actingAs($karyawan)->get(route('portal.employees.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_access_employee_page(): void
    {
        $admin = $this->getOrCreateAdmin();

        $response = $this->actingAs($admin)->get(route('portal.employees.index'));
        $response->assertStatus(200);
        $response->assertSee('Manajemen Data Karyawan');
    }

    public function test_admin_can_create_update_and_delete_employee(): void
    {
        $admin = $this->getOrCreateAdmin();

        // 1. Create
        $response = $this->actingAs($admin)->post(route('portal.employees.store'), [
            'name' => 'Testing User Baruu',
            'username' => 'userbarutest',
            'email' => 'userbarutest@ptmsn.co.id',
            'phone' => '081239999991',
            'role' => 'karyawan',
            'division' => 'Teknik',
            'position' => 'Teknisi Lapangan',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('portal.employees.index'));
        $this->assertDatabaseHas('users', [
            'username' => 'userbarutest',
            'email' => 'userbarutest@ptmsn.co.id',
        ]);

        $created = User::where('username', 'userbarutest')->first();
        $this->assertNotNull($created);

        // 2. Update
        $updateResponse = $this->actingAs($admin)->put(route('portal.employees.update', $created->id), [
            'name' => 'Testing User Baruu Updated',
            'employee_code' => $created->employee_code,
            'username' => 'userbarutest',
            'email' => 'userbarutest@ptmsn.co.id',
            'phone' => '081239999991',
            'role' => 'karyawan',
            'division' => 'NOC',
            'position' => 'Staff NOC',
            'is_active' => '1',
        ]);

        $updateResponse->assertRedirect(route('portal.employees.index'));
        $this->assertDatabaseHas('users', [
            'id' => $created->id,
            'name' => 'Testing User Baruu Updated',
            'division' => 'NOC',
        ]);

        // 3. Delete
        $deleteResponse = $this->actingAs($admin)->delete(route('portal.employees.destroy', $created->id));
        $deleteResponse->assertRedirect(route('portal.employees.index'));
        $this->assertSoftDeleted('users', [
            'id' => $created->id,
        ]);
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $admin = $this->getOrCreateAdmin();

        $response = $this->actingAs($admin)->delete(route('portal.employees.destroy', $admin->id));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
