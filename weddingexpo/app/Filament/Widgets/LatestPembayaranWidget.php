<?php

namespace App\Filament\Widgets;

use App\Models\DataPembayaran;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;

class LatestPembayaranWidget extends BaseWidget
{
    protected static ?string $heading = 'Pembayaran Terbaru';

    protected static ?int $sort = 3;


    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(DataPembayaran::query()->latest('tanggal_bayar')->limit(5))
            ->columns([
                Tables\Columns\TextColumn::make('nama_pembayar')->label('Nama Pembayar'),
                Tables\Columns\TextColumn::make('nominal')->label('Nominal')->prefix('Rp. ')->numeric(),
                Tables\Columns\TextColumn::make('tanggal_bayar')->label('Tanggal Bayar')->date('d M Y'),
                Tables\Columns\TextColumn::make('metode_pembayaran')->label('Metode'),
            ]);
    }
}
