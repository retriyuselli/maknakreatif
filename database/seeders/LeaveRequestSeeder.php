<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\LeaveType;
use Carbon\Carbon;

class LeaveRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "🏖️ Creating Leave Request Data...\n";
        
        // Ambil semua users dan leave types
        $users = User::all();
        $leaveTypes = LeaveType::all();
        
        if ($users->isEmpty()) {
            echo "❌ No users found. Please run UserSeeder first.\n";
            return;
        }
        
        if ($leaveTypes->isEmpty()) {
            echo "❌ No leave types found. Please run LeaveTypeSeeder first.\n";
            return;
        }
        
        // Array status untuk variasi
        $statuses = ['pending', 'approved', 'rejected'];
        
        // Array reasons untuk variasi cuti
        $leaveReasons = [
            'annual' => [
                'Liburan keluarga',
                'Berlibur ke Bali',
                'Cuti tahunan',
                'Istirahat',
                'Liburan akhir tahun',
                'Refreshing',
                'Quality time dengan keluarga'
            ],
            'sick' => [
                'Sakit demam',
                'Flu dan batuk',
                'Sakit kepala',
                'Checkup kesehatan',
                'Rawat jalan rumah sakit',
                'Istirahat medis',
                'Sakit perut'
            ],
            'emergency' => [
                'Keluarga sakit',
                'Keperluan mendesak',
                'Kecelakaan keluarga',
                'Urusan penting',
                'Emergency keluarga',
                'Musibah',
                'Kondisi darurat'
            ],
            'maternity' => [
                'Cuti melahirkan',
                'Persiapan persalinan',
                'Pemulihan pasca melahirkan',
                'Perawatan bayi baru lahir'
            ]
        ];
        
        $createdCount = 0;
        
        // Buat data leave request untuk setiap user
        foreach ($users as $user) {
            // Random 2-5 leave requests per user
            $requestCount = rand(2, 5);
            
            for ($i = 0; $i < $requestCount; $i++) {
                $leaveType = $leaveTypes->random();
                $status = $statuses[array_rand($statuses)];
                
                // Tentukan rentang tanggal (6 bulan terakhir sampai 3 bulan ke depan)
                $startDate = Carbon::now()
                    ->subMonths(6)
                    ->addDays(rand(0, 270)); // Random dalam 9 bulan
                
                // Durasi cuti berdasarkan jenis
                $maxDuration = match($leaveType->name) {
                    'Cuti Tahunan' => rand(2, 7), // 2-7 hari
                    'Cuti Sakit' => rand(1, 3),   // 1-3 hari
                    'Cuti Darurat' => rand(1, 2), // 1-2 hari
                    'Cuti Melahirkan' => rand(30, 90), // 1-3 bulan
                    default => rand(1, 5)
                };
                
                $endDate = $startDate->copy()->addDays($maxDuration - 1);
                $totalDays = $startDate->diffInDays($endDate) + 1;
                
                // Tentukan approver (random user yang bukan dirinya sendiri)
                $approver = null;
                if ($status !== 'pending') {
                    $possibleApprovers = $users->where('id', '!=', $user->id);
                    if ($possibleApprovers->isNotEmpty()) {
                        $approver = $possibleApprovers->random();
                    }
                }
                
                // Buat leave request
                $leaveRequest = LeaveRequest::create([
                    'user_id' => $user->id,
                    'leave_type_id' => $leaveType->id,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'total_days' => $totalDays,
                    'status' => $status,
                    'approved_by' => $approver?->id,
                ]);
                
                $createdCount++;
                
                // Tampilkan info
                $statusIcon = match($status) {
                    'approved' => '✅',
                    'rejected' => '❌',
                    'pending' => '⏳',
                    default => '📝'
                };
                
                echo "  {$statusIcon} {$user->name} - {$leaveType->name} ({$totalDays} hari) - {$status}\n";
                echo "     📅 {$startDate->format('d/m/Y')} - {$endDate->format('d/m/Y')}\n";
                
                if ($approver) {
                    echo "     👤 Approved by: {$approver->name}\n";
                }
                echo "\n";
            }
        }
        
        echo "🎉 Successfully created {$createdCount} leave request records!\n\n";
        
        // Tampilkan statistik
        echo "📊 LEAVE REQUEST SUMMARY:\n";
        echo "─────────────────────────────────────\n";
        
        $totalRequests = LeaveRequest::count();
        $pendingCount = LeaveRequest::where('status', 'pending')->count();
        $approvedCount = LeaveRequest::where('status', 'approved')->count();
        $rejectedCount = LeaveRequest::where('status', 'rejected')->count();
        
        echo "📈 Total Leave Requests: {$totalRequests}\n";
        echo "⏳ Pending: {$pendingCount}\n";
        echo "✅ Approved: {$approvedCount}\n";
        echo "❌ Rejected: {$rejectedCount}\n\n";
        
        // Statistik per leave type
        echo "📋 BREAKDOWN BY LEAVE TYPE:\n";
        foreach ($leaveTypes as $type) {
            $typeCount = LeaveRequest::where('leave_type_id', $type->id)->count();
            $typeApproved = LeaveRequest::where('leave_type_id', $type->id)->where('status', 'approved')->count();
            echo "└─ {$type->name}: {$typeCount} requests ({$typeApproved} approved)\n";
        }
        
        echo "\n📅 DATE RANGE:\n";
        $earliestDate = LeaveRequest::min('start_date');
        $latestDate = LeaveRequest::max('end_date');
        echo "└─ From: {$earliestDate} to {$latestDate}\n";
        
        echo "\n🏖️ Leave request data generation completed!\n";
    }
}
