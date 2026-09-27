<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'nama_layanan' => 'Cuci Kering Setrika (Reguler 2 Hari)',
                'jenis' => 'kiloan',
                'harga' => 7000,
                'durasi_jam' => 48,
                'deskripsi' => 'Pencucian bersih harum, dikeringkan dan disetrika rapi, packing plastik tertutup.',
                'is_active' => true,
            ],
            [
                'nama_layanan' => 'Cuci Kering Setrika (Kilat 1 Hari)',
                'jenis' => 'kiloan',
                'harga' => 10000,
                'durasi_jam' => 24,
                'deskripsi' => 'Layanan cepat 24 jam selesai, wangi, rapi, dan siap pakai.',
                'is_active' => true,
            ],
            [
                'nama_layanan' => 'Cuci Kering Setrika (Express 6 Jam)',
                'jenis' => 'kiloan',
                'harga' => 15000,
                'durasi_jam' => 6,
                'deskripsi' => 'Layanan super kilat selesai dalam 6 jam dengan prioritas mesin khusus.',
                'is_active' => true,
            ],
            [
                'nama_layanan' => 'Cuci Kering Saja (Lipat Rapi)',
                'jenis' => 'kiloan',
                'harga' => 5000,
                'durasi_jam' => 24,
                'deskripsi' => 'Cuci bersih dan kering, dilipat rapi tanpa setrika.',
                'is_active' => true,
            ],
            [
                'nama_layanan' => 'Setrika Saja (Reguler)',
                'jenis' => 'kiloan',
                'harga' => 4500,
                'durasi_jam' => 24,
                'deskripsi' => 'Pakaian disetrika licin menggunakan uap, parfum laundry premium.',
                'is_active' => true,
            ],
            [
                'nama_layanan' => 'Bed Cover Besar (King/Queen)',
                'jenis' => 'satuan',
                'harga' => 25000,
                'durasi_jam' => 48,
                'deskripsi' => 'Pencucian khusus bed cover ukuran besar dengan perlakuan serat lembut.',
                'is_active' => true,
            ],
            [
                'nama_layanan' => 'Bed Cover Kecil (Single)',
                'jenis' => 'satuan',
                'harga' => 20000,
                'durasi_jam' => 48,
                'deskripsi' => 'Pencucian bed cover single bersih wangi dan higienis.',
                'is_active' => true,
            ],
            [
                'nama_layanan' => 'Selimut / Karpet Mini',
                'jenis' => 'satuan',
                'harga' => 15000,
                'durasi_jam' => 48,
                'deskripsi' => 'Pencucian selimut bulu tebal atau karpet ukuran kecil.',
                'is_active' => true,
            ],
            [
                'nama_layanan' => 'Jas / Blazer Pria & Wanita',
                'jenis' => 'satuan',
                'harga' => 25000,
                'durasi_jam' => 48,
                'deskripsi' => 'Dry clean khusus pakaian formal dengan hanger pelindung.',
                'is_active' => true,
            ],
            [
                'nama_layanan' => 'Cuci Sepatu Casual / Sneakers',
                'jenis' => 'satuan',
                'harga' => 30000,
                'durasi_jam' => 48,
                'deskripsi' => 'Deep cleaning sepatu luar dan dalam, anti jamur dan bau.',
                'is_active' => true,
            ],
        ];

        foreach ($services as $svc) {
            Service::updateOrCreate(['nama_layanan' => $svc['nama_layanan']], $svc);
        }
    }
}
