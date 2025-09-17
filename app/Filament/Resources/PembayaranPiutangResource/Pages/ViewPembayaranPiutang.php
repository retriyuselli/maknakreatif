<?php

namespace App\Filament\Resources\PembayaranPiutangResource\Pages;

use App\Filament\Resources\PembayaranPiutangResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPembayaranPiutang extends ViewRecord
{
    protected static string $resource = PembayaranPiutangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
