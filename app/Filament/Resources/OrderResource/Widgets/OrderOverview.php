<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use Illuminate\Support\Number;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\DataPembayaran;
use App\Models\Expense;
use App\Models\ExpenseOps;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class OrderOverview extends BaseWidget
{

    protected static ?string $pollingInterval = '5s';

    public $metrics = [
        'payments' => 0,
        'projects' => 0,
        'revenue' => 0,
        'processing' => 0,
        'total_revenue' => 0,
        'documents' => 0,
        'pending_documents' => 0
    ];

    public function mount(): void
    {
        $this->refreshMetrics();
    }

    /**
     * Listen for order updates and refresh metrics
     */
    #[On('order-updated')]
    #[On('payment-received')]
    public function refreshMetrics(): void
    {
        $currentMonth = Carbon::now();

        // Single query to get all monthly metrics
        $monthlyData = Order::whereMonth('closing_date', $currentMonth->month)
            ->whereYear('closing_date', $currentMonth->year)
            ->select(
                DB::raw('COUNT(*) as total_projects'),
                DB::raw('SUM(total_price + penambahan - pengurangan - promo) as monthly_revenue'),
                DB::raw('COUNT(CASE WHEN status = "processing" THEN 1 END) as processing_count') // Ini menghitung order dengan status "processing"
            )
            ->first();

        $this->metrics['projects'] = $monthlyData->total_projects ?? 0;
        $this->metrics['revenue'] = $monthlyData->monthly_revenue ?? 0;
        $this->metrics['processing'] = $monthlyData->processing_count ?? 0; // Menyimpan hasil hitungan
        $this->metrics['documents'] = Order::whereNotNull('doc_kontrak')->count();
        $this->metrics['pending_documents'] = Order::whereNull('doc_kontrak')->count();

        // Get payments for orders with "processing" status
        $this->metrics['payments'] = DataPembayaran::whereIn('order_id', function ($query) {
            $query->select('id')
                ->from('orders')
                ->where('status', 'processing');
        })->sum('nominal');

        // Calculate total revenue for the current year
        $this->metrics['total_revenue'] = Order::whereYear('closing_date', $currentMonth->year)
            ->sum(DB::raw('(total_price + penambahan) - (pengurangan + promo)'));


        $this->metrics['total_expenseOps'] = ExpenseOps::sum('amount');

        // Get expenses for orders with "processing" status
        $this->metrics['total_expense'] = Expense::whereIn('order_id', function ($query) {
            $query->select('id')
                ->from('orders')
                ->where('status', 'processing');
        })->sum('amount');
    }

    /**
     * Format currency with Indonesian Rupiah format
     */
    protected function formatCurrency(float $amount): string
    {
        return '' . number_format($amount, 0, ',', '.');
    }

    /**
     * Calculate simple trend indicators
     */
    protected function calculateTrend(string $metric): array
    {
        $trend = [];
        $days = 7;
        
        for ($i = 0; $i < $days; $i++) {
            $date = Carbon::now()->subDays($i);
            
            $value = match($metric) {
                'projects' => Order::whereDate('created_at', $date)->count(),
                'revenue' => Order::whereDate('created_at', $date)
                    ->sum(DB::raw('total_price + penambahan - pengurangan - promo')),
                default => 0
            };
            
            $trend[] = $value;
        }

        return $trend;
    }

    protected function getStats(): array
    {
        // Create a simple trend for projects and revenue
        $projectTrend = $this->calculateTrend('projects');
        $revenueTrend = $this->calculateTrend('revenue');$statusTarget = OrderStatus::Processing; // Ganti dengan OrderStatus::DONE jika ingin status 'done'
        $targetOrderIds = Order::where('status', $statusTarget)->pluck('id');
        $totalPembayaranUntukTargetOrder = DataPembayaran::whereIn('order_id', $targetOrderIds)
            ->sum('nominal');
        $totalPengeluaranUntukTargetOrder = Expense::whereIn('order_id', $targetOrderIds)
            ->sum('amount');
        $sumUangDiterimaUntukTargetOrder = $totalPembayaranUntukTargetOrder - $totalPengeluaranUntukTargetOrder;

        // Deskripsi bisa disesuaikan berdasarkan statusTarget
        $descriptionText = 'Untuk order dengan status ' . ($statusTarget instanceof \BackedEnum ? $statusTarget->value : $statusTarget);

        return [
            // Customer Payments Overview
            Stat::make('Total Customer Payments', $this->formatCurrency($this->metrics['payments']))
                ->description('Total payments received')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->url(route('reports.customer-payments', ['status' => OrderStatus::Processing->value])),
            
            // Customer Expenses Overview
            Stat::make('Total Customer Expenses', $this->formatCurrency($this->metrics['total_expense']))
                ->description('Total expenses')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('danger'),

            // Total Sisa Uang Customer
            Stat::make('Total Sisa Uang Customer', $this->formatCurrency($this->metrics['payments'] - $this->metrics['total_expense']))
                ->description('Total sisa uang')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            // Monthly Projects Overview
            Stat::make('New Projects This Month', $this->metrics['projects'])
                ->description('Projects in ' . now()->format('F Y'))
                ->descriptionIcon('heroicon-m-document-plus')
                ->chart($projectTrend)
                ->color('primary'),

            // Monthly Revenue Overview
            Stat::make('Monthly Revenue', $this->formatCurrency($this->metrics['revenue']))
                ->description('Revenue in ' . now()->format('F Y'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart($revenueTrend)
                ->color('success'),

            // Total Documents Overview
            Stat::make('Total Documents Contract', $this->metrics['documents'])
                ->description('Total documents')
                ->description(sprintf('%d pending proof documentation', $this->metrics['pending_documents']))
                ->color('primary'),

            // Total Revenue Overview
            Stat::make('Total Revenue', $this->formatCurrency($this->metrics['total_revenue']))
                ->description('Overall revenue')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->chart($revenueTrend)
                ->color('success'),

            Stat::make('Total Expenses', $this->formatCurrency($this->metrics['total_expenseOps']))
                ->description('Overall expenses')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('danger'),
            
            Stat::make(
                'Total Uang Diterima (' . ($statusTarget instanceof \BackedEnum ? $statusTarget->value : $statusTarget) . ')',
                '' . Number::format($sumUangDiterimaUntukTargetOrder, precision: 0, locale: 'id')
            )
            ->description($descriptionText)
            ->descriptionIcon('heroicon-m-banknotes') // Ganti ikon jika perlu
            ->color('primary'), // Ganti warna jika perlu (success, warning, danger, etc.)
        ];
    }
}
