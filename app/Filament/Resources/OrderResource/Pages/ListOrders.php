<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification; // Add this
use Illuminate\Support\Carbon; // Add this

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\ActionGroup::make([
                Actions\Action::make('downloadProfitLossReport')
                    ->label('Download Laporan (PDF)')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function () {
                        // Get the current table query builder including filters
                        $baseQuery = $this->getFilteredTableQuery();
                        $query = $baseQuery->with(['prospect', 'dataPembayaran', 'expenses']);
                        // Fetch the orders based on the current filters
                        $orders = $query->get();

                        // --- Sanitize Potential UTF-8 Issues ---
                        foreach ($orders as $order) {
                            if ($order->prospect && !mb_check_encoding($order->prospect->name_event ?? '', 'UTF-8')) {
                                Log::warning("Malformed UTF-8 in prospect->name_event for Order ID: " . $order->id . " | Data: " . ($order->prospect->name_event ?? 'NULL'));
                                $order->prospect->name_event = iconv('UTF-8', 'UTF-8//IGNORE', $order->prospect->name_event ?? '');
                            }
                        }
                        
                        // --- End Debugging UTF-8 ---
                        if ($orders->isEmpty()) {
                            Notification::make()
                                ->warning()
                                ->title('Tidak Ada Order')
                                ->body('Tidak ada data order yang cocok dengan filter saat ini untuk membuat laporan.')
                                ->send();
                            return;
                        }

                        // Calculate totals based on the requirements for profit_loss_report.blade.php
                        $totalPaymentsReceived = $orders->sum('bayar'); // Total Pemasukan (Diterima)
                        $totalOrderValue = $orders->sum('grand_total'); // Nilai Order (Grand Total)
                        $totalActualExpenses = $orders->sum('tot_pengeluaran'); // Total Pengeluaran Aktual

                        // Laba bersih dihitung berdasarkan total nilai order dikurangi total pengeluaran aktual
                        $netProfitCalculation = $totalOrderValue - $totalActualExpenses;

                        // Prepare data for the PDF view
                        $reportData = [
                            'orders' => $orders,
                            'totalIncome' => $totalPaymentsReceived, // Passed as 'totalIncome' to Blade
                            'totalExpenses' => $totalOrderValue,     // Passed as 'totalExpenses' to Blade (for Grand Total column)
                            'sumAllOrdersPengeluaran' => $totalActualExpenses, // Passed as 'sumAllOrdersPengeluaran' to Blade
                            'netProfit' => $netProfitCalculation,     // Passed as 'netProfit' to Blade
                            // Attempt to get filter dates (adjust key if your filter key is different)
                            'filterStartDate' => $this->tableFilters['event_dates']['from_date'] ?? null,
                            'filterEndDate' => $this->tableFilters['event_dates']['until_date'] ?? null,
                            'generatedDate' => now()->format('d M Y H:i'),
                        ];

                        // Generate PDF using a Blade view
                        $pdf = Pdf::loadView('pdf.profit_loss_report', $reportData);

                        // Return download response
                        return response()->streamDownload(fn() => print($pdf->output()), 'laporan_laba_rugi_' . now()->format('YmdHis') . '.pdf');
                    })
                    ->tooltip('Download laporan Laba Rugi (PDF) berdasarkan filter saat ini.'),
                ])->label('Laporan L/R')->button()->color('warning'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            OrderResource\Widgets\OrderOverview::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make('All'),
            'pending' => Tab::make()->query(fn ($query) => $query->where('status', 'pending')),
            'processing' => Tab::make()->query(fn ($query) => $query->where('status', 'processing')),
            'done' => Tab::make()->query(fn ($query) => $query->where('status', 'done')),
            'cancelled' => Tab::make()->query(fn ($query) => $query->where('status', 'cancelled')),
        ];
    }
}
