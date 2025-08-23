<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Partisipasi;

class PartisipasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Contoh data, sesuaikan expo_id, vendor_id, category_tenant_id dengan data yang ada di tabel terkait
        $data = [
            [
                'expo_id' => 1,
                'vendor_id' => 1,
                'category_tenant_id' => 1,
                'tanggal_booking' => '2025-08-01',
                'harga_jual' => 10000000,
                'status_pembayaran' => 'Belum Lunas',
            ],
            [
                'expo_id' => 1,
                'vendor_id' => 2,
                'category_tenant_id' => 2,
                'tanggal_booking' => '2025-08-02',
                'harga_jual' => 7000000,
                'status_pembayaran' => 'Lunas',
            ],
        ];

        foreach ($data as $item) {
            Partisipasi::create($item);
        }
    }
}
