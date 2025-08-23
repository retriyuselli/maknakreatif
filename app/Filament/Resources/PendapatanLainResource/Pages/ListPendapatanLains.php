<?php

namespace App\Filament\Resources\PendapatanLainResource\Pages;

use App\Filament\Resources\PendapatanLainResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPendapatanLains extends ListRecords
{
    protected static string $resource = PendapatanLainResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
