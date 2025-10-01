<?php

namespace App\Filament\Resources\JournalBatchResource\Pages;

use App\Filament\Resources\JournalBatchResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJournalBatch extends EditRecord
{
    protected static string $resource = JournalBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
