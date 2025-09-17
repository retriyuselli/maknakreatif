<?php

namespace App\Filament\Resources\PembayaranPiutangResource\Pages;

use App\Filament\Resources\PembayaranPiutangResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreatePembayaranPiutang extends CreateRecord
{
    protected static string $resource = PembayaranPiutangResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Pembayaran Piutang berhasil dicatat')
            ->body('Pembayaran piutang telah berhasil disimpan.');
    }
}
