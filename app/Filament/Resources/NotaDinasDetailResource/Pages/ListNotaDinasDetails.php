<?php

namespace App\Filament\Resources\NotaDinasDetailResource\Pages;

use App\Filament\Resources\NotaDinasDetailResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNotaDinasDetails extends ListRecords
{
    protected static string $resource = NotaDinasDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
