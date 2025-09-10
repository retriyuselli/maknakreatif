<?php

namespace App\Filament\Resources\CompanyLogoResource\Pages;

use App\Filament\Resources\CompanyLogoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompanyLogos extends ListRecords
{
    protected static string $resource = CompanyLogoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
