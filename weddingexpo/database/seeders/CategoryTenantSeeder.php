<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CategoryTenant;

class CategoryTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Contoh data, sesuaikan expo_id dengan data yang ada di tabel expos
        $data = [
            [
                'expo_id' => 1,
                'category' => 'Platinum',
                'harga_jual' => 10000000,
                'harga_modal' => 8000000,
                'jumlah_unit' => 5,
                'ukuran' => '3x3m',
                'deskripsi' => 'Kategori tenant platinum dengan lokasi strategis.',
                'status' => 'Aktif',
            ],
            [
                'expo_id' => 1,
                'category' => 'Gold',
                'harga_jual' => 7000000,
                'harga_modal' => 6000000,
                'jumlah_unit' => 10,
                'ukuran' => '3x3m',
                'deskripsi' => 'Kategori tenant gold dengan fasilitas standar.',
                'status' => 'Aktif',
            ],
        ];

        foreach ($data as $item) {
            CategoryTenant::create($item);
        }
    }
}
