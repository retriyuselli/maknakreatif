<?php

namespace App\Filament\Resources\RekeningTujuanResource\Pages;

use App\Filament\Resources\RekeningTujuanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRekeningTujuan extends EditRecord
{
    protected static string $resource = RekeningTujuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
