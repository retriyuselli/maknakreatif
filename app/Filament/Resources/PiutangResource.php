<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PiutangResource\Pages;
use App\Models\Piutang;
use App\Enums\JenisPiutang;
use App\Enums\StatusPiutang;
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

class PiutangResource extends Resource
{
    protected static ?string $model = Piutang::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Piutang';

    protected static ?string $modelLabel = 'Piutang';

    protected static ?string $pluralModelLabel = 'Piutang';

    protected static ?string $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Piutang')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('nomor_piutang')
                                    ->label('Nomor Piutang')
                                    ->default(fn () => Piutang::generateNomorPiutang())
                                    ->disabled()
                                    ->dehydrated()
                                    ->required(),

                                Forms\Components\Select::make('jenis_piutang')
                                    ->label('Jenis Piutang')
                                    ->options(JenisPiutang::getOptions())
                                    ->required(),

                                Forms\Components\Select::make('prioritas')
                                    ->label('Prioritas')
                                    ->options([
                                        'rendah' => 'Rendah',
                                        'sedang' => 'Sedang',
                                        'tinggi' => 'Tinggi',
                                        'mendesak' => 'Mendesak',
                                    ])
                                    ->default('sedang')
                                    ->required(),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('nama_debitur')
                                    ->label('Nama Debitur')
                                    ->required()
                                    ->placeholder('Nama yang berhutang kepada kita'),

