<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\JenisUsaha;

class JenisUsahaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jenisUsahas = [
            'Wedding Organizer',
            'Wedding Planner',
            'Dekorasi Pernikahan',
            'Catering Pernikahan',
            'Fotografi Pernikahan',
            'Videografi Pernikahan',
            'Musik & Entertainment',
            'Busana Pengantin',
            'Makeup Artist',
            'Florist',
            'Undangan Pernikahan',
            'Souvenir Pernikahan',
            'Venue Pernikahan',
            'Gedung Pernikahan',
            'Hotel & Resort',
            'Transportasi Pernikahan',
            'Jewelry & Aksesoris',
            'Honeymoon Travel',
            'Tenda & Perlengkapan',
            'Sound System',
            'Lighting',
            'Kue Pengantin',
            'Henna Art',
            'Spa & Beauty Treatment',
            'MC & Host',
            'Dancer & Performer',
            'Rental Mobil Pengantin',
            'Perlengkapan Akad Nikah',
            'Dokumentasi Drone',
            'Live Streaming Wedding'
        ];

        foreach ($jenisUsahas as $jenisUsaha) {
            JenisUsaha::create([
                'nama_jenis_usaha' => $jenisUsaha
            ]);
        }
    }
}