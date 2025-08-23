<?php

namespace App\Filament\Resources\CategoryTenantResource\Pages;

use App\Filament\Resources\CategoryTenantResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCategoryTenants extends ListRecords
{
    protected static string $resource = CategoryTenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
