<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RekeningTujuan;

class RekeningTujuanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rekeningTujuans = [
            [
                'nama_bank' => 'Bank Mandiri',
                'nomor_rekening' => '1370012345678',
                'nama_pemilik' => 'PT. Makna Kreatif Indonesia'
            ],
            [
                'nama_bank' => 'Bank BCA',
                'nomor_rekening' => '5465012345678',
                'nama_pemilik' => 'PT. Makna Kreatif Indonesia'
            ]
        ];

        foreach ($rekeningTujuans as $rekening) {
            RekeningTujuan::create($rekening);
        }
    }
}
