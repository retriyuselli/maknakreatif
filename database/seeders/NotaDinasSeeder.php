<?php

namespace Database\Seeders;

use App\Models\NotaDinas;
use App\Models\NotaDinasDetail;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotaDinasSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Get users for pengirim, penerima, and approver
        $users = User::all();
        $vendors = Vendor::all();

        if ($users->isEmpty()) {
            $this->command->error('No users found. Please run UserSeeder first.');
            return;
        }

        if ($vendors->isEmpty()) {
            $this->command->error('No vendors found. Please run VendorSeeder first.');
            return;
        }

        $this->command->info('Creating Nota Dinas records...');

        // Create sample Nota Dinas with details
        $notaDinasList = [
            [
                'no_nd' => 'ND-202508-001',
                'tanggal' => '2025-08-01',
                'sifat' => 'Segera',
                'hal' => 'Permintaan Transfer Vendor Wedding Andi & Sari',
                'catatan' => 'Transfer untuk vendor wedding Andi & Sari tanggal 15 Agustus 2025. Mohon segera diproses.',
                'status' => 'disetujui',
                'details' => [
                    [
                        'nama_rekening' => 'CV Dekorasi Mewah Jakarta',
                        'keperluan' => 'Dekorasi Pelaminan dan Taman',
                        'event' => 'Wedding Andi & Sari',
                        'jumlah_transfer' => 15000000,
                        'invoice_number' => 'INV-DM-001',
                        'status_invoice' => 'sudah_dibayar',
                    ],
                    [
                        'nama_rekening' => 'UD Catering Premium Bogor',
                        'keperluan' => 'Catering 200 Pax',
                        'event' => 'Wedding Andi & Sari',
                        'jumlah_transfer' => 25000000,
                        'invoice_number' => 'INV-CP-001',
                        'status_invoice' => 'sudah_dibayar',
                    ],
                    [
                        'nama_rekening' => 'Foto Video Cinematic',
                        'keperluan' => 'Dokumentasi Wedding',
                        'event' => 'Wedding Andi & Sari',
                        'jumlah_transfer' => 8000000,
                        'invoice_number' => 'INV-FV-001',
                        'status_invoice' => 'sudah_dibayar',
                    ],
                ],
            ],
            [
                'no_nd' => 'ND-202508-002',
                'tanggal' => '2025-08-05',
                'sifat' => 'Biasa',
                'hal' => 'Permintaan Transfer Vendor Wedding Budi & Rina',
                'catatan' => 'Transfer untuk vendor wedding Budi & Rina tanggal 20 Agustus 2025.',
                'status' => 'diajukan',
                'details' => [
                    [
                        'nama_rekening' => 'Soundsystem Pro Jakarta',
                        'keperluan' => 'Sound System dan Lighting',
                        'event' => 'Wedding Budi & Rina',
                        'jumlah_transfer' => 5000000,
                        'invoice_number' => 'INV-SS-002',
                        'status_invoice' => 'menunggu',
                    ],
                    [
                        'nama_rekening' => 'Tenda Pesta Sentosa',
                        'keperluan' => 'Sewa Tenda 10x15',
                        'event' => 'Wedding Budi & Rina',
                        'jumlah_transfer' => 3500000,
                        'invoice_number' => 'INV-TP-002',
                        'status_invoice' => 'belum_dibayar',
                    ],
                ],
            ],
            [
                'no_nd' => 'ND-202508-003',
                'tanggal' => '2025-08-10',
                'sifat' => 'Segera',
                'hal' => 'Permintaan Transfer Vendor Wedding David & Lisa',
                'catatan' => 'Transfer untuk vendor wedding David & Lisa tanggal 25 Agustus 2025. Event premium package.',
                'status' => 'dibayar',
                'details' => [
                    [
                        'nama_rekening' => 'Luxury Wedding Decor',
                        'keperluan' => 'Dekorasi Premium Package',
                        'event' => 'Wedding David & Lisa',
                        'jumlah_transfer' => 35000000,
                        'invoice_number' => 'INV-LW-003',
                        'status_invoice' => 'sudah_dibayar',
                    ],
                    [
                        'nama_rekening' => 'Catering Royal Menu',
                        'keperluan' => 'Catering 300 Pax Premium',
                        'event' => 'Wedding David & Lisa',
                        'jumlah_transfer' => 45000000,
                        'invoice_number' => 'INV-CR-003',
                        'status_invoice' => 'sudah_dibayar',
                    ],
                    [
                        'nama_rekening' => 'Professional Wedding Band',
                        'keperluan' => 'Live Music Performance',
                        'event' => 'Wedding David & Lisa',
                        'jumlah_transfer' => 12000000,
                        'invoice_number' => 'INV-PW-003',
                        'status_invoice' => 'sudah_dibayar',
                    ],
                    [
                        'nama_rekening' => 'Makeup Artist Celebrity',
                        'keperluan' => 'Makeup Pengantin dan Keluarga',
                        'event' => 'Wedding David & Lisa',
                        'jumlah_transfer' => 8000000,
                        'invoice_number' => 'INV-MA-003',
                        'status_invoice' => 'sudah_dibayar',
                    ],
                ],
            ],
            [
                'no_nd' => 'ND-202508-004',
                'tanggal' => '2025-08-12',
                'sifat' => 'Biasa',
                'hal' => 'Permintaan Transfer Vendor Corporate Event PT Maju Jaya',
                'catatan' => 'Transfer untuk vendor corporate event PT Maju Jaya tanggal 30 Agustus 2025.',
                'status' => 'draft',
                'details' => [
                    [
                        'nama_rekening' => 'Event Organizer Professional',
                        'keperluan' => 'Corporate Event Management',
                        'event' => 'Annual Meeting PT Maju Jaya',
                        'jumlah_transfer' => 20000000,
                        'invoice_number' => 'INV-EO-004',
                        'status_invoice' => 'belum_dibayar',
                    ],
                    [
                        'nama_rekening' => 'Hotel Grand Ballroom',
                        'keperluan' => 'Venue Corporate Event',
                        'event' => 'Annual Meeting PT Maju Jaya',
                        'jumlah_transfer' => 15000000,
                        'invoice_number' => 'INV-HG-004',
                        'status_invoice' => 'belum_dibayar',
                    ],
                ],
            ],
            [
                'no_nd' => 'ND-202508-005',
                'tanggal' => '2025-08-15',
                'sifat' => 'Segera',
                'hal' => 'Permintaan Transfer Vendor Birthday Party Anak',
                'catatan' => 'Transfer untuk vendor birthday party anak tanggal 18 Agustus 2025.',
                'status' => 'ditolak',
                'details' => [
                    [
                        'nama_rekening' => 'Kids Party Organizer',
                        'keperluan' => 'Organizing Birthday Party',
                        'event' => 'Birthday Party Alicia',
                        'jumlah_transfer' => 5000000,
                        'invoice_number' => 'INV-KP-005',
                        'status_invoice' => 'belum_dibayar',
                    ],
                ],
            ],
        ];

        foreach ($notaDinasList as $ndData) {
            // Create Nota Dinas
            $notaDinas = NotaDinas::create([
                'no_nd' => $ndData['no_nd'],
                'tanggal' => $ndData['tanggal'],
                'pengirim_id' => $users->random()->id,
                'penerima_id' => $users->random()->id,
                'sifat' => $ndData['sifat'],
                'hal' => $ndData['hal'],
                'catatan' => $ndData['catatan'],
                'status' => $ndData['status'],
                'approved_by' => in_array($ndData['status'], ['disetujui', 'dibayar']) ? $users->random()->id : null,
                'approved_at' => in_array($ndData['status'], ['disetujui', 'dibayar']) ? now()->subDays(rand(1, 5)) : null,
            ]);

            // Create Nota Dinas Details
            foreach ($ndData['details'] as $detailData) {
                NotaDinasDetail::create([
                    'nota_dinas_id' => $notaDinas->id,
                    'nama_rekening' => $detailData['nama_rekening'],
                    'vendor_id' => $vendors->random()->id,
                    'keperluan' => $detailData['keperluan'],
                    'event' => $detailData['event'],
                    'jumlah_transfer' => $detailData['jumlah_transfer'],
                    'invoice_number' => $detailData['invoice_number'],
                    'invoice_file' => null, // File akan diupload manual
                    'bukti_transfer' => null, // File akan diupload manual
                    'status_invoice' => $detailData['status_invoice'],
                ]);
            }

            $this->command->info("Created Nota Dinas: {$ndData['no_nd']} with " . count($ndData['details']) . " details");
        }

        $this->command->info('NotaDinas seeder completed successfully!');
        $this->command->info('Created ' . count($notaDinasList) . ' Nota Dinas records with details');
    }
}
