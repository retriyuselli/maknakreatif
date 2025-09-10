<?php

namespace App\Filament\Resources\NotaDinasResource\Pages;

use App\Filament\Resources\NotaDinasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNotaDinas extends ListRecords
{
    protected static string $resource = NotaDinasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
