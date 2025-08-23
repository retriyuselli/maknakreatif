<?php

namespace App\Filament\Widgets;

use App\Models\Partisipasi;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;

class LatestPartisipasiWidget extends BaseWidget
{
    protected static ?string $heading = 'Partisipasi Terbaru';

    protected static ?int $sort = 2;

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(Partisipasi::query()->latest('created_at')->limit(5))
            ->columns([
                Tables\Columns\TextColumn::make('expo.nama_expo')->label('Expo'),
                Tables\Columns\TextColumn::make('vendor.nama_vendor')->label('Vendor'),
                Tables\Columns\TextColumn::make('tanggal_booking')->label('Tanggal Booking')->date('d M Y'),
                Tables\Columns\TextColumn::make('status_pembayaran')->label('Status')->badge(),
                Tables\Columns\TextColumn::make('tot_nominal')->label('Total Dibayar')->prefix('Rp. ')->numeric(),
            ]);
    }
}
