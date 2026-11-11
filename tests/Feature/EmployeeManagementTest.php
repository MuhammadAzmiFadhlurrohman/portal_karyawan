<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeManagementTest extends TestCase
{
    public function test_non_admin_cannot_access_employee_page(): void
    {
        $karyawan = User::whereDoesntHave('roles', function ($q) {
            $q->whereIn('name', ['admin', 'super_admin', 'hrd']);
        })->first();

        if (! $karyawan) {
            $this->markTestSkipped('No plain karyawan found.');
        }

        $response = $this->actingAs($karyawan)->get(route('portal.employees.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_access_employee_page(): void
    {
        $admin = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'super_admin']);
        })->first();

        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->get(route('portal.employees.index'));
        $response->assertStatus(200);
        $response->assertSee('Manajemen Data Karyawan');
        $response->assertSee('Tambah Karyawan');
    }

    public function test_admin_can_create_update_and_delete_employee(): void
    {
        $admin = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'super_admin']);
        })->first();

        $this->assertNotNull($admin);

        // 1. Create
        $response = $this->actingAs($admin)->post(route('portal.employees.store'), [
            'name' => 'Testing User Baruu',
            'email' => 'testuserbaruu@msn.co.id',
            'phone' => '089988776655',
            'nik' => '3204998877665544',
            'division' => 'Teknik',
            'position' => 'Teknisi Junior',
            'role' => 'karyawan',
            'bank' => 'BCA',
            'no_rekening' => '543219876',
            'atas_nama' => 'Testing User Baruu',
        ]);

        $response->assertRedirect(route('portal.employees.index'));
        $created = User::where('email', 'testuserbaruu@msn.co.id')->first();
        $this->assertNotNull($created);
        $this->assertEquals('Testing User Baruu', $created->name);
        $this->assertTrue($created->hasRole('karyawan'));
        $this->assertStringStartsWith('KR', $created->employee_code);

        // 2. Update
        $updateResponse = $this->actingAs($admin)->put(route('portal.employees.update', $created->id), [
            'name' => 'Testing User Diupdate',
            'employee_code' => $created->employee_code,
            'username' => $created->username,
            'email' => 'testuserbaruu@msn.co.id',
            'phone' => '089988776655',
            'nik' => '3204998877665544',
            'division' => 'NOC',
            'position' => 'NOC Engineer',
            'role' => 'karyawan',
        ]);

        $updateResponse->assertRedirect(route('portal.employees.index'));
        $created->refresh();
        $this->assertEquals('Testing User Diupdate', $created->name);
        $this->assertEquals('NOC', $created->division);

        // 3. Reset Password
        $resetResponse = $this->actingAs($admin)->post(route('portal.employees.reset-password', $created->id));
        $resetResponse->assertSessionHas('status');

        // 4. Delete
        $deleteResponse = $this->actingAs($admin)->delete(route('portal.employees.destroy', $created->id));
        $deleteResponse->assertRedirect(route('portal.employees.index'));
        $this->assertSoftDeleted('users', ['id' => $created->id]);

        // Cleanup permanently so test database stays clean
        $created->forceDelete();
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $admin = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'super_admin']);
        })->first();

        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->delete(route('portal.employees.destroy', $admin->id));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
