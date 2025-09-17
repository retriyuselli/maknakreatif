<?php

namespace App\Filament\Resources\PembayaranPiutangResource\Pages;

use App\Filament\Resources\PembayaranPiutangResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPembayaranPiutangs extends ListRecords
{
    protected static string $resource = PembayaranPiutangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
