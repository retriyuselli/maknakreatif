<?php

namespace App\Filament\Resources\PayrollResource\Pages;

use App\Filament\Resources\PayrollResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreatePayroll extends CreateRecord
{
    protected static string $resource = PayrollResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Payroll Berhasil Dibuat')
            ->body('Data payroll karyawan telah berhasil ditambahkan ke sistem.');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Pastikan user_id tidak duplikat
        if (isset($data['user_id'])) {
            $existingPayroll = \App\Models\Payroll::where('user_id', $data['user_id'])->first();
            if ($existingPayroll) {
                Notification::make()
                    ->danger()
                    ->title('Error')
                    ->body('Karyawan ini sudah memiliki data payroll. Silakan edit data yang sudah ada.')
                    ->persistent()
                    ->send();
                
                $this->halt();
            }
        }

        return $data;
    }
}
