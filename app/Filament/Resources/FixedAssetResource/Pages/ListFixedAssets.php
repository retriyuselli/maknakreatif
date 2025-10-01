<?php

namespace App\Filament\Resources\FixedAssetResource\Pages;

use App\Filament\Resources\FixedAssetResource;
use App\Models\FixedAsset;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListFixedAssets extends ListRecords
{
    protected static string $resource = FixedAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('bulk_depreciation')
                ->label('Hitung Semua Penyusutan')
                ->icon('heroicon-o-calculator')
                ->color('info')
                ->action(function () {
                    $assets = FixedAsset::where('is_active', true)
                        ->whereColumn('current_book_value', '>', 'salvage_value')
                        ->get();
                    
                    $totalDepreciation = 0;
                    $processedCount = 0;
                    
                    foreach ($assets as $asset) {
                        if (!$asset->isFullyDepreciated()) {
                            $monthlyDepreciation = $asset->calculateMonthlyDepreciation();
                            $asset->accumulated_depreciation += $monthlyDepreciation;
                            $asset->updateBookValue();
                            $totalDepreciation += $monthlyDepreciation;
                            $processedCount++;
                        }
                    }
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Penyusutan Bulk Selesai')
                        ->body("Diproses {$processedCount} aset. Total penyusutan: IDR " . number_format($totalDepreciation))
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading('Hitung Penyusutan untuk Semua Aset Aktif')
                ->modalDescription('Ini akan menghitung penyusutan bulanan untuk semua aset aktif yang belum sepenuhnya tersusut.')
                ->modalSubmitActionLabel('Hitung'),

            Actions\CreateAction::make()
                ->label('Tambah Aset'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua Aset'),
            
            'active' => Tab::make('Aktif')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(FixedAsset::query()->where('is_active', true)->count()),
            
            'inactive' => Tab::make('Tidak Aktif')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->badge(FixedAsset::query()->where('is_active', false)->count()),
            
            'needs_maintenance' => Tab::make('Perlu Maintenance')
                ->modifyQueryUsing(fn (Builder $query) => $query->needsMaintenance())
                ->badge(FixedAsset::query()->needsMaintenance()->count()),
            
            'fully_depreciated' => Tab::make('Tersusut Penuh')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereColumn('current_book_value', '<=', 'salvage_value'))
                ->badge(FixedAsset::query()->whereColumn('current_book_value', '<=', 'salvage_value')->count()),
        ];
    }
}
