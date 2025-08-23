<?php

namespace App\Filament\Resources\CategoryTenantResource\Pages;

use App\Filament\Resources\CategoryTenantResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategoryTenant extends EditRecord
{
    protected static string $resource = CategoryTenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
