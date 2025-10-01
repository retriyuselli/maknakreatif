<?php

namespace App\Filament\Resources\FixedAssetResource\Pages;

use App\Filament\Resources\FixedAssetResource;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Actions;

class DepreciationHistory extends Page
{
    use InteractsWithRecord;
    
    protected static string $resource = FixedAssetResource::class;
    protected static string $view = 'filament.resources.fixed-asset-resource.pages.depreciation-history';

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getTitle(): string
    {
        return 'Riwayat Penyusutan - ' . $this->record->asset_name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back_to_edit')
                ->label('Kembali ke Edit')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn () => static::getResource()::getUrl('edit', ['record' => $this->record])),
                
            Actions\Action::make('generate_depreciation')
                ->label('Generate Penyusutan')
                ->icon('heroicon-o-calculator')
                ->color('primary')
                ->action(function () {
                    $depreciationAmount = $this->record->calculateMonthlyDepreciation();
                    
                    if ($depreciationAmount > 0) {
                        $currentAccumulated = $this->record->accumulated_depreciation;
                        $newAccumulated = $currentAccumulated + $depreciationAmount;
                        $currentBookValue = $this->record->current_book_value;
                        $newBookValue = $this->record->purchase_price - $newAccumulated;
                        
                        // Create AssetDepreciation record
                        $this->record->depreciations()->create([
                            'depreciation_date' => now(),
                            'depreciation_amount' => $depreciationAmount,
                            'accumulated_depreciation_before' => $currentAccumulated,
                            'accumulated_depreciation_after' => $newAccumulated,
                            'book_value_before' => $currentBookValue,
                            'book_value_after' => $newBookValue,
                            'notes' => 'Penyusutan bulanan - ' . now()->format('M Y'),
                            'is_adjustment' => false,
                        ]);
                        
                        // Update asset accumulated depreciation
                        $this->record->update([
                            'accumulated_depreciation' => $newAccumulated,
                        ]);
                        $this->record->updateBookValue();
                        
                        // Create journal entry
                        $this->record->createDepreciationJournalEntry($depreciationAmount);
                        
                        $this->redirect(static::getResource()::getUrl('depreciation-history', ['record' => $this->record]));
                    }
                })
                ->requiresConfirmation()
                ->modalHeading('Generate Penyusutan Bulanan')
                ->modalDescription('Ini akan membuat entry penyusutan untuk bulan ini. Lanjutkan?')
                ->visible(fn () => $this->record && !$this->record->isFullyDepreciated()),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->record)
            ->schema([
                Infolists\Components\Section::make('Informasi Aset')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('asset_code')
                                    ->label('Kode Aset')
                                    ->badge()
                                    ->color('primary'),
                                    
                                Infolists\Components\TextEntry::make('asset_name')
                                    ->label('Nama Aset')
                                    ->weight('bold'),
                                    
                                Infolists\Components\TextEntry::make('category')
                                    ->label('Kategori')
                                    ->badge()
                                    ->color('info')
                                    ->formatStateUsing(fn ($state) => \App\Models\FixedAsset::CATEGORIES[$state] ?? $state),
                            ]),
                            
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('purchase_price')
                                    ->label('Harga Pembelian')
                                    ->money('IDR'),
                                    
                                Infolists\Components\TextEntry::make('accumulated_depreciation')
                                    ->label('Akumulasi Penyusutan')
                                    ->money('IDR')
                                    ->color('warning'),
                                    
                                Infolists\Components\TextEntry::make('current_book_value')
                                    ->label('Nilai Buku')
                                    ->money('IDR')
                                    ->color('success'),
                                    
                                Infolists\Components\TextEntry::make('useful_life_years')
                                    ->label('Masa Manfaat')
                                    ->suffix(' tahun')
                                    ->formatStateUsing(fn ($state, $record) => 
                                        $state . ($record->useful_life_months ? " {$record->useful_life_months} bulan" : '')),
                            ]),
                    ]),
                    
                Infolists\Components\Section::make('Riwayat Penyusutan')
                    ->description('Entry penyusutan bulanan untuk aset ini')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('depreciations')
                            ->label('')
                            ->schema([
                                Infolists\Components\Grid::make(5)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('depreciation_date')
                                            ->label('Tanggal')
                                            ->date('M Y')
                                            ->weight('bold'),
                                            
                                        Infolists\Components\TextEntry::make('depreciation_amount')
                                            ->label('Jumlah Penyusutan')
                                            ->money('IDR')
                                            ->color('warning'),
                                            
                                        Infolists\Components\TextEntry::make('accumulated_depreciation_after')
                                            ->label('Akumulasi')
                                            ->money('IDR')
                                            ->color('danger'),
                                            
                                        Infolists\Components\TextEntry::make('book_value_after')
                                            ->label('Nilai Buku')
                                            ->money('IDR')
                                            ->color('success'),
                                            
                                        Infolists\Components\TextEntry::make('notes')
                                            ->label('Catatan')
                                            ->default('-')
                                            ->color('gray'),
                                    ]),
                            ])
                            ->columnSpanFull()
                            ->visible(fn () => $this->record->depreciations->count() > 0),
                            
                        Infolists\Components\TextEntry::make('no_depreciation')
                            ->label('')
                            ->default('Tidak ada entry penyusutan. Klik "Generate Penyusutan" untuk membuat entry bulanan.')
                            ->color('gray')
                            ->visible(fn () => $this->record->depreciations->count() === 0),
                    ]),
            ]);
    }
}
