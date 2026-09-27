<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'kode_pelanggan' => 'CUST-0001',
                'nama' => 'Budi Santoso',
                'no_hp' => '081234567890',
                'alamat' => 'Jl. Mawar No. 12, Kelurahan Melati',
                'catatan' => 'Pelanggan setia, suka wangi lavender',
            ],
            [
                'kode_pelanggan' => 'CUST-0002',
                'nama' => 'Siti Nurhaliza',
                'no_hp' => '082198765432',
                'alamat' => 'Jl. Anggrek Blok B4, Komplek Permata',
                'catatan' => 'Minta plastik dipisah per 2 stel',
            ],
            [
                'kode_pelanggan' => 'CUST-0003',
                'nama' => 'Rian Pratama',
                'no_hp' => '085712344321',
                'alamat' => 'Kost Podomoro Kamar 15, Jl. Kampus',
                'catatan' => 'Anak kos, langganan paket kiloan kilat',
            ],
            [
                'kode_pelanggan' => 'CUST-0004',
                'nama' => 'Dewi Lestari',
                'no_hp' => '087811223344',
                'alamat' => 'Perumahan Griya Indah No. 88',
                'catatan' => 'Sering laundry bed cover dan gorden',
            ],
            [
                'kode_pelanggan' => 'CUST-0005',
                'nama' => 'Ahmad Fauzi',
                'no_hp' => '081399887766',
                'alamat' => 'Jl. Diponegoro Gang 3 No. 5',
                'catatan' => 'Pakaian kantor kemeja putih jangan dicampur warna luntur',
            ],
        ];

        foreach ($customers as $cust) {
            Customer::updateOrCreate(['kode_pelanggan' => $cust['kode_pelanggan']], $cust);
        }
    }
}
