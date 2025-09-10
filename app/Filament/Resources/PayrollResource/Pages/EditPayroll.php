<?php

namespace App\Filament\Resources\PayrollResource\Pages;

use App\Filament\Resources\PayrollResource;
use Filament\Actions;
use Filament\Actions\Modal\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditPayroll extends EditRecord
{
    protected static string $resource = PayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Hapus')
                ->icon('heroicon-o-trash'),
            
            Actions\ViewAction::make()
                ->label('Lihat Slip Gaji')
                ->icon('heroicon-o-document-text'),
                
            Actions\Action::make('view_history')
                ->label('Lihat Riwayat')
                ->icon('heroicon-o-clock')
                ->color('info')
                ->modalHeading('Riwayat Payroll')
                ->modalContent(function ($record) {
                    $notes = $record->notes ?? 'Tidak ada riwayat';
                    return view('filament.payroll.history-modal', ['notes' => $notes]);
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Payroll Berhasil Diperbarui')
            ->body('Data payroll karyawan telah berhasil diperbarui.');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Log perubahan dalam notes jika ada perubahan salary
        if (isset($data['monthly_salary']) && $this->record->monthly_salary != $data['monthly_salary']) {
            $oldSalary = $this->record->monthly_salary;
            $newSalary = $data['monthly_salary'];
            
            $changeLog = "[" . now()->format('d/m/Y H:i') . "] Perubahan gaji: " .
                        "Rp " . number_format($oldSalary, 0, ',', '.') . " → " .
                        "Rp " . number_format($newSalary, 0, ',', '.') . 
                        " (Diperbarui via sistem)";
            
            $data['notes'] = ($data['notes'] ? $data['notes'] . "\n\n" : '') . $changeLog;
        }

        return $data;
    }
}
