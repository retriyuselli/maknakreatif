<?php

namespace App\Filament\Resources\DataPembayaranResource\Pages;

use App\Filament\Resources\DataPembayaranResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDataPembayaran extends EditRecord
{
    protected static string $resource = DataPembayaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }
}
