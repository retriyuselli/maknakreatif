<?php

namespace App\Filament\Resources\ProspectAppResource\Pages;

use App\Filament\Resources\ProspectAppResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProspectApp extends EditRecord
{
    protected static string $resource = ProspectAppResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
