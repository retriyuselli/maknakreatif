<?php

namespace App\Filament\Resources\PendapatanLainResource\Pages;

use App\Filament\Resources\PendapatanLainResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPendapatanLain extends EditRecord
{
    protected static string $resource = PendapatanLainResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
