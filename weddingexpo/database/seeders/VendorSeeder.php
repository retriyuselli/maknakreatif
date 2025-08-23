<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Vendor;
use App\Models\JenisUsaha;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil ID dari jenis usaha yang sudah ada
        $jenisUsahas = JenisUsaha::pluck('id', 'nama_jenis_usaha')->toArray();

        $vendors = [
            // Wedding Organizer
            [
                'nama_vendor' => 'Elegant Wedding Organizer',
                'jenis_usaha_id' => $jenisUsahas['Wedding Organizer'] ?? 1,
                'alamat' => 'Jl. Sudirman No. 123, Palembang',
                'kota' => 'Palembang',
                'no_telepon' => '0711-123456',
                'email' => 'info@elegantwedding.com',
                'nama_pic' => 'Sarah Wijaya',
                'no_wa_pic' => '081234567890'
            ],
            [
                'nama_vendor' => 'Royal Wedding Planner',
                'jenis_usaha_id' => $jenisUsahas['Wedding Planner'] ?? 2,
                'alamat' => 'Jl. Ahmad Yani No. 456, Palembang',
                'kota' => 'Palembang',
                'no_telepon' => '0711-234567',
                'email' => 'contact@royalwedding.com',
                'nama_pic' => 'Diana Sari',
                'no_wa_pic' => '081345678901'
            ],
            
            // Dekorasi Pernikahan
            [
                'nama_vendor' => 'Blossom Decoration',
                'jenis_usaha_id' => $jenisUsahas['Dekorasi Pernikahan'] ?? 3,
                'alamat' => 'Jl. Veteran No. 789, Palembang',
                'kota' => 'Palembang',
                'no_telepon' => '0711-345678',
                'email' => 'hello@blossomdeco.com',
                'nama_pic' => 'Rina Maharani',
                'no_wa_pic' => '081456789012'
            ],
            [
                'nama_vendor' => 'Golden Touch Decoration',
                'jenis_usaha_id' => $jenisUsahas['Dekorasi Pernikahan'] ?? 3,
                'alamat' => 'Jl. Kapten Rivai No. 321, Palembang',
                'kota' => 'Palembang',
                'no_telepon' => '0711-456789',
                'email' => 'info@goldentouch.com',
                'nama_pic' => 'Bambang Sutrisno',
                'no_wa_pic' => '081567890123'
            ],
            
            // Catering Pernikahan
            [
                'nama_vendor' => 'Delicious Wedding Catering',
                'jenis_usaha_id' => $jenisUsahas['Catering Pernikahan'] ?? 4,
                'alamat' => 'Jl. Jenderal Sudirman No. 654, Palembang',
                'kota' => 'Palembang',
                'no_telepon' => '0711-567890',
                'email' => 'order@deliciouscatering.com',
                'nama_pic' => 'Chef Ahmad',
                'no_wa_pic' => '081678901234'
            ],
            [
                'nama_vendor' => 'Rasa Istimewa Catering',
                'jenis_usaha_id' => $jenisUsahas['Catering Pernikahan'] ?? 4,
                'alamat' => 'Jl. Demang Lebar Daun No. 987, Palembang',
                'kota' => 'Palembang',
                'no_telepon' => '0711-678901',
                'email' => 'info@rasaistimewa.com',
                'nama_pic' => 'Siti Nurhaliza',
                'no_wa_pic' => '081789012345'
            ],
            
            // Fotografi Pernikahan
            [
                'nama_vendor' => 'Moment Photography',
                'jenis_usaha_id' => $jenisUsahas['Fotografi Pernikahan'] ?? 5,
                'alamat' => 'Jl. R.E. Martadinata No. 147, Palembang',
                'kota' => 'Palembang',
                'no_telepon' => '0711-789012',
                'email' => 'capture@momentphoto.com',
                'nama_pic' => 'Andi Pratama',
                'no_wa_pic' => '081890123456'
            ],
            [
                'nama_vendor' => 'Eternal Memories Studio',
                'jenis_usaha_id' => $jenisUsahas['Fotografi Pernikahan'] ?? 5,
                'alamat' => 'Jl. Tasik No. 258, Palembang',
                'kota' => 'Palembang',
                'no_telepon' => '0711-890123',
                'email' => 'info@eternalmemories.com',
                'nama_pic' => 'Rudi Hermawan',
                'no_wa_pic' => '081901234567'
            ],
            
            // Videografi Pernikahan
            [
                'nama_vendor' => 'Cinematic Wedding Video',
                'jenis_usaha_id' => $jenisUsahas['Videografi Pernikahan'] ?? 6,
                'alamat' => 'Jl. Letkol Iskandar No. 369, Palembang',
                'kota' => 'Palembang',
                'no_telepon' => '0711-901234',
                'email' => 'video@cinematicwedding.com',
                'nama_pic' => 'Dedi Kurniawan',
                'no_wa_pic' => '082012345678'
            ],
            
            // Musik & Entertainment
            [
                'nama_vendor' => 'Harmony Music Entertainment',
                'jenis_usaha_id' => $jenisUsahas['Musik & Entertainment'] ?? 7,
                'alamat' => 'Jl. Srijaya No. 741, Palembang',
                'kota' => 'Palembang',
                'no_telepon' => '0711-012345',
                'email' => 'booking@harmonymusic.com',
                'nama_pic' => 'Yoga Pratama',
                'no_wa_pic' => '082123456789'
            ],
            
            // Busana Pengantin
            [
                'nama_vendor' => 'Anggun Bridal House',
                'jenis_usaha_id' => $jenisUsahas['Busana Pengantin'] ?? 8,
                'alamat' => 'Jl. Kolonel Atmo No. 852, Palembang',
                'kota' => 'Palembang',
                'no_telepon' => '0711-123450',
                'email' => 'rental@anggunbridal.com',
                'nama_pic' => 'Lestari Dewi',
                'no_wa_pic' => '082234567890'
            ],
            [
                'nama_vendor' => 'Princess Wedding Dress',
                'jenis_usaha_id' => $jenisUsahas['Busana Pengantin'] ?? 8,
                'alamat' => 'Jl. Radial No. 963, Palembang',
                'kota' => 'Palembang',
                'no_telepon' => '0711-234501',
                'email' => 'info@princessdress.com',
                'nama_pic' => 'Maya Sari',
                'no_wa_pic' => '082345678901'
            ],
            
            // Makeup Artist
            [
                'nama_vendor' => 'Glamour Makeup Studio',
                'jenis_usaha_id' => $jenisUsahas['Makeup Artist'] ?? 9,
                'alamat' => 'Jl. Angkatan 45 No. 174, Palembang',
                'kota' => 'Palembang',
                'no_telepon' => '0711-345012',
                'email' => 'book@glamourmakeup.com',
                'nama_pic' => 'Indira Putri',
                'no_wa_pic' => '082456789012'
            ],
            [
                'nama_vendor' => 'Beauty Touch MUA',
                'jenis_usaha_id' => $jenisUsahas['Makeup Artist'] ?? 9,
                'alamat' => 'Jl. Basuki Rahmat No. 285, Palembang',
                'kota' => 'Palembang',
                'no_telepon' => '0711-456123',
                'email' => 'contact@beautytouch.com',
                'nama_pic' => 'Fitri Handayani',
                'no_wa_pic' => '082567890123'
            ],
            
            // Florist
            [
                'nama_vendor' => 'Garden of Love Florist',
                'jenis_usaha_id' => $jenisUsahas['Florist'] ?? 10,
                'alamat' => 'Jl. Mayjend H.R. Andan No. 396, Palembang',
                'kota' => 'Palembang',
                'no_telepon' => '0711-567234',
                'email' => 'order@gardenoflove.com',
                'nama_pic' => 'Dewi Kartika',
                'no_wa_pic' => '082678901234'
            ],
            
            // Undangan Pernikahan
            [
                'nama_vendor' => 'Creative Invitation Design',
                'jenis_usaha_id' => $jenisUsahas['Undangan Pernikahan'] ?? 11,
                'alamat' => 'Jl. Kol. H. Burlian No. 507, Palembang',
                'kota' => 'Palembang',
                'no_telepon' => '0711-678345',
                'email' => 'design@creativeinvitation.com',
                'nama_pic' => 'Arief Budiman',
                'no_wa_pic' => '082789012345'
            ],
            
            // Venue Pernikahan
            [
                'nama_vendor' => 'Grand Ballroom Palembang',
                'jenis_usaha_id' => $jenisUsahas['Venue Pernikahan'] ?? 13,
                'alamat' => 'Jl. Jenderal Ahmad Yani No. 618, Palembang',
                'kota' => 'Palembang',
                'no_telepon' => '0711-789456',
                'email' => 'reservation@grandballroom.com',
                'nama_pic' => 'Hendra Wijaya',
                'no_wa_pic' => '082890123456'
            ],
            [
                'nama_vendor' => 'Riverside Garden Venue',
                'jenis_usaha_id' => $jenisUsahas['Venue Pernikahan'] ?? 13,
                'alamat' => 'Jl. Musi Raya No. 729, Palembang',
                'kota' => 'Palembang',
                'no_telepon' => '0711-890567',
                'email' => 'info@riversidegarden.com',
                'nama_pic' => 'Sari Melati',
                'no_wa_pic' => '082901234567'
            ],
            
            // Sound System
            [
                'nama_vendor' => 'Pro Audio Sound System',
                'jenis_usaha_id' => $jenisUsahas['Sound System'] ?? 20,
                'alamat' => 'Jl. Sekip Jaya No. 830, Palembang',
                'kota' => 'Palembang',
                'no_telepon' => '0711-901678',
                'email' => 'rental@proaudiosound.com',
                'nama_pic' => 'Budi Santoso',
                'no_wa_pic' => '083012345678'
            ],
            
            // Kue Pengantin
            [
                'nama_vendor' => 'Sweet Dreams Wedding Cake',
                'jenis_usaha_id' => $jenisUsahas['Kue Pengantin'] ?? 22,
                'alamat' => 'Jl. Palembang-Betung No. 941, Palembang',
                'kota' => 'Palembang',
                'no_telepon' => '0711-012789',
                'email' => 'order@sweetdreamscake.com',
                'nama_pic' => 'Chef Lina',
                'no_wa_pic' => '083123456789'
            ]
        ];

        foreach ($vendors as $vendor) {
            Vendor::create($vendor);
        }
    }
}