<?php

namespace App\Filament\Resources\CategoryTenantResource\Pages;

use App\Filament\Resources\CategoryTenantResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCategoryTenant extends CreateRecord
{
    protected static string $resource = CategoryTenantResource::class;
}
