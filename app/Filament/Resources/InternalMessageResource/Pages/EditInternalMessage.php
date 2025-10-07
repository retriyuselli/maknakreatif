<?php

namespace App\Filament\Resources\InternalMessageResource\Pages;

use App\Filament\Resources\InternalMessageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInternalMessage extends EditRecord
{
    protected static string $resource = InternalMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
