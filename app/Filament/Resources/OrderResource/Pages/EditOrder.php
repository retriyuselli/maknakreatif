<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;


class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->visible(Auth::user()->hasRole('super_admin'))
                ->color('danger'),
            Actions\Action::make('Invoice')
                ->label('Detail')
                ->color('success')
                ->icon('heroicon-o-eye')
                ->url(fn ($record) => OrderResource::getUrl('invoice', ['record' => $record->id]))
                ->openUrlInNewTab(),
        ];
    }

}
