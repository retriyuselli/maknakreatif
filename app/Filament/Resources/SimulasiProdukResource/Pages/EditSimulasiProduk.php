<?php

namespace App\Filament\Resources\SimulasiProdukResource\Pages;

use App\Filament\Resources\SimulasiProdukResource;
use App\Models\SimulasiProduk;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSimulasiProduk extends EditRecord
{
    protected static string $resource = SimulasiProdukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('penawaran')
                ->label('Preview')
                ->color('success')
                ->icon('heroicon-o-eye')
                ->url(fn (SimulasiProduk $record) => route('simulasi.show', $record))
                ->openUrlInNewTab(),
        ];
    }
}
