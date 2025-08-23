<?php

namespace App\Filament\Resources\PengeluaranLainResource\Pages;

use App\Filament\Resources\PengeluaranLainResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPengeluaranLain extends EditRecord
{
    protected static string $resource = PengeluaranLainResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
