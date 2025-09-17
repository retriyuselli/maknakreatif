<?php

namespace App\Filament\Resources\PembayaranPiutangResource\Pages;

use App\Filament\Resources\PembayaranPiutangResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditPembayaranPiutang extends EditRecord
{
    protected static string $resource = PembayaranPiutangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
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
            ->title('Pembayaran Piutang berhasil diupdate')
            ->body('Perubahan data pembayaran piutang telah berhasil disimpan.');
    }
}
