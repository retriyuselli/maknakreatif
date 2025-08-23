<?php

namespace App\Filament\Resources\ExpenseOpsResource\Pages;

use App\Filament\Resources\ExpenseOpsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExpenseOps extends EditRecord
{
    protected static string $resource = ExpenseOpsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
