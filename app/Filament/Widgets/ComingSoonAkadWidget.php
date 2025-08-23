<?php
namespace App\Filament\Widgets;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class ComingSoonAkadWidget extends BaseWidget
{
    protected static ?string $heading = 'Coming Soon Akad';
    protected static ?int $sort = 7;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                OrderResource::getEloquentQuery()
                    ->whereHas('prospect', function (Builder $query) {
                        $query->whereNotNull('date_akad')
                              ->where('date_akad', '>=', now());
                    })
            )
            ->defaultPaginationPageOption(5)
            ->defaultSort('prospect.date_akad', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('prospect.date_akad')
                    ->label('Tgl Akad')
                    ->date('d-m-Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('prospect.name_event')
                    ->label('Nama Event')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Account Manager')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->url(fn (Order $record): string => OrderResource::getUrl('edit', ['record' => $record])),
                ])
            ]);
    }
}