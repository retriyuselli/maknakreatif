<?php

namespace App\Filament\Resources\PartisipasiResource\Pages;

use App\Filament\Resources\PartisipasiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPartisipasi extends EditRecord
{
    protected static string $resource = PartisipasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
