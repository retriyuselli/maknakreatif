<?php

namespace App\Filament\Resources\DataPribadiResource\Pages;

use App\Filament\Resources\DataPribadiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDataPribadis extends ListRecords
{
    protected static string $resource = DataPribadiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('linkToDataPribadi')
                ->label('Link to Data Pribadi')
                ->icon('heroicon-o-link')
                ->url(route('data-pribadi.create')) // Menggunakan nama rute yang benar
                ->color('primary'),
        ];
    }
}
