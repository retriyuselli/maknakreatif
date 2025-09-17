<?php

namespace App\Filament\Resources\PiutangResource\Pages;

use App\Filament\Resources\PiutangResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPiutang extends ViewRecord
{
    protected static string $resource = PiutangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('terima_pembayaran')
                ->label('Terima Pembayaran')
                ->icon('heroicon-o-currency-dollar')
                ->color('success')
                // ->url(fn () => \App\Filament\Resources\PembayaranPiutangResource::getUrl('create', ['piutang_id' => $this->record->id]))
                ->visible(fn () => in_array($this->record->status, ['aktif', 'dibayar_sebagian', 'jatuh_tempo'])),
        ];
    }
}
