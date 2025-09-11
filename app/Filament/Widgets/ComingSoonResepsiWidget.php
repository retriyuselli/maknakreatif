<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ComingSoonResepsiWidget extends BaseWidget
{
    use HasWidgetShield;
    
    protected static ?string $heading = 'Coming Soon Resepsi';

    protected static ?int $sort = 8;

    public function table(Table $table): Table
    {
        return $table
        ->query(
            OrderResource::getEloquentQuery()
                ->whereHas('prospect', function (Builder $query) {
                    $query->whereNotNull('date_resepsi')
                          ->where('date_resepsi', '>=', now());
                })
        )
            ->defaultPaginationPageOption(5)
            ->defaultSort('prospect.date_resepsi', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('prospect.date_resepsi')
                    ->label('Tgl Resepsi')
                    ->date('d-m-Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('prospect.name_event')
                    ->label('Prospect')
                    ->label('Nama event')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->label('Account Manager')
                    ->sortable(),
                // Tables\Columns\TextColumn::make('items.product.name')
                //     ->label('Product')
                //     ->sortable()
                //     ->wrap(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->url(fn (Order $record): string => OrderResource::getUrl('edit', ['record' => $record])),
                ])
            ]);
    }
}