                                Forms\Components\TextInput::make('kontak_debitur')
                                    ->label('Kontak Debitur')
                                    ->placeholder('No. HP/Telepon untuk follow up')
                                    ->tel(),
                            ]),

                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan Piutang')
                            ->required()
                            ->placeholder('Jelaskan detail piutang, invoice, dll')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Detail Keuangan')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('jumlah_pokok')
                                    ->label('Jumlah Pokok')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        $pokok = (float) $state;
                                        $bunga = (float) $get('persentase_bunga') ?? 0;
                                        $totalBunga = ($pokok * $bunga) / 100;
                                        $total = $pokok + $totalBunga;
                                        $set('total_piutang', $total);
                                        $set('sisa_piutang', $total);
                                    }),

                                Forms\Components\TextInput::make('persentase_bunga')
                                    ->label('Bunga (%)')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(0)
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        $pokok = (float) $get('jumlah_pokok') ?? 0;
                                        $bunga = (float) $state ?? 0;
                                        $totalBunga = ($pokok * $bunga) / 100;
                                        $total = $pokok + $totalBunga;
                                        $set('total_piutang', $total);
                                        $set('sisa_piutang', $total);
                                    }),

                                Forms\Components\TextInput::make('total_piutang')
                                    ->label('Total Piutang')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->dehydrated(),
                            ]),
                    ]),

                Forms\Components\Section::make('Tanggal & Status')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\DatePicker::make('tanggal_piutang')
                                    ->label('Tanggal Piutang')
                                    ->required()
                                    ->default(now()),

                                Forms\Components\DatePicker::make('tanggal_jatuh_tempo')
                                    ->label('Tanggal Jatuh Tempo')
                                    ->required()
                                    ->minDate(now()),

                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options(StatusPiutang::getOptions())
                                    ->default('aktif')
                                    ->required(),
                            ]),
                    ]),

                Forms\Components\Section::make('Lampiran & Catatan')
                    ->schema([
                        Forms\Components\FileUpload::make('lampiran')
                            ->label('Lampiran')
                            ->multiple()
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(2048)
                            ->helperText('Upload dokumen pendukung (PDF, gambar). Maksimal 2MB per file.'),

                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan Tambahan')
                            ->placeholder('Catatan atau informasi tambahan tentang piutang ini'),
                    ]),

                Forms\Components\Hidden::make('dibuat_oleh')
                    ->default(Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_piutang')
                    ->label('Nomor Piutang')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                Tables\Columns\TextColumn::make('jenis_piutang')
                    ->label('Jenis')
                    ->formatStateUsing(fn ($state) => $state instanceof JenisPiutang ? $state->getLabel() : JenisPiutang::from($state)->getLabel())
                    ->badge()
                    ->color(fn ($state) => match($state instanceof JenisPiutang ? $state->value : $state) {
                        'operasional' => 'warning',
                        'pribadi' => 'danger', 
                        'bisnis' => 'primary',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('nama_debitur')
                    ->label('Debitur')
                    ->searchable()
                    ->limit(20),

                Tables\Columns\TextColumn::make('kontak_debitur')
                    ->label('Kontak')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total_piutang')
                    ->label('Total Piutang')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sudah_dibayar')
                    ->label('Sudah Dibayar')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sisa_piutang')
                    ->label('Sisa Piutang')
                    ->money('IDR')
                    ->sortable()
                    ->weight(FontWeight::Bold),

                Tables\Columns\TextColumn::make('tanggal_jatuh_tempo')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => $state instanceof StatusPiutang ? $state->getLabel() : StatusPiutang::from($state)->getLabel())
                    ->badge()
                    ->color(fn ($state) => $state instanceof StatusPiutang ? $state->getColor() : StatusPiutang::from($state)->getColor()),

                Tables\Columns\TextColumn::make('prioritas')
                    ->label('Prioritas')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'rendah' => 'gray',
                        'sedang' => 'info',
                        'tinggi' => 'warning',
                        'mendesak' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jenis_piutang')
                    ->label('Jenis Piutang')
                    ->options(JenisPiutang::getOptions()),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(StatusPiutang::getOptions()),

                Tables\Filters\Filter::make('jatuh_tempo')
                    ->label('Akan Jatuh Tempo')
                    ->query(fn (Builder $query): Builder => $query->akanJatuhTempo(7)),

                Tables\Filters\Filter::make('sudah_jatuh_tempo')
                    ->label('Sudah Jatuh Tempo')
                    ->query(fn (Builder $query): Builder => $query->jatuhTempo()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('terima_pembayaran')
                    ->label('Terima Pembayaran')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('success')
                    // ->url(fn (Piutang $record) => PembayaranPiutangResource::getUrl('create', ['piutang_id' => $record->id]))
                    ->visible(fn (Piutang $record) => in_array($record->status, [StatusPiutang::AKTIF, StatusPiutang::DIBAYAR_SEBAGIAN, StatusPiutang::JATUH_TEMPO])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('tanggal_jatuh_tempo', 'asc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Piutang')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('nomor_piutang')
                                    ->label('Nomor Piutang')
                                    ->weight(FontWeight::Bold),

                                Infolists\Components\TextEntry::make('jenis_piutang')
                                    ->label('Jenis Piutang')
                                    ->formatStateUsing(fn ($state) => $state instanceof JenisPiutang ? $state->getLabel() : JenisPiutang::from($state)->getLabel())
                                    ->badge(),

                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->formatStateUsing(fn ($state) => $state instanceof StatusPiutang ? $state->getLabel() : StatusPiutang::from($state)->getLabel())
                                    ->badge()
                                    ->color(fn ($state) => $state instanceof StatusPiutang ? $state->getColor() : StatusPiutang::from($state)->getColor()),
                            ]),

                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('nama_debitur')
                                    ->label('Debitur'),

                                Infolists\Components\TextEntry::make('kontak_debitur')
                                    ->label('Kontak Debitur')
                                    ->visible(fn ($record) => $record->kontak_debitur),
                            ]),

                        Infolists\Components\TextEntry::make('keterangan')
                            ->label('Keterangan')
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Detail Keuangan')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('jumlah_pokok')
                                    ->label('Jumlah Pokok')
                                    ->money('IDR'),

                                Infolists\Components\TextEntry::make('persentase_bunga')
                                    ->label('Bunga')
                                    ->suffix('%'),

                                Infolists\Components\TextEntry::make('total_piutang')
                                    ->label('Total Piutang')
                                    ->money('IDR')
                                    ->weight(FontWeight::Bold),
                            ]),

                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('sudah_dibayar')
                                    ->label('Sudah Dibayar')
                                    ->money('IDR')
                                    ->color('success'),

                                Infolists\Components\TextEntry::make('sisa_piutang')
                                    ->label('Sisa Piutang')
                                    ->money('IDR')
                                    ->weight(FontWeight::Bold)
                                    ->color('danger'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Tanggal')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('tanggal_piutang')
                                    ->label('Tanggal Piutang')
                                    ->date('d M Y'),

                                Infolists\Components\TextEntry::make('tanggal_jatuh_tempo')
                                    ->label('Jatuh Tempo')
                                    ->date('d M Y'),

                                Infolists\Components\TextEntry::make('tanggal_lunas')
                                    ->label('Tanggal Lunas')
                                    ->date('d M Y')
                                    ->visible(fn ($record) => $record->tanggal_lunas),
                            ]),
                    ]),

                Infolists\Components\Section::make('Catatan & Lampiran')
                    ->schema([
                        Infolists\Components\TextEntry::make('catatan')
                            ->label('Catatan')
                            ->visible(fn ($record) => $record->catatan),

                        Infolists\Components\TextEntry::make('lampiran')
                            ->label('Lampiran')
                            ->visible(fn ($record) => $record->lampiran),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPiutangs::route('/'),
            'create' => Pages\CreatePiutang::route('/create'),
            'view' => Pages\ViewPiutang::route('/{record}'),
            'edit' => Pages\EditPiutang::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'aktif')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function getWidgets(): array
    {
        return [
            PiutangResource\Widgets\PiutangOverviewWidget::class,
            PiutangResource\Widgets\PiutangJatuhTempoWidget::class,
            PiutangResource\Widgets\TopDebiturWidget::class,
        ];
    }
}
