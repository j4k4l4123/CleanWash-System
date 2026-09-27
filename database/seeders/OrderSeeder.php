<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\Payment;
use App\Models\Service;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::all();
        $services = Service::all();

        if ($customers->isEmpty() || $services->isEmpty()) {
            return;
        }

        $now = now();

        $sampleOrders = [
            [
                'kode_order' => 'ORD-20260918-001',
                'customer_id' => $customers[0]->id,
                'service_id' => $services[0]->id, // Kiloan Reguler 7000
                'berat_atau_jumlah' => 4.5,
                'harga_per_satuan' => 7000,
                'total_harga' => 31500,
                'tgl_masuk' => $now->copy()->subHours(5),
                'estimasi_selesai' => $now->copy()->addHours(43),
                'status_cucian' => 'diterima',
                'status_pembayaran' => 'belum_lunas',
                'metode_pembayaran' => null,
                'catatan' => 'Baju warna putih tolong dipisah, jangan pakai pelembut berlebih.',
                'logs' => [
                    ['status' => 'diterima', 'keterangan' => 'Pakaian diterima di kasir laundry, penimbangan 4.5 Kg.'],
                ],
            ],
            [
                'kode_order' => 'ORD-20260918-002',
                'customer_id' => $customers[1]->id,
                'service_id' => $services[1]->id, // Kiloan Kilat 10000
                'berat_atau_jumlah' => 3.0,
                'harga_per_satuan' => 10000,
                'total_harga' => 30000,
                'tgl_masuk' => $now->copy()->subHours(8),
                'estimasi_selesai' => $now->copy()->addHours(16),
                'status_cucian' => 'diterima',
                'status_pembayaran' => 'lunas',
                'metode_pembayaran' => 'qris',
                'catatan' => 'Butuh cepat untuk dinas luar kota.',
                'logs' => [
                    ['status' => 'diterima', 'keterangan' => 'Order diterima dan dibayar lunas via QRIS.'],
                ],
                'payment' => [
                    'kode_pembayaran' => 'INV-20260918-001',
                    'jumlah_bayar' => 30000,
                    'uang_diterima' => 30000,
                    'kembalian' => 0,
                    'metode_pembayaran' => 'qris',
                    'status' => 'lunas',
                    'tgl_bayar' => $now->copy()->subHours(8),
                    'catatan' => 'Pembayaran lunas via QRIS static kasir.',
                ],
            ],
            [
                'kode_order' => 'ORD-20260917-003',
                'customer_id' => $customers[2]->id,
                'service_id' => $services[0]->id, // Kiloan Reguler 7000
                'berat_atau_jumlah' => 6.2,
                'harga_per_satuan' => 7000,
                'total_harga' => 43400,
                'tgl_masuk' => $now->copy()->subDays(1)->subHours(3),
                'estimasi_selesai' => $now->copy()->addHours(21),
                'status_cucian' => 'diterima',
                'status_pembayaran' => 'belum_lunas',
                'metode_pembayaran' => null,
                'catatan' => 'Baju anak kos 1 minggu.',
                'logs' => [
                    ['status' => 'diterima', 'keterangan' => 'Order diterima kasir, antrean pengerjaan.'],
                ],
            ],
            [
                'kode_order' => 'ORD-20260917-004',
                'customer_id' => $customers[3]->id,
                'service_id' => $services[5]->id, // Bed Cover Besar 25000
                'berat_atau_jumlah' => 2,
                'harga_per_satuan' => 25000,
                'total_harga' => 50000,
                'tgl_masuk' => $now->copy()->subDays(1)->subHours(8),
                'estimasi_selesai' => $now->copy()->addHours(16),
                'tgl_selesai' => $now->copy()->subHours(2),
                'status_cucian' => 'siap_diambil',
                'status_pembayaran' => 'lunas',
                'metode_pembayaran' => 'transfer',
                'catatan' => 'Bed cover king size motif bunga dan polos abu-abu.',
                'logs' => [
                    ['status' => 'diterima', 'keterangan' => '2 pcs Bed Cover ukuran King diterima.'],
                    ['status' => 'siap_diambil', 'keterangan' => 'Bed cover sudah bersih, wangi, rapi dan siap diambil.'],
                ],
                'payment' => [
                    'kode_pembayaran' => 'INV-20260917-002',
                    'jumlah_bayar' => 50000,
                    'uang_diterima' => 50000,
                    'kembalian' => 0,
                    'metode_pembayaran' => 'transfer',
                    'status' => 'lunas',
                    'tgl_bayar' => $now->copy()->subDays(1)->subHours(8),
                    'catatan' => 'Transfer Bank BCA.',
                ],
            ],
            [
                'kode_order' => 'ORD-20260916-005',
                'customer_id' => $customers[4]->id,
                'service_id' => $services[2]->id, // Express 6 Jam 15000
                'berat_atau_jumlah' => 2.5,
                'harga_per_satuan' => 15000,
                'total_harga' => 37500,
                'tgl_masuk' => $now->copy()->subDays(2),
                'estimasi_selesai' => $now->copy()->subDays(2)->addHours(6),
                'tgl_selesai' => $now->copy()->subDays(2)->addHours(5),
                'status_cucian' => 'siap_diambil',
                'status_pembayaran' => 'belum_lunas',
                'metode_pembayaran' => null,
                'catatan' => 'Kemeja kerja putih 5 pcs.',
                'logs' => [
                    ['status' => 'diterima', 'keterangan' => 'Order express masuk.'],
                    ['status' => 'siap_diambil', 'keterangan' => 'Sudah dipacking rapi dan ditempatkan di rak A-03.'],
                ],
            ],
            [
                'kode_order' => 'ORD-20260915-006',
                'customer_id' => $customers[0]->id,
                'service_id' => $services[9]->id, // Cuci Sepatu 30000
                'berat_atau_jumlah' => 2,
                'harga_per_satuan' => 30000,
                'total_harga' => 60000,
                'tgl_masuk' => $now->copy()->subDays(3),
                'estimasi_selesai' => $now->copy()->subDays(1),
                'tgl_selesai' => $now->copy()->subDays(1),
                'tgl_diambil' => $now->copy()->subDays(1)->addHours(4),
                'status_cucian' => 'selesai',
                'status_pembayaran' => 'lunas',
                'metode_pembayaran' => 'tunai',
                'catatan' => 'Sepatu sneakers putih dan vans hitam.',
                'logs' => [
                    ['status' => 'diterima', 'keterangan' => 'Diterima 2 pasang sepatu.'],
                    ['status' => 'siap_diambil', 'keterangan' => 'Sepatu sudah bersih wangi di shoe bag siap diambil.'],
                    ['status' => 'selesai', 'keterangan' => 'Sudah diserahkan ke pelanggan dan dibayar tunai.'],
                ],
                'payment' => [
                    'kode_pembayaran' => 'INV-20260915-003',
                    'jumlah_bayar' => 60000,
                    'uang_diterima' => 100000,
                    'kembalian' => 40000,
                    'metode_pembayaran' => 'tunai',
                    'status' => 'lunas',
                    'tgl_bayar' => $now->copy()->subDays(1)->addHours(4),
                    'catatan' => 'Bayar tunai di tempat saat ambil barang.',
                ],
            ],
        ];

        foreach ($sampleOrders as $data) {
            $logs = $data['logs'] ?? [];
            $paymentData = $data['payment'] ?? null;
            unset($data['logs'], $data['payment']);

            $order = Order::updateOrCreate(
                ['kode_order' => $data['kode_order']],
                $data
            );

            // Create logs
            foreach ($logs as $l) {
                OrderStatusLog::create([
                    'order_id' => $order->id,
                    'status' => $l['status'],
                    'keterangan' => $l['keterangan'],
                    'diupdate_oleh' => 'Admin Laundry',
                ]);
            }

            // Create payment if paid
            if ($paymentData) {
                $paymentData['order_id'] = $order->id;
                Payment::updateOrCreate(
                    ['kode_pembayaran' => $paymentData['kode_pembayaran']],
                    $paymentData
                );
            }
        }
    }
}
