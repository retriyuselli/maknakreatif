<?php

namespace App\Filament\Resources\CompanyLogoResource\Pages;

use App\Filament\Resources\CompanyLogoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompanyLogo extends EditRecord
{
    protected static string $resource = CompanyLogoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
