<?php

namespace App\Filament\Resources\AccountManagerTargetResource\Pages;

use App\Filament\Resources\AccountManagerTargetResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditAccountManagerTarget extends EditRecord
{
    protected static string $resource = AccountManagerTargetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('refresh_achieved')
                ->label('Refresh Pencapaian')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->action(function () {
                    $record = $this->getRecord();
                    $achieved = Order::where('user_id', $record->user_id)
                        ->whereYear('closing_date', $record->year)
                        ->whereMonth('closing_date', $record->month)
                        ->sum('grand_total') ?? 0;
                    
                    $record->update(['achieved_amount' => $achieved]);
                    
                    $this->fillForm();
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Pencapaian berhasil di-refresh')
                        ->success()
                        ->send();
                }),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Auto-calculate achieved amount when loading the form
        $record = $this->getRecord();
        $achieved = Order::where('user_id', $record->user_id)
            ->whereYear('closing_date', $record->year)
            ->whereMonth('closing_date', $record->month)
            ->sum('grand_total') ?? 0;
        
        $data['achieved_amount'] = $achieved;
        
        return $data;
    }
}
