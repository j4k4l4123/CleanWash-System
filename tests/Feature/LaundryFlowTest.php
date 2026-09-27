<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaundryFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test laundry dashboard access for authenticated user.
     */
    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('CleanWash');
    }

    public function test_order_statuses_has_only_three_valid_statuses(): void
    {
        $statuses = Order::listStatuses();

        $this->assertEquals(['diterima', 'siap_diambil', 'selesai'], array_keys($statuses));
        $this->assertArrayNotHasKey('proses_cuci', $statuses);
        $this->assertArrayNotHasKey('pengeringan', $statuses);
        $this->assertArrayNotHasKey('setrika', $statuses);
    }

    public function test_can_update_status_to_siap_diambil_and_selesai(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create(['kode_pelanggan' => 'CUST-001', 'nama' => 'Siti', 'no_hp' => '081234567891']);
        $service = Service::create([
            'kode_layanan' => 'CKR',
            'nama_layanan' => 'Cuci Kering',
            'kategori' => 'kiloan',
            'harga' => 7000,
            'satuan' => 'kg',
            'estimasi_hari' => 2,
            'estimasi_jam' => 0,
            'is_active' => true,
        ]);
        $order = Order::create([
            'kode_order' => 'ORD-TEST-001',
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'berat_atau_jumlah' => 2,
            'harga_per_satuan' => 7000,
            'total_harga' => 14000,
            'tgl_masuk' => now(),
            'estimasi_selesai' => now()->addDays(2),
            'status_cucian' => 'diterima',
            'status_pembayaran' => 'belum_lunas',
        ]);

        // Update to siap_diambil
        $response = $this->actingAs($user)->post("/orders/{$order->id}/update-status", [
            'status_cucian' => 'siap_diambil',
            'keterangan' => 'Selesai dicuci dan siap diambil',
        ]);

        $response->assertRedirect("/orders/{$order->id}");
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status_cucian' => 'siap_diambil',
        ]);

        // Update to selesai
        $response = $this->actingAs($user)->post("/orders/{$order->id}/update-status", [
            'status_cucian' => 'selesai',
            'keterangan' => 'Sudah diambil pelanggan',
        ]);

        $response->assertRedirect("/orders/{$order->id}");
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status_cucian' => 'selesai',
        ]);
    }

    public function test_cannot_update_to_removed_statuses(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create(['kode_pelanggan' => 'CUST-002', 'nama' => 'Budi', 'no_hp' => '081234567892']);
        $service = Service::create([
            'kode_layanan' => 'CKR2',
            'nama_layanan' => 'Cuci Kering',
            'kategori' => 'kiloan',
            'harga' => 7000,
            'satuan' => 'kg',
            'estimasi_hari' => 2,
            'estimasi_jam' => 0,
            'is_active' => true,
        ]);
        $order = Order::create([
            'kode_order' => 'ORD-TEST-002',
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'berat_atau_jumlah' => 2,
            'harga_per_satuan' => 7000,
            'total_harga' => 14000,
            'tgl_masuk' => now(),
            'estimasi_selesai' => now()->addDays(2),
            'status_cucian' => 'diterima',
            'status_pembayaran' => 'belum_lunas',
        ]);

        foreach (['proses_cuci', 'pengeringan', 'setrika'] as $invalidStatus) {
            $response = $this->actingAs($user)->post("/orders/{$order->id}/update-status", [
                'status_cucian' => $invalidStatus,
            ]);

            $response->assertSessionHasErrors('status_cucian');
        }
    }

    public function test_order_show_page_displays_only_three_steps(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create(['kode_pelanggan' => 'CUST-003', 'nama' => 'Andi', 'no_hp' => '081234567893']);
        $service = Service::create([
            'kode_layanan' => 'CKR3',
            'nama_layanan' => 'Cuci Kering',
            'kategori' => 'kiloan',
            'harga' => 7000,
            'satuan' => 'kg',
            'estimasi_hari' => 2,
            'estimasi_jam' => 0,
            'is_active' => true,
        ]);
        $order = Order::create([
            'kode_order' => 'ORD-TEST-003',
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'berat_atau_jumlah' => 2,
            'harga_per_satuan' => 7000,
            'total_harga' => 14000,
            'tgl_masuk' => now(),
            'estimasi_selesai' => now()->addDays(2),
            'status_cucian' => 'diterima',
            'status_pembayaran' => 'belum_lunas',
        ]);

        $response = $this->actingAs($user)->get("/orders/{$order->id}");

        $response->assertStatus(200);
        $response->assertSee('1. Diterima');
        $response->assertSee('2. Siap Diambil');
        $response->assertSee('3. Selesai');
        $response->assertDontSee('2. Dicuci');
        $response->assertDontSee('3. Dikeringkan');
        $response->assertDontSee('4. Disetrika');
    }
}
