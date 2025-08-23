<?php

namespace App\Filament\Resources\ProspectAppResource\Pages;

use App\Filament\Resources\ProspectAppResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProspectApps extends ListRecords
{
    protected static string $resource = ProspectAppResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
