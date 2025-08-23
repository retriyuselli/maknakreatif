<?php

namespace App\Filament\Resources\AccountManagerTargetResource\Pages;

use App\Filament\Resources\AccountManagerTargetResource;
use App\Models\AccountManagerTarget;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAccountManagerTarget extends EditRecord
{
    protected static string $resource = AccountManagerTargetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function resolveRecord($key): AccountManagerTarget
    {
        // Explicitly resolve the record without the custom query modifications
        return AccountManagerTarget::findOrFail($key);
    }
}
