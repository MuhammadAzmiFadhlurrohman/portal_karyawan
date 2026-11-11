<?php

namespace Tests\Feature;

use App\Models\MasterMaterial;
use App\Models\MaterialMovement;
use App\Models\Pengajuan;
use App\Models\User;
use App\Modules\Gomsn\Services\PengajuanService;
use Database\Seeders\RoleAndDivisionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FinanceInventoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndDivisionSeeder::class);
    }

    protected function createFinanceUser(): User
    {
        $user = User::factory()->create([
            'username' => 'finance_test_' . uniqid(),
            'email' => 'finance_' . uniqid() . '@example.com',
            'division' => 'Finance',
        ]);
        $user->assignRole('finance');

        return $user;
    }

    protected function createEmployeeUser(): User
    {
        $user = User::factory()->create([
            'username' => 'karyawan_test_' . uniqid(),
            'email' => 'karyawan_' . uniqid() . '@example.com',
            'division' => 'Teknik',
        ]);
        $user->assignRole('teknisi');

        return $user;
    }

    public function test_non_finance_user_is_forbidden_from_inventory_page(): void
    {
        $employee = $this->createEmployeeUser();

        $response = $this->actingAs($employee)->get(route('gomsn.inventory.index'));
        $response->assertStatus(403);
    }

    public function test_finance_user_can_access_inventory_page_and_view_materials(): void
    {
        $finance = $this->createFinanceUser();

        MasterMaterial::create([
            'kode' => 'KBL-FO-12C',
            'nama' => 'Kabel FO 12 Core ADSS',
            'satuan' => 'meter',
            'stok_tersedia' => 150,
            'min_stok' => 20,
            'is_active' => true,
        ]);

        $response = $this->actingAs($finance)->get(route('gomsn.inventory.index'));
        $response->assertStatus(200);
        $response->assertSee('Stok Barang Kantor');
        $response->assertSee('KBL-FO-12C');
        $response->assertSee('Kabel FO 12 Core ADSS');
    }

    public function test_finance_user_can_create_new_material_with_initial_stock(): void
    {
        $finance = $this->createFinanceUser();

        $response = $this->actingAs($finance)->post(route('gomsn.inventory.store'), [
            'kode' => 'PATCH-LC-SC',
            'nama' => 'Patchcord LC to SC Simplex 5m',
            'satuan' => 'pcs',
            'stok_tersedia' => 25,
            'min_stok' => 5,
            'is_active' => 1,
            'notes' => 'Pengadaan stok awal kantor',
        ]);

        $response->assertRedirect(route('gomsn.inventory.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('master_materials', [
            'kode' => 'PATCH-LC-SC',
            'nama' => 'Patchcord LC to SC Simplex 5m',
            'satuan' => 'pcs',
            'stok_tersedia' => 25,
            'min_stok' => 5,
            'is_active' => true,
        ]);

        $material = MasterMaterial::where('kode', 'PATCH-LC-SC')->first();
        $this->assertNotNull($material);

        $this->assertDatabaseHas('material_movements', [
            'master_material_id' => $material->id,
            'user_id' => $finance->id,
            'type' => 'IN',
            'quantity' => 25,
            'stock_before' => 0,
            'stock_after' => 25,
        ]);
    }

    public function test_finance_user_can_update_material(): void
    {
        $finance = $this->createFinanceUser();

        $material = MasterMaterial::create([
            'kode' => 'JC-12C',
            'nama' => 'Joint Closure Dome 12 Core',
            'satuan' => 'pcs',
            'stok_tersedia' => 10,
            'min_stok' => 3,
            'is_active' => true,
        ]);

        $response = $this->actingAs($finance)->put(route('gomsn.inventory.update', $material), [
            'kode' => 'JC-12C-V2',
            'nama' => 'Joint Closure Dome 12 Core V2 Waterproof',
            'satuan' => 'pcs',
            'min_stok' => 5,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('gomsn.inventory.index'));
        $this->assertDatabaseHas('master_materials', [
            'id' => $material->id,
            'kode' => 'JC-12C-V2',
            'nama' => 'Joint Closure Dome 12 Core V2 Waterproof',
            'min_stok' => 5,
        ]);
    }

    public function test_finance_user_can_restock_material(): void
    {
        $finance = $this->createFinanceUser();

        $material = MasterMaterial::create([
            'kode' => 'SLEEVE-60',
            'nama' => 'Protection Sleeve 60mm',
            'satuan' => 'pcs',
            'stok_tersedia' => 100,
            'min_stok' => 20,
            'is_active' => true,
        ]);

        $response = $this->actingAs($finance)->post(route('gomsn.inventory.restock', $material), [
            'quantity' => 50,
            'notes' => 'Restock faktur #INV-9981',
        ]);

        $response->assertRedirect(route('gomsn.inventory.index'));
        $material->refresh();
        $this->assertEquals(150, $material->stok_tersedia);

        $this->assertDatabaseHas('material_movements', [
            'master_material_id' => $material->id,
            'user_id' => $finance->id,
            'type' => 'IN',
            'quantity' => 50,
            'stock_before' => 100,
            'stock_after' => 150,
            'notes' => 'Restock faktur #INV-9981',
        ]);
    }

    public function test_finance_user_can_adjust_physical_stock_opname(): void
    {
        $finance = $this->createFinanceUser();

        $material = MasterMaterial::create([
            'kode' => 'ADAPTOR-SC',
            'nama' => 'Adaptor SC-UPC Simplex',
            'satuan' => 'pcs',
            'stok_tersedia' => 40,
            'min_stok' => 10,
            'is_active' => true,
        ]);

        $response = $this->actingAs($finance)->post(route('gomsn.inventory.adjust', $material), [
            'actual_stock' => 38,
            'reason' => 'Hasil opname fisik ditemukan 2 pcs rusak pecah',
        ]);

        $response->assertRedirect(route('gomsn.inventory.index'));
        $material->refresh();
        $this->assertEquals(38, $material->stok_tersedia);

        $this->assertDatabaseHas('material_movements', [
            'master_material_id' => $material->id,
            'user_id' => $finance->id,
            'type' => 'ADJUSTMENT',
            'quantity' => 2,
            'stock_before' => 40,
            'stock_after' => 38,
        ]);
    }

    public function test_finance_user_can_fetch_movements_json(): void
    {
        $finance = $this->createFinanceUser();

        $material = MasterMaterial::create([
            'kode' => 'TANG-CRIMP',
            'nama' => 'Tang Crimping RJ45',
            'satuan' => 'unit',
            'stok_tersedia' => 5,
            'min_stok' => 2,
            'is_active' => true,
        ]);

        MaterialMovement::create([
            'master_material_id' => $material->id,
            'user_id' => $finance->id,
            'type' => 'IN',
            'quantity' => 5,
            'stock_before' => 0,
            'stock_after' => 5,
            'notes' => 'Initial stock unit',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($finance)->getJson(route('gomsn.inventory.movements', $material));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'material' => ['id', 'kode', 'nama', 'satuan', 'stok_tersedia', 'min_stok'],
            'movements' => [
                '*' => ['id', 'type', 'quantity', 'stock_before', 'stock_after', 'notes', 'user_name'],
            ],
        ]);
    }

    public function test_stok_pengajuan_release_deducts_material_inventory(): void
    {
        $finance = $this->createFinanceUser();
        $employee = $this->createEmployeeUser();

        $material = MasterMaterial::create([
            'kode' => 'SFP-1G',
            'nama' => 'Module SFP 1G 1310nm 20KM',
            'satuan' => 'pcs',
            'stok_tersedia' => 10,
            'min_stok' => 2,
            'is_active' => true,
        ]);

        // Employee creates a STOK pengajuan
        $service = app(PengajuanService::class);
        $pengajuan = $service->createPengajuan($employee, [
            'jenis' => 'STOK',
            'kategori' => 'Operasional',
            'tujuan' => 'Penggantian modul SFP link POP',
        ], [
            [
                'deskripsi' => 'Module SFP 1G 1310nm 20KM',
                'jumlah' => 2,
                'harga_satuan' => 0,
                'master_material_id' => $material->id,
            ],
        ]);

        // Simulate supervisor approval leading to menunggu_finance
        $pengajuan->status = 'menunggu_finance';
        $pengajuan->save();

        // Finance releases stock
        $service->releaseStockFinance($pengajuan, $finance, 'Barang diserahkan langsung di gudang');

        $material->refresh();
        $this->assertEquals(8, $material->stok_tersedia);

        $this->assertDatabaseHas('material_movements', [
            'master_material_id' => $material->id,
            'user_id' => $employee->id,
            'type' => 'OUT',
            'quantity' => 2,
            'stock_before' => 10,
            'stock_after' => 8,
        ]);
    }

    public function test_finance_user_can_delete_material(): void
    {
        $finance = $this->createFinanceUser();

        $material = MasterMaterial::create([
            'kode' => 'ROUTER-TEST-DEL',
            'nama' => 'Router Test Hapus',
            'satuan' => 'unit',
            'stok_tersedia' => 3,
            'min_stok' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($finance)->delete(route('gomsn.inventory.destroy', $material));
        $response->assertRedirect(route('gomsn.inventory.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('master_materials', [
            'id' => $material->id,
        ]);
    }
}
