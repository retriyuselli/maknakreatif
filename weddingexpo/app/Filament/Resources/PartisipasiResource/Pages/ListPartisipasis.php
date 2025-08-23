<?php

namespace App\Filament\Resources\PartisipasiResource\Pages;

use App\Filament\Resources\PartisipasiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPartisipasis extends ListRecords
{
    protected static string $resource = PartisipasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
