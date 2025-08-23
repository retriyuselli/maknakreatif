<?php

namespace App\Filament\Widgets;

use App\Models\Partisipasi;
use App\Models\Vendor;
use App\Models\Expo;
use App\Models\CategoryTenant;
use App\Models\DataPembayaran;
use App\Models\RekeningTujuan;
use App\Models\User;
use App\Models\Pengeluaran;
use App\Models\PengeluaranLain;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 0;
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        // Get date filters from the page
        // Assuming the filters are set in the Dashboard page
        // Adjust the filter names according to your actual filter names
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $periode = $this->filters['periode'] ?? null;

        // Query with date filter for all relevant models
        $partisipasiQuery = Partisipasi::query();
        $vendorQuery = Vendor::query();
        $expoQuery = Expo::query();
        $pembayaranQuery = DataPembayaran::query();
        $pengeluaranQuery = Pengeluaran::query();
        $pengeluaranLainQuery = PengeluaranLain::query();

        if ($startDate) {
            $partisipasiQuery->whereDate('tanggal_booking', '>=', $startDate);
            $vendorQuery->whereDate('created_at', '>=', $startDate);
            $expoQuery->whereDate('tanggal_mulai', '>=', $startDate);
            $pembayaranQuery->whereDate('tanggal_bayar', '>=', $startDate);
            $pengeluaranQuery->whereDate('tanggal', '>=', $startDate);
            $pengeluaranLainQuery->whereDate('tanggal', '>=', $startDate);
            $periode = $this->filters['periode'] ?? null;
                if ($periode) {
                    $expoQuery->where('periode', $periode);
                    // Jika ingin filter data lain berdasarkan expo yang periode-nya sama, bisa join atau whereIn expo_id
                }
        }
        if ($endDate) {            
            $partisipasiQuery->whereDate('tanggal_booking', '<=', $endDate);
            $vendorQuery->whereDate('created_at', '<=', $endDate);
            $expoQuery->whereDate('tanggal_mulai', '<=', $endDate);
            $pembayaranQuery->whereDate('tanggal_bayar', '<=', $endDate);
            $pengeluaranQuery->whereDate('tanggal', '<=', $endDate);
            $pengeluaranLainQuery->whereDate('tanggal', '<=', $endDate);
            $periode = $this->filters['periode'] ?? null;
                if ($periode) {
                    $expoQuery->where('periode', $periode);
                    // Jika ingin filter data lain berdasarkan expo yang periode-nya sama, bisa join atau whereIn expo_id
                }
        }

        $totalPembayaran = $pembayaranQuery->sum('nominal');
        $totalPengeluaranExpo = $pengeluaranQuery->sum('nominal');
        $totalPengeluaranLain = $pengeluaranLainQuery->sum('nominal');
        $labaRugi = $totalPembayaran - ($totalPengeluaranExpo + $totalPengeluaranLain);

        return [
            Stat::make('Total Partisipasi', $partisipasiQuery->count()),
            Stat::make('Total Vendor', $vendorQuery->count() . ' (Aktif: ' . $vendorQuery->whereNull('deleted_at')->count() . ')'),
            Stat::make('Total Expo', $expoQuery->count() . ' (Aktif: ' . $expoQuery->whereNull('deleted_at')->count() . ')'),
            Stat::make('Total Kategori Tenant', CategoryTenant::count()),
            Stat::make('Total Pembayaran', $pembayaranQuery->count()),
            Stat::make('Total Nominal Pembayaran', 'Rp ' . number_format($totalPembayaran, 0, ',', '.')),
            Stat::make('Total Pengeluaran Expo', 'Rp ' . number_format($totalPengeluaranExpo, 0, ',', '.')),
            Stat::make('Total Pengeluaran Lain', 'Rp ' . number_format($totalPengeluaranLain, 0, ',', '.')),
            Stat::make('Laba / Rugi Sementara', 'Rp ' . number_format($labaRugi, 0, ',', '.')),
        ];
    }
}
