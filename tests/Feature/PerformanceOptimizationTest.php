<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PerformanceOptimizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_orders_index_loads_without_lazy_loading_violation(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create([
            'kode_pelanggan' => 'CUST-0001',
            'nama' => 'Budi Santoso',
            'no_hp' => '081234567890',
        ]);
        $service = Service::create([
            'nama_layanan' => 'Cuci Kiloan Regular',
            'jenis' => 'kiloan',
            'harga' => 8000,
            'durasi_jam' => 24,
            'is_active' => true,
        ]);

        Order::create([
            'kode_order' => 'ORD-20261009-001',
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'berat_atau_jumlah' => 3.5,
            'harga_per_satuan' => 8000,
            'total_harga' => 28000,
            'tgl_masuk' => Carbon::now(),
            'estimasi_selesai' => Carbon::now()->addDay(),
            'status_cucian' => 'diterima',
            'status_pembayaran' => 'belum_lunas',
        ]);

        $response = $this->actingAs($user)->get(route('orders.index'));

        $response->assertStatus(200);
        $response->assertViewHas('orders');
    }

    public function test_customer_show_aggregates_and_paginates_orders(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create([
            'kode_pelanggan' => 'CUST-0002',
            'nama' => 'Dewi Lestari',
            'no_hp' => '081987654321',
        ]);
        $service = Service::create([
            'nama_layanan' => 'Cuci Kering Express',
            'jenis' => 'kiloan',
            'harga' => 12000,
            'durasi_jam' => 6,
            'is_active' => true,
        ]);

        for ($i = 1; $i <= 12; $i++) {
            Order::create([
                'kode_order' => sprintf('ORD-20261009-%03d', $i),
                'customer_id' => $customer->id,
                'service_id' => $service->id,
                'berat_atau_jumlah' => 2.0,
                'harga_per_satuan' => 12000,
                'total_harga' => 24000,
                'tgl_masuk' => Carbon::now()->subMinutes($i),
                'estimasi_selesai' => Carbon::now()->addHours(6),
                'status_cucian' => 'diterima',
                'status_pembayaran' => 'lunas',
            ]);
        }

        $response = $this->actingAs($user)->get(route('customers.show', $customer));

        $response->assertStatus(200);
        $response->assertViewHas('totalOrders', 12);
        $response->assertViewHas('totalSpending', 12 * 24000.0);
        $response->assertViewHas('orders');

        /** @var LengthAwarePaginator $paginatedOrders */
        $paginatedOrders = $response->viewData('orders');
        $this->assertEquals(10, $paginatedOrders->perPage());
        $this->assertEquals(12, $paginatedOrders->total());
        $this->assertEquals(2, $paginatedOrders->lastPage());
    }

    public function test_active_services_are_cached_in_order_create(): void
    {
        $user = User::factory()->create();
        Service::create([
            'nama_layanan' => 'Setrika Saja',
            'jenis' => 'kiloan',
            'harga' => 5000,
            'durasi_jam' => 12,
            'is_active' => true,
        ]);

        Cache::forget('active_services');
        $this->assertFalse(Cache::has('active_services'));

        $response = $this->actingAs($user)->get(route('orders.create'));
        $response->assertStatus(200);

        $this->assertTrue(Cache::has('active_services'));
    }
}
