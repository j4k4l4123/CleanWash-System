<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkerRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_pekerja_can_login_and_is_redirected_to_orders_index(): void
    {
        $pekerja = User::factory()->pekerja()->create([
            'email' => 'pekerja@laundry.test',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'pekerja@laundry.test',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('orders.index'));
        $this->assertAuthenticatedAs($pekerja);
    }

    public function test_pekerja_accessing_dashboard_is_redirected_to_orders_index(): void
    {
        $pekerja = User::factory()->pekerja()->create();

        $response = $this->actingAs($pekerja)->get('/dashboard');
        $response->assertRedirect(route('orders.index'));

        $rootResponse = $this->actingAs($pekerja)->get('/');
        $rootResponse->assertRedirect(route('orders.index'));
    }

    public function test_pekerja_cannot_access_restricted_modules(): void
    {
        $pekerja = User::factory()->pekerja()->create();

        $this->actingAs($pekerja)->get('/customers')->assertStatus(403);
        $this->actingAs($pekerja)->get('/services')->assertStatus(403);
        $this->actingAs($pekerja)->get('/payments')->assertStatus(403);
        $this->actingAs($pekerja)->get('/users')->assertStatus(403);
        $this->actingAs($pekerja)->get('/orders/create')->assertStatus(403);
    }

    public function test_pekerja_only_sees_orders_with_status_diterima(): void
    {
        $pekerja = User::factory()->pekerja()->create();
        $customer = Customer::create([
            'kode_pelanggan' => 'CUST-001',
            'nama' => 'Budi Pelanggan',
            'no_hp' => '0812345678',
        ]);
        $service = Service::create([
            'nama_layanan' => 'Cuci Kering',
            'jenis' => 'kiloan',
            'harga' => 6000,
            'durasi_jam' => 24,
            'is_active' => true,
        ]);

        $orderDiterima = Order::create([
            'kode_order' => 'ORD-TEST-001',
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'berat_atau_jumlah' => 2,
            'harga_per_satuan' => 6000,
            'total_harga' => 12000,
            'tgl_masuk' => now(),
            'estimasi_selesai' => now()->addDay(),
            'status_cucian' => 'diterima',
            'status_pembayaran' => 'belum_lunas',
        ]);

        $orderSelesai = Order::create([
            'kode_order' => 'ORD-TEST-002',
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'berat_atau_jumlah' => 3,
            'harga_per_satuan' => 6000,
            'total_harga' => 18000,
            'tgl_masuk' => now(),
            'estimasi_selesai' => now()->addDay(),
            'status_cucian' => 'selesai',
            'status_pembayaran' => 'lunas',
        ]);

        $response = $this->actingAs($pekerja)->get('/orders');
        $response->assertStatus(200);
        $response->assertSee('ORD-TEST-001');
        $response->assertDontSee('ORD-TEST-002');

        // Buka detail order Diterima -> 200 OK
        $this->actingAs($pekerja)->get(route('orders.show', $orderDiterima))->assertStatus(200);

        // Buka detail order Selesai -> 403 Forbidden
        $this->actingAs($pekerja)->get(route('orders.show', $orderSelesai))->assertStatus(403);
    }

    public function test_admin_can_create_and_update_pekerja_user(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'Staf Baru',
            'email' => 'staf@laundry.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'pekerja',
            'no_hp' => '081233344455',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'staf@laundry.test',
            'role' => 'pekerja',
        ]);

        $user = User::where('email', 'staf@laundry.test')->first();

        $updateResponse = $this->actingAs($admin)->put(route('users.update', $user), [
            'name' => 'Staf Diperbarui',
            'email' => 'staf@laundry.test',
            'role' => 'kasir',
            'no_hp' => '081233344455',
        ]);

        $updateResponse->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'kasir',
        ]);
    }

    public function test_pekerja_views_do_not_contain_action_buttons(): void
    {
        $pekerja = User::factory()->pekerja()->create();
        $customer = Customer::create([
            'kode_pelanggan' => 'CUST-002',
            'nama' => 'Pelanggan UI',
            'no_hp' => '0812345678',
        ]);
        $service = Service::create([
            'nama_layanan' => 'Cuci Kering',
            'jenis' => 'kiloan',
            'harga' => 6000,
            'durasi_jam' => 24,
            'is_active' => true,
        ]);

        $order = Order::create([
            'kode_order' => 'ORD-UI-001',
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'berat_atau_jumlah' => 2,
            'harga_per_satuan' => 6000,
            'total_harga' => 12000,
            'tgl_masuk' => now(),
            'estimasi_selesai' => now()->addDay(),
            'status_cucian' => 'diterima',
            'status_pembayaran' => 'belum_lunas',
        ]);

        $indexResponse = $this->actingAs($pekerja)->get('/orders');
        $indexResponse->assertStatus(200);
        $indexResponse->assertDontSee('Terima Order Baru');
        $indexResponse->assertDontSee('href="'.route('customers.index').'"', false);

        $showResponse = $this->actingAs($pekerja)->get(route('orders.show', $order));
        $showResponse->assertStatus(200);
        $showResponse->assertDontSee('Update Status Cucian');
        $showResponse->assertDontSee('Konfirmasi Pembayaran Lunas');
        $showResponse->assertDontSee(route('orders.edit', $order));
    }
}
