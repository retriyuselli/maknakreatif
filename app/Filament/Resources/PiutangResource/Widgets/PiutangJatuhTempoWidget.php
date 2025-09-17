<?php

namespace App\Filament\Resources\PiutangResource\Widgets;

use App\Models\Piutang;
use App\Enums\StatusPiutang;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class PiutangJatuhTempoWidget extends BaseWidget
{
    protected static ?string $heading = 'Piutang yang Perlu Follow-up (Overdue + 30 Hari ke Depan)';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Piutang::query()
                    ->whereIn('status', [
                        StatusPiutang::AKTIF,
                        StatusPiutang::DIBAYAR_SEBAGIAN,
                        StatusPiutang::JATUH_TEMPO
                    ])
                    ->where('sisa_piutang', '>', 0) // Hanya yang masih punya sisa
                    ->where(function($query) {
                        // Piutang yang akan jatuh tempo dalam 30 hari OR sudah jatuh tempo
                        $query->where('tanggal_jatuh_tempo', '<=', now()->addDays(30))
                              ->where('tanggal_jatuh_tempo', '>=', now()->subDays(90)); // Termasuk yang overdue max 90 hari
                    })
                    ->orderBy('tanggal_jatuh_tempo', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('nomor_piutang')
                    ->label('Nomor Piutang')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('nama_debitur')
                    ->label('Debitur')
                    ->searchable()
                    ->limit(25),

                Tables\Columns\TextColumn::make('sisa_piutang')
                    ->label('Sisa Piutang')
                    ->money('IDR')
                    ->color('danger')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('tanggal_jatuh_tempo')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->color(fn ($record) => $record->tanggal_jatuh_tempo <= now()->addDays(7) ? 'danger' : 'warning')
                    ->badge()
                    ->formatStateUsing(function ($state, $record) {
                        $days = now()->diffInDays($record->tanggal_jatuh_tempo, false);
                        if ($days < 0) {
                            return $state . ' (Terlambat ' . abs($days) . ' hari)';
                        } elseif ($days <= 7) {
                            return $state . ' (' . $days . ' hari lagi)';
                        }
                        return $state;
                    }),

                Tables\Columns\TextColumn::make('kontak_debitur')
                    ->label('Kontak')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        StatusPiutang::AKTIF => 'primary',
                        StatusPiutang::JATUH_TEMPO => 'danger',
                        StatusPiutang::LUNAS => 'success',
                        StatusPiutang::DIBAYAR_SEBAGIAN => 'warning',
                        default => 'gray',
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('hubungi')
                    ->label('Hubungi')
                    ->icon('heroicon-o-phone')
                    ->color('primary')
                    ->url(fn ($record) => $record->kontak_debitur ? 'tel:' . $record->kontak_debitur : null)
                    ->openUrlInNewTab(false)
                    ->visible(fn ($record) => $record->kontak_debitur),

                Tables\Actions\Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->url(function ($record) {
                        if (!$record->kontak_debitur) return null;
                        $phone = preg_replace('/[^0-9]/', '', $record->kontak_debitur);
                        if (substr($phone, 0, 1) === '0') {
                            $phone = '62' . substr($phone, 1);
                        }
                        $message = urlencode("Halo {$record->nama_debitur}, kami ingin mengingatkan bahwa piutang dengan nomor {$record->nomor_piutang} akan jatuh tempo pada " . $record->tanggal_jatuh_tempo->format('d M Y') . ". Sisa pembayaran sebesar Rp " . number_format($record->sisa_piutang, 0, ',', '.') . ". Terima kasih.");
                        return "https://wa.me/{$phone}?text={$message}";
                    })
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->kontak_debitur),

                Tables\Actions\Action::make('view')
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('filament.admin.resources.piutangs.view', $record))
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('Tidak ada piutang yang perlu difollow-up')
            ->emptyStateDescription('Semua piutang dalam kondisi baik untuk 30 hari ke depan')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
