<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PembayaranPiutangResource\Pages;
use App\Models\PembayaranPiutang;
use App\Models\Piutang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\FontWeight;

class PembayaranPiutangResource extends Resource
{
    protected static ?string $model = PembayaranPiutang::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Pembayaran Piutang';

    protected static ?string $modelLabel = 'Pembayaran Piutang';

    protected static ?string $pluralModelLabel = 'Pembayaran Piutang';

    protected static ?string $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pembayaran')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('piutang_id')
                                    ->label('Piutang')
                                    ->relationship('piutang', 'nomor_piutang', function (Builder $query) {
                                        return $query->whereIn('status', ['aktif', 'dibayar_sebagian', 'jatuh_tempo']);
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        if ($state) {
                                            $piutang = Piutang::find($state);
                                            $set('max_pembayaran', $piutang->sisa_piutang);
                                        }
                                    }),

                                Forms\Components\TextInput::make('nomor_pembayaran')
                                    ->label('Nomor Pembayaran')
                                    ->default(fn () => PembayaranPiutang::generateNomorPembayaran())
                                    ->disabled()
                                    ->dehydrated()
                                    ->required(),
                            ]),

                        Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\Placeholder::make('info_piutang')
                                    ->label('Informasi Piutang')
                                    ->content(function (Forms\Get $get) {
                                        $piutangId = $get('piutang_id');
                                        if (!$piutangId) {
                                            return 'Pilih piutang terlebih dahulu';
                                        }
                                        
                                        $piutang = Piutang::find($piutangId);
                                        return "
                                            Debitur: {$piutang->nama_debitur}
                                            Total Piutang: Rp " . number_format($piutang->total_piutang, 0, ',', '.') . "
                                            Sudah Dibayar: Rp " . number_format($piutang->sudah_dibayar, 0, ',', '.') . "
                                            Sisa Piutang: Rp " . number_format($piutang->sisa_piutang, 0, ',', '.') . "
                                        ";
                                    })
                                    ->visible(fn (Forms\Get $get) => $get('piutang_id')),
                            ]),
                    ]),

                Forms\Components\Section::make('Detail Pembayaran')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('jumlah_pembayaran')
                                    ->label('Jumlah Pembayaran')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        $pembayaran = (float) $state;
                                        $bunga = (float) $get('jumlah_bunga') ?? 0;
                                        $denda = (float) $get('denda') ?? 0;
                                        $set('total_pembayaran', $pembayaran + $bunga + $denda);
                                    }),

                                Forms\Components\TextInput::make('jumlah_bunga')
                                    ->label('Bunga')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0)
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        $pembayaran = (float) $get('jumlah_pembayaran') ?? 0;
                                        $bunga = (float) $state ?? 0;
                                        $denda = (float) $get('denda') ?? 0;
                                        $set('total_pembayaran', $pembayaran + $bunga + $denda);
                                    }),

                                Forms\Components\TextInput::make('denda')
                                    ->label('Denda')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0)
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        $pembayaran = (float) $get('jumlah_pembayaran') ?? 0;
                                        $bunga = (float) $get('jumlah_bunga') ?? 0;
                                        $denda = (float) $state ?? 0;
                                        $set('total_pembayaran', $pembayaran + $bunga + $denda);
                                    }),
                            ]),

                        Forms\Components\TextInput::make('total_pembayaran')
                            ->label('Total Pembayaran')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated(),
                    ]),

                Forms\Components\Section::make('Metode & Tanggal')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('payment_method_id')
                                    ->label('Metode Pembayaran')
                                    ->relationship('paymentMethod', 'name')
                                    ->required(),

                                Forms\Components\DatePicker::make('tanggal_pembayaran')
                                    ->label('Tanggal Pembayaran')
                                    ->required()
                                    ->default(now()),

                                Forms\Components\DatePicker::make('tanggal_dicatat')
                                    ->label('Tanggal Dicatat')
                                    ->default(now())
                                    ->required(),
                            ]),
                    ]),

                Forms\Components\Section::make('Referensi & Konfirmasi')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('nomor_referensi')
                                    ->label('Nomor Referensi')
                                    ->placeholder('Nomor referensi bank/transfer'),

                                Forms\Components\Select::make('dikonfirmasi_oleh')
                                    ->label('Dikonfirmasi Oleh')
                                    ->relationship('dikonfirmasiOleh', 'name')
                                    ->default(Auth::id()),
                            ]),
                    ]),

                Forms\Components\Section::make('Status & Catatan')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'dikonfirmasi' => 'Dikonfirmasi',
                                        'dibatalkan' => 'Dibatalkan',
                                    ])
                                    ->default('pending')
                                    ->required(),

                                Forms\Components\FileUpload::make('bukti_pembayaran')
                                    ->label('Bukti Pembayaran')
                                    ->multiple()
                                    ->acceptedFileTypes(['application/pdf', 'image/*'])
                                    ->maxSize(2048),
                            ]),

                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan')
                            ->placeholder('Catatan tambahan pembayaran')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Hidden::make('dibayar_oleh')
                    ->default(Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_pembayaran')
                    ->label('Nomor Pembayaran')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                Tables\Columns\TextColumn::make('piutang.nomor_piutang')
                    ->label('Nomor Piutang')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('piutang.nama_debitur')
                    ->label('Debitur')
                    ->searchable()
                    ->limit(20),

                Tables\Columns\TextColumn::make('total_pembayaran')
                    ->label('Total Pembayaran')
                    ->money('IDR')
                    ->sortable()
                    ->weight(FontWeight::Bold),

                Tables\Columns\TextColumn::make('paymentMethod.name')
                    ->label('Metode'),

                Tables\Columns\TextColumn::make('tanggal_pembayaran')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'pending' => 'warning',
                        'dikonfirmasi' => 'success',
                        'dibatalkan' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('dikonfirmasiOleh.name')
                    ->label('Dikonfirmasi Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'dikonfirmasi' => 'Dikonfirmasi',
                        'dibatalkan' => 'Dibatalkan',
                    ]),

                Tables\Filters\SelectFilter::make('payment_method_id')
                    ->label('Metode Pembayaran')
                    ->relationship('paymentMethod', 'name'),

                Tables\Filters\Filter::make('tanggal_pembayaran')
                    ->form([
                        Forms\Components\DatePicker::make('dari'),
                        Forms\Components\DatePicker::make('sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_pembayaran', '>=', $date),
                            )
                            ->when(
                                $data['sampai'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_pembayaran', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('konfirmasi')
                    ->label('Konfirmasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (PembayaranPiutang $record) {
                        $record->update([
                            'status' => 'dikonfirmasi',
                            'dikonfirmasi_oleh' => Auth::id(),
                        ]);
                    })
                    ->visible(fn (PembayaranPiutang $record) => $record->status === 'pending'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Pembayaran')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('nomor_pembayaran')
                                    ->label('Nomor Pembayaran')
                                    ->weight(FontWeight::Bold),

                                Infolists\Components\TextEntry::make('piutang.nomor_piutang')
                                    ->label('Nomor Piutang')
                                    ->weight(FontWeight::Bold),

                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn ($state) => match($state) {
                                        'pending' => 'warning',
                                        'dikonfirmasi' => 'success',
                                        'dibatalkan' => 'danger',
                                        default => 'gray',
                                    }),
                            ]),

                        Infolists\Components\TextEntry::make('piutang.nama_debitur')
                            ->label('Debitur'),

                        Infolists\Components\TextEntry::make('catatan')
                            ->label('Catatan')
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record->catatan),
                    ]),

                Infolists\Components\Section::make('Detail Keuangan')
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('jumlah_pembayaran')
                                    ->label('Jumlah Pembayaran')
                                    ->money('IDR'),

                                Infolists\Components\TextEntry::make('jumlah_bunga')
                                    ->label('Bunga')
                                    ->money('IDR'),

                                Infolists\Components\TextEntry::make('denda')
                                    ->label('Denda')
                                    ->money('IDR'),

                                Infolists\Components\TextEntry::make('total_pembayaran')
                                    ->label('Total Pembayaran')
                                    ->money('IDR')
                                    ->weight(FontWeight::Bold),
                            ]),
                    ]),

                Infolists\Components\Section::make('Informasi Piutang')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('piutang.total_piutang')
                                    ->label('Total Piutang')
                                    ->money('IDR'),

                                Infolists\Components\TextEntry::make('piutang.sudah_dibayar')
                                    ->label('Sudah Dibayar')
                                    ->money('IDR')
                                    ->color('success'),

                                Infolists\Components\TextEntry::make('piutang.sisa_piutang')
                                    ->label('Sisa Piutang')
                                    ->money('IDR')
                                    ->weight(FontWeight::Bold)
                                    ->color('danger'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Metode & Tanggal')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('paymentMethod.name')
                                    ->label('Metode Pembayaran'),

                                Infolists\Components\TextEntry::make('tanggal_pembayaran')
                                    ->label('Tanggal Pembayaran')
                                    ->date('d M Y'),

                                Infolists\Components\TextEntry::make('tanggal_dicatat')
                                    ->label('Tanggal Dicatat')
                                    ->date('d M Y'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Referensi & Konfirmasi')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('nomor_referensi')
                                    ->label('Nomor Referensi')
                                    ->visible(fn ($record) => $record->nomor_referensi),

                                Infolists\Components\TextEntry::make('dikonfirmasiOleh.name')
                                    ->label('Dikonfirmasi Oleh')
                                    ->visible(fn ($record) => $record->dikonfirmasi_oleh),
                            ]),
                    ]),

                Infolists\Components\Section::make('Lampiran')
                    ->schema([
                        Infolists\Components\TextEntry::make('bukti_pembayaran')
                            ->label('Bukti Pembayaran')
                            ->visible(fn ($record) => $record->bukti_pembayaran),
                    ])
                    ->visible(fn ($record) => $record->bukti_pembayaran),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPembayaranPiutangs::route('/'),
            'create' => Pages\CreatePembayaranPiutang::route('/create'),
            'view' => Pages\ViewPembayaranPiutang::route('/{record}'),
            'edit' => Pages\EditPembayaranPiutang::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }
}
