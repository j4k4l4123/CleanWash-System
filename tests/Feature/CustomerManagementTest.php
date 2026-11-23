<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_kasir_can_add_customer_and_view_details(): void
    {
        $kasir = User::factory()->kasir()->create();

        $response = $this->actingAs($kasir)->post('/customers', [
            'nama' => 'Pelanggan Baru',
            'no_hp' => '081234567890',
            'alamat' => 'Jl. Merdeka No. 1',
            'catatan' => 'Pelanggan prioritas',
        ]);

        $customer = Customer::first();
        $this->assertNotNull($customer);
        $this->assertEquals('Pelanggan Baru', $customer->nama);
        $this->assertEquals('CUST-0001', $customer->kode_pelanggan);

        $response->assertRedirect(route('customers.show', $customer));
        $response->assertSessionHas('success');

        $showResponse = $this->actingAs($kasir)->get(route('customers.show', $customer));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Pelanggan Baru');
        $showResponse->assertSee('CUST-0001');
    }

    public function test_customer_show_page_renders_with_order_statistics(): void
    {
        $kasir = User::factory()->kasir()->create();
        $customer = Customer::create([
            'kode_pelanggan' => 'CUST-0001',
            'nama' => 'Budi Santoso',
            'no_hp' => '081234567890',
            'alamat' => 'Jl. Mawar No. 10',
        ]);

        $service = Service::create([
            'nama_layanan' => 'Cuci Kiloan Reguler',
            'jenis' => 'kiloan',
            'harga' => 8000,
            'durasi_jam' => 24,
            'is_active' => true,
        ]);

        Order::create([
            'kode_order' => 'ORD-TEST-001',
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'berat_atau_jumlah' => 3,
            'harga_per_satuan' => 8000,
            'total_harga' => 24000,
            'tgl_masuk' => now(),
            'estimasi_selesai' => now()->addDay(),
            'status_cucian' => 'selesai',
            'status_pembayaran' => 'lunas',
        ]);

        $showResponse = $this->actingAs($kasir)->get(route('customers.show', $customer));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Budi Santoso');
        $showResponse->assertSee('ORD-TEST-001');
        $showResponse->assertSee('24.000');
    }

    public function test_quick_create_customer_via_api(): void
    {
        $kasir = User::factory()->kasir()->create();

        $response = $this->actingAs($kasir)->postJson(route('customers.quick-store'), [
            'nama' => 'Citra Lestari',
            'no_hp' => '087812345678',
            'alamat' => 'Jl. Kenanga 12',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'customer' => [
                'nama' => 'Citra Lestari',
                'kode_pelanggan' => 'CUST-0001',
            ],
        ]);

        $this->assertDatabaseHas('customers', [
            'nama' => 'Citra Lestari',
            'kode_pelanggan' => 'CUST-0001',
        ]);
    }

    public function test_generate_kode_pelanggan_avoids_duplicates(): void
    {
        Customer::create([
            'kode_pelanggan' => 'CUST-0001',
            'nama' => 'Customer 1',
            'no_hp' => '0811111111',
        ]);

        Customer::create([
            'kode_pelanggan' => 'CUST-0002',
            'nama' => 'Customer 2',
            'no_hp' => '0822222222',
        ]);

        $newCode = Customer::generateKodePelanggan();
        $this->assertEquals('CUST-0003', $newCode);
    }

    public function test_quick_create_customer_validation_fails_when_fields_are_missing(): void
    {
        $kasir = User::factory()->kasir()->create();

        $response = $this->actingAs($kasir)->postJson(route('customers.quick-store'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['nama', 'no_hp']);
        $response->assertJsonFragment([
            'nama' => ['Nama pelanggan wajib diisi.'],
            'no_hp' => ['Nomor HP / WhatsApp wajib diisi.'],
        ]);
    }

    public function test_quick_create_customer_with_catatan_and_alamat(): void
    {
        $kasir = User::factory()->kasir()->create();

        $response = $this->actingAs($kasir)->postJson(route('customers.quick-store'), [
            'nama' => 'Ahmad Dahlan',
            'no_hp' => '081234567899',
            'alamat' => 'Jl. Sudirman No. 99',
            'catatan' => 'Alergi deterjen wangi menyengat',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'customer' => [
                'nama' => 'Ahmad Dahlan',
                'no_hp' => '081234567899',
                'alamat' => 'Jl. Sudirman No. 99',
                'catatan' => 'Alergi deterjen wangi menyengat',
            ],
        ]);

        $this->assertDatabaseHas('customers', [
            'nama' => 'Ahmad Dahlan',
            'catatan' => 'Alergi deterjen wangi menyengat',
        ]);
    }

    public function test_quick_customer_modal_is_rendered_on_order_and_customer_pages(): void
    {
        $kasir = User::factory()->kasir()->create();
        $customer = Customer::create([
            'kode_pelanggan' => 'CUST-0001',
            'nama' => 'Budi Santoso',
            'no_hp' => '081234567890',
        ]);
        $service = Service::create([
            'nama_layanan' => 'Cuci Kiloan Reguler',
            'jenis' => 'kiloan',
            'harga' => 8000,
            'durasi_jam' => 24,
            'is_active' => true,
        ]);
        $order = Order::create([
            'kode_order' => 'ORD-TEST-001',
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'berat_atau_jumlah' => 2,
            'harga_per_satuan' => 8000,
            'total_harga' => 16000,
            'tgl_masuk' => now(),
            'estimasi_selesai' => now()->addDay(),
            'status_cucian' => 'diterima',
            'status_pembayaran' => 'belum_lunas',
        ]);

        // Order create page
        $createRes = $this->actingAs($kasir)->get(route('orders.create'));
        $createRes->assertStatus(200);
        $createRes->assertSee('id="customerModal"', false);
        $createRes->assertSee('+ Tambah Pelanggan Baru');

        // Order edit page
        $editRes = $this->actingAs($kasir)->get(route('orders.edit', $order));
        $editRes->assertStatus(200);
        $editRes->assertSee('id="customerModal"', false);
        $editRes->assertSee('+ Tambah Pelanggan Baru');

        // Customers index page
        $indexRes = $this->actingAs($kasir)->get(route('customers.index'));
        $indexRes->assertStatus(200);
        $indexRes->assertSee('id="customerModal"', false);
        $indexRes->assertSee('Tambah Cepat');

        // Dashboard page
        $dashRes = $this->actingAs($kasir)->get(route('dashboard'));
        $dashRes->assertStatus(200);
        $dashRes->assertSee('id="customerModal"', false);
        $dashRes->assertSee('+ Pelanggan Cepat');
    }
}
