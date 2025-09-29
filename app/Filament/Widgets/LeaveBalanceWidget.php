<?php

namespace App\Filament\Widgets;

use App\Models\LeaveBalance;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class LeaveBalanceWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected int | string | array $columnSpan = 'full';
    
    public function getHeading(): ?string
    {
        return 'Ringkasan Saldo Cuti';
    }

    protected function getStats(): array
    {
        $currentUser = Auth::user();
        $currentYear = now()->year;
        
        // Get current user's leave balance for annual leave
        $currentUserAnnualBalance = LeaveBalance::where('user_id', $currentUser->id)
            ->where('year', $currentYear)
            ->whereHas('leaveType', function($query) {
                $query->where('name', 'like', '%annual%')
                      ->orWhere('name', 'like', '%tahunan%');
            })
            ->first();

        // Get current user's sick leave balance
        $currentUserSickBalance = LeaveBalance::where('user_id', $currentUser->id)
            ->where('year', $currentYear)
            ->whereHas('leaveType', function($query) {
                $query->where('name', 'like', '%sick%')
                      ->orWhere('name', 'like', '%sakit%');
            })
            ->first();

        // Get all users' leave balances for overview
        $totalEmployees = User::where('status', 'active')->count();
        
        // Average remaining annual leave days
        $averageAnnualLeave = LeaveBalance::where('year', $currentYear)
            ->whereHas('leaveType', function($query) {
                $query->where('name', 'like', '%annual%')
                      ->orWhere('name', 'like', '%tahunan%');
            })
            ->avg('remaining_days') ?? 0;
            
        // Average remaining sick leave days
        $averageSickLeave = LeaveBalance::where('year', $currentYear)
            ->whereHas('leaveType', function($query) {
                $query->where('name', 'like', '%sick%')
                      ->orWhere('name', 'like', '%sakit%');
            })
            ->avg('remaining_days') ?? 0;

        // Count employees with low leave balance (less than 5 days)
        $lowLeaveBalanceCount = LeaveBalance::where('year', $currentYear)
            ->where('remaining_days', '<', 5)
            ->count();

        // Total leave days used this year
        $totalUsedLeave = LeaveBalance::where('year', $currentYear)
            ->sum('used_days') ?? 0;

        // Get active employees with leave balances
        $activeEmployeesWithLeave = LeaveBalance::where('year', $currentYear)
            ->distinct('user_id')
            ->count('user_id');

        return [
            Stat::make('Cuti Tahunan Saya', $currentUserAnnualBalance ? $currentUserAnnualBalance->remaining_days . ' hari' : 'Belum diatur')
                ->description('Sisa hari cuti tahunan')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color($this->getLeaveBalanceColor($currentUserAnnualBalance?->remaining_days ?? 0))
                ->chart($this->getLeaveUsageChart($currentUserAnnualBalance)),

            Stat::make('Cuti Sakit Saya', $currentUserSickBalance ? $currentUserSickBalance->remaining_days . ' hari' : 'Belum diatur')
                ->description('Sisa hari cuti sakit')
                ->descriptionIcon('heroicon-m-heart')
                ->color($this->getSickLeaveColor($currentUserSickBalance?->remaining_days ?? 0)),

            Stat::make('Rata-rata Tim', number_format($averageAnnualLeave, 1) . ' hari')
                ->description('Rata-rata sisa cuti tahunan')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('Peringatan Saldo Rendah', $lowLeaveBalanceCount . ' karyawan')
                ->description('Karyawan dengan sisa kurang dari 5 hari')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowLeaveBalanceCount > 0 ? 'danger' : 'success'),

            Stat::make('Total Digunakan Tahun Ini', $totalUsedLeave . ' hari')
                ->description('Total hari cuti yang digunakan perusahaan')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('warning'),

            Stat::make('Karyawan Aktif', $activeEmployeesWithLeave . '/' . $totalEmployees)
                ->description('Karyawan dengan saldo cuti')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),
        ];
    }

    protected function getLeaveBalanceColor(?int $remaining): string
    {
        if ($remaining === null) return 'gray';
        if ($remaining >= 15) return 'success';
        if ($remaining >= 10) return 'info';
        if ($remaining >= 5) return 'warning';
        return 'danger';
    }

    protected function getSickLeaveColor(?int $remaining): string
    {
        if ($remaining === null) return 'gray';
        if ($remaining >= 8) return 'success';
        if ($remaining >= 5) return 'info';
        if ($remaining >= 3) return 'warning';
        return 'danger';
    }

    protected function getLeaveUsageChart(?LeaveBalance $balance): array
    {
        if (!$balance) {
            return [0, 0, 0, 0, 0, 0, 0];
        }

        // Simple chart showing usage pattern
        $totalDays = $balance->allocated_days ?? 20;
        $used = $balance->used_days ?? 0;
        $remaining = $balance->remaining_days ?? $totalDays;

        // Create a simple trend chart
        $months = 7; // Show last 7 months
        $chart = [];
        $monthlyUsage = $used / max(now()->month, 1); // Average usage per month
        
        for ($i = 0; $i < $months; $i++) {
            $chart[] = max(0, round($monthlyUsage * ($i + 1)));
        }

        return $chart;
    }

    public function getDescription(): ?string
    {
        return 'Ringkasan saldo cuti untuk tahun berjalan (' . now()->year . ')';
    }

    protected function getColumns(): int
    {
        return 3; // Display 3 stats per row
    }

    // Make the widget refreshable
    protected static ?string $maxHeight = '300px';
    
    // Add polling for real-time updates
    protected static ?string $pollingInterval = '30s';

    // You can add custom styling
    protected function getViewData(): array
    {
        return [
            'currentYear' => now()->year,
            'currentUser' => Auth::user()->name,
        ];
    }
}
