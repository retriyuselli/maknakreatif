<?php

namespace App\Filament\Resources\DataPribadiResource\Pages;

use App\Filament\Resources\DataPribadiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDataPribadi extends EditRecord
{
    protected static string $resource = DataPribadiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
