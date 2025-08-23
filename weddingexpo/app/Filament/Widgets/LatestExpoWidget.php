<?php

namespace App\Filament\Widgets;

use App\Models\Expo;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;

class LatestExpoWidget extends BaseWidget
{
    protected static ?string $heading = 'Expo Terbaru';

    protected static ?int $sort = 1;

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(Expo::query()->latest('created_at')->limit(5))
            ->columns([
                Tables\Columns\TextColumn::make('nama_expo')->label('Nama Expo'),
                Tables\Columns\TextColumn::make('tanggal_mulai')->label('Tanggal Mulai')->date('d M Y'),
                Tables\Columns\TextColumn::make('tanggal_selesai')->label('Tanggal Selesai')->date('d M Y'),
            ]);
    }
}
