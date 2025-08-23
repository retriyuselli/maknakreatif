<?php

namespace App\Filament\Resources\RekeningTujuanResource\Pages;

use App\Filament\Resources\RekeningTujuanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRekeningTujuans extends ListRecords
{
    protected static string $resource = RekeningTujuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
