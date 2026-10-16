<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_reports(): void
    {
        $this->get('/reports')->assertRedirect('/login');
        $this->get('/reports/print')->assertRedirect('/login');
    }

    public function test_pekerja_cannot_access_reports(): void
    {
        $pekerja = User::factory()->pekerja()->create();

        $this->actingAs($pekerja)->get('/reports')->assertStatus(403);
        $this->actingAs($pekerja)->get('/reports/print')->assertStatus(403);
    }

    public function test_kasir_and_admin_can_access_reports(): void
    {
        $kasir = User::factory()->kasir()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($kasir)->get('/reports')->assertStatus(200)->assertSee('Laporan');
        $this->actingAs($kasir)->get('/reports/print')->assertStatus(200)->assertSee('CLEANWASH MANAGEMENT LAUNDRY');

        $this->actingAs($admin)->get('/reports')->assertStatus(200)->assertSee('Laporan');
        $this->actingAs($admin)->get('/reports/print')->assertStatus(200)->assertSee('CLEANWASH MANAGEMENT LAUNDRY');
    }

    public function test_report_calculates_financial_metrics_correctly(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = Customer::create([
            'kode_pelanggan' => 'CUST-001',
            'nama' => 'Budi Santoso',
            'no_hp' => '081234567890',
        ]);
        $service = Service::create([
            'nama_layanan' => 'Cuci Komplit',
            'jenis' => 'kiloan',
            'harga' => 10000,
            'durasi_jam' => 24,
            'is_active' => true,
        ]);

        $orderLunas = Order::create([
            'kode_order' => 'ORD-TEST-001',
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'berat_atau_jumlah' => 3,
            'harga_per_satuan' => 10000,
            'total_harga' => 30000,
            'tgl_masuk' => Carbon::now(),
            'estimasi_selesai' => Carbon::now()->addDay(),
            'status_cucian' => 'selesai',
            'status_pembayaran' => 'lunas',
            'metode_pembayaran' => 'tunai',
        ]);

        Payment::create([
            'order_id' => $orderLunas->id,
            'kode_pembayaran' => 'INV-TEST-001',
            'jumlah_bayar' => 30000,
            'uang_diterima' => 50000,
            'kembalian' => 20000,
            'metode_pembayaran' => 'tunai',
            'status' => 'lunas',
            'tgl_bayar' => Carbon::now(),
        ]);

        $orderBelumLunas = Order::create([
            'kode_order' => 'ORD-TEST-002',
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'berat_atau_jumlah' => 2,
            'harga_per_satuan' => 10000,
            'total_harga' => 20000,
            'tgl_masuk' => Carbon::now(),
            'estimasi_selesai' => Carbon::now()->addDay(),
            'status_cucian' => 'diterima',
            'status_pembayaran' => 'belum_lunas',
        ]);

        $response = $this->actingAs($admin)->get('/reports?period=today');

        $response->assertStatus(200);
        $response->assertSee('Rp 30.000'); // Omzet lunas
        $response->assertSee('Rp 20.000'); // Piutang belum lunas
        $response->assertSee('ORD-TEST-001');
        $response->assertSee('ORD-TEST-002');
    }

    public function test_cetak_nota_cucian_and_kwitansi_accessible_by_kasir(): void
    {
        $kasir = User::factory()->kasir()->create();
        $customer = Customer::create([
            'kode_pelanggan' => 'CUST-002',
            'nama' => 'Dewi Lestari',
            'no_hp' => '089876543210',
        ]);
        $service = Service::create([
            'nama_layanan' => 'Cuci Kilat',
            'jenis' => 'kiloan',
            'harga' => 15000,
            'durasi_jam' => 6,
            'is_active' => true,
        ]);

        $order = Order::create([
            'kode_order' => 'ORD-PRINT-001',
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'berat_atau_jumlah' => 2,
            'harga_per_satuan' => 15000,
            'total_harga' => 30000,
            'tgl_masuk' => Carbon::now(),
            'estimasi_selesai' => Carbon::now()->addHours(6),
            'status_cucian' => 'diterima',
            'status_pembayaran' => 'lunas',
            'metode_pembayaran' => 'qris',
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'kode_pembayaran' => 'INV-PRINT-001',
            'jumlah_bayar' => 30000,
            'uang_diterima' => 30000,
            'kembalian' => 0,
            'metode_pembayaran' => 'qris',
            'status' => 'lunas',
            'tgl_bayar' => Carbon::now(),
        ]);

        // Kasir can print order receipt
        $notaResponse = $this->actingAs($kasir)->get(route('orders.print', $order));
        $notaResponse->assertStatus(200);
        $notaResponse->assertSee('ORD-PRINT-001');
        $notaResponse->assertSee('Dewi Lestari');
        $notaResponse->assertSee('CLEANWASH LAUNDRY');

        // Kasir can print payment receipt (kwitansi)
        $kwitansiResponse = $this->actingAs($kasir)->get(route('payments.receipt', $payment));
        $kwitansiResponse->assertStatus(200);
        $kwitansiResponse->assertSee('INV-PRINT-001');
        $kwitansiResponse->assertSee('Dewi Lestari');

        // Order detail view shows Cetak Nota button
        $detailResponse = $this->actingAs($kasir)->get(route('orders.show', $order));
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('Cetak Nota');
        $detailResponse->assertSee(route('orders.print', $order));

        // Order list view shows Nota button
        $indexResponse = $this->actingAs($kasir)->get(route('orders.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Nota');
        $indexResponse->assertSee(route('orders.print', $order));
    }
}
