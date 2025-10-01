<?php

namespace App\Filament\Resources\FixedAssetResource\Pages;

use App\Filament\Resources\FixedAssetResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewFixedAsset extends ViewRecord
{
    protected static string $resource = FixedAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            
            Actions\Action::make('calculate_depreciation')
                ->label('Calculate Depreciation')
                ->icon('heroicon-o-calculator')
                ->color('info')
                ->action(function () {
                    if (!$this->record->isFullyDepreciated()) {
                        $monthlyDepreciation = $this->record->calculateMonthlyDepreciation();
                        $this->record->accumulated_depreciation += $monthlyDepreciation;
                        $this->record->updateBookValue();
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Depreciation Calculated')
                            ->body("Monthly depreciation: IDR " . number_format($monthlyDepreciation))
                            ->success()
                            ->send();
                            
                        $this->refreshFormData(['accumulated_depreciation', 'current_book_value']);
                    }
                })
                ->requiresConfirmation()
                ->visible(fn () => !$this->record->isFullyDepreciated()),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Asset Information')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('asset_code')
                                    ->label('Asset Code')
                                    ->copyable(),
                                
                                Infolists\Components\TextEntry::make('category')
                                    ->label('Category')
                                    ->formatStateUsing(fn ($state) => \App\Models\FixedAsset::CATEGORIES[$state] ?? $state)
                                    ->badge(),
                                
                                Infolists\Components\TextEntry::make('condition')
                                    ->label('Condition')
                                    ->formatStateUsing(fn ($state) => \App\Models\FixedAsset::CONDITIONS[$state] ?? $state)
                                    ->badge(),
                            ]),

                        Infolists\Components\TextEntry::make('asset_name')
                            ->label('Asset Name')
                            ->columnSpanFull(),

                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('location')
                                    ->label('Location'),
                                
                                Infolists\Components\IconEntry::make('is_active')
                                    ->label('Active')
                                    ->boolean(),
                            ]),
                    ]),

                Infolists\Components\Section::make('Purchase Information')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('purchase_date')
                                    ->label('Purchase Date')
                                    ->date('d M Y'),
                                
                                Infolists\Components\TextEntry::make('purchase_price')
                                    ->label('Purchase Price')
                                    ->money('IDR'),
                                
                                Infolists\Components\TextEntry::make('salvage_value')
                                    ->label('Salvage Value')
                                    ->money('IDR'),
                            ]),

                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('supplier')
                                    ->label('Supplier'),
                                
                                Infolists\Components\TextEntry::make('invoice_number')
                                    ->label('Invoice Number'),
                                
                                Infolists\Components\TextEntry::make('warranty_expiry')
                                    ->label('Warranty Expiry')
                                    ->date('d M Y'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Depreciation Information')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('depreciation_method')
                                    ->label('Method')
                                    ->formatStateUsing(fn ($state) => \App\Models\FixedAsset::DEPRECIATION_METHODS[$state] ?? $state),
                                
                                Infolists\Components\TextEntry::make('useful_life_years')
                                    ->label('Useful Life (Years)')
                                    ->suffix(' years'),
                                
                                Infolists\Components\TextEntry::make('useful_life_months')
                                    ->label('Additional Months')
                                    ->suffix(' months'),
                            ]),

                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('chartOfAccount.account_name')
                                    ->label('Asset Account')
                                    ->formatStateUsing(fn ($record) => 
                                        $record->chartOfAccount ? 
                                        "{$record->chartOfAccount->account_code} - {$record->chartOfAccount->account_name}" : 
                                        'Not set'),
                                
                                Infolists\Components\TextEntry::make('depreciationAccount.account_name')
                                    ->label('Depreciation Account')
                                    ->formatStateUsing(fn ($record) => 
                                        $record->depreciationAccount ? 
                                        "{$record->depreciationAccount->account_code} - {$record->depreciationAccount->account_name}" : 
                                        'Not set'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Current Status')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('accumulated_depreciation')
                                    ->label('Accumulated Depreciation')
                                    ->money('IDR'),
                                
                                Infolists\Components\TextEntry::make('current_book_value')
                                    ->label('Current Book Value')
                                    ->money('IDR')
                                    ->color(fn ($record) => $record->current_book_value <= $record->salvage_value ? 'danger' : 'success'),
                                
                                Infolists\Components\TextEntry::make('monthly_depreciation')
                                    ->label('Monthly Depreciation')
                                    ->state(fn ($record) => $record->calculateMonthlyDepreciation())
                                    ->money('IDR'),
                            ]),

                        Infolists\Components\TextEntry::make('notes')
                            ->label('Notes')
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Depreciation History')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('depreciations')
                            ->schema([
                                Infolists\Components\Grid::make(4)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('depreciation_date')
                                            ->label('Date')
                                            ->date('d M Y'),
                                        
                                        Infolists\Components\TextEntry::make('depreciation_amount')
                                            ->label('Amount')
                                            ->money('IDR'),
                                        
                                        Infolists\Components\TextEntry::make('book_value_after')
                                            ->label('Book Value After')
                                            ->money('IDR'),
                                        
                                        Infolists\Components\TextEntry::make('notes')
                                            ->label('Notes'),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => $record->depreciations()->exists()),
            ]);
    }
}
