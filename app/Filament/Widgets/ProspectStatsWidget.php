<?php

namespace App\Filament\Widgets;

use App\Models\Prospect;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProspectStatsWidget extends BaseWidget
{
    use HasWidgetShield;
    
    protected function getStats(): array
    {
        $totalProspects = Prospect::count();
        $prospectsWithOrders = Prospect::has('orders')->count();
        $prospectsWithoutOrders = Prospect::doesntHave('orders')->count();
        $protectedProspects = $prospectsWithOrders; // Prospects that cannot be deleted
        $conversionRate = $totalProspects > 0 ? round(($prospectsWithOrders / $totalProspects) * 100, 1) : 0;

        return [
            Stat::make('Total Prospect', $totalProspects)
                ->description('Semua prospect yang terdaftar')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Warm Prospect', $prospectsWithoutOrders)
                ->description('Prospect yang belum dikonversi (dapat dihapus)')
                ->descriptionIcon('heroicon-m-fire')
                ->color('warning'),

            Stat::make('Converted Prospect', $prospectsWithOrders)
                ->description('Prospect yang sudah ada order (dilindungi)')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('success'),

            Stat::make('Conversion Rate', $conversionRate . '%')
                ->description('Tingkat konversi prospect ke order')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color($conversionRate >= 50 ? 'success' : ($conversionRate >= 25 ? 'warning' : 'danger')),
        ];
    }
}
