<?php

namespace App\Filament\Resources\AccountManagerTargetResource\Pages;

use App\Filament\Resources\AccountManagerTargetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAccountManagerTargets extends ListRecords
{
    protected static string $resource = AccountManagerTargetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
