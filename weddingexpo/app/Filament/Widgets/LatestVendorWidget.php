<?php

namespace App\Filament\Widgets;

use App\Models\Vendor;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;

class LatestVendorWidget extends BaseWidget
{
    protected static ?string $heading = 'Vendor Terbaru';

    protected static ?int $sort = 4;


    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(Vendor::query()->latest('created_at')->limit(5))
            ->columns([
                Tables\Columns\TextColumn::make('nama_vendor')->label('Nama Vendor'),
                Tables\Columns\TextColumn::make('jenisUsaha.nama_jenis_usaha')->label('Kategori'),
                Tables\Columns\TextColumn::make('created_at')->label('Tanggal Daftar')->date('d M Y'),
            ]);
    }
}
