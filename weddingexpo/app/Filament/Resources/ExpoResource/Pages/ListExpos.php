<?php

namespace App\Filament\Resources\ExpoResource\Pages;

use App\Filament\Resources\ExpoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExpos extends ListRecords
{
    protected static string $resource = ExpoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
