<?php

namespace App\Filament\Resources\SopCategoryResource\Pages;

use App\Filament\Resources\SopCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSopCategory extends EditRecord
{
    protected static string $resource = SopCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
