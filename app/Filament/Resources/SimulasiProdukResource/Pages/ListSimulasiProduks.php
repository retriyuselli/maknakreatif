<?php

namespace App\Filament\Resources\SimulasiProdukResource\Pages;

use App\Filament\Resources\SimulasiProdukResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSimulasiProduks extends ListRecords
{
    protected static string $resource = SimulasiProdukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
