<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\Pendapatan;
use App\Filament\Resources\PendapatanLainResource\RelationManagers;
use App\Filament\Resources\PendapatanLainResource\Pages;
use App\Models\PendapatanLain;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Support\RawJs;
use Illuminate\Support\Str;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PendapatanLainResource extends Resource
{
    protected static ?string $model = PendapatanLain::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-up';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationLabel = 'Pendapatan Lain';
    protected static ?string $cluster = Pendapatan::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Pendapatan')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Pendapatan')
                                    ->required()
                                    ->maxLength(255)    
                                    ->columnSpanFull(),

                                Forms\Components\Select::make('payment_method_id')
                                    ->relationship('paymentMethod', 'name')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->is_cash ? 'Kas/Tunai' : ($record->bank_name ? "{$record->bank_name} - {$record->no_rekening}" : $record->name))
                                    ->label('Metode Pembayaran')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('nominal')
                                    ->required()
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->placeholder('0')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->inputMode('numeric')
                                    ->label('Nominal')
                                    ->helperText('Masukkan nominal pendapatan'),

                                Forms\Components\DatePicker::make('tgl_bayar')
                                    ->label('Tanggal Pendapatan')
                                    ->default(now())
                                    ->native(false)
                                    ->displayFormat('d M Y')
                                    ->required()
                                    ->helperText('Pilih tanggal ketika pendapatan diterima'),

                                Forms\Components\Select::make('kategori_transaksi')
                                    ->options([
                                        'uang_masuk' => 'Uang Masuk',
                                    ])
                                    ->default('uang_masuk')
                                    ->required()
                                    ->disabled()
                                    ->helperText('Otomatis diatur sebagai Uang Masuk.'),

                                Forms\Components\FileUpload::make('image')
                                    ->label('Bukti Pendapatan')
                                    ->image()
                                    ->imageEditor()
                                    ->directory('pendapatan-lain')
                                    ->downloadable()
                                    ->openable()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(5120) // 5MB
                                    ->imageResizeMode('cover')
                                    ->imageCropAspectRatio('16:9')
                                    ->columnSpanFull()
                                    ->helperText('Upload bukti pendapatan (JPEG, PNG, WEBP, max 5MB)'),

                                Forms\Components\Textarea::make('keterangan')
                                    ->label('Keterangan')
                                    ->required()
                                    ->rows(3)
                                    ->maxLength(1000)
                                    ->columnSpanFull()
                                    ->helperText('Jelaskan detail pendapatan ini (max 1000 karakter)'),
                            ]),
                    ]),
                ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('tgl_bayar', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Pendapatan')
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->description(fn (PendapatanLain $record): ?string => Str::limit($record->keterangan, 50)),

                Tables\Columns\TextColumn::make('nominal')
                    ->label('Jumlah')
                    ->numeric()
                    ->money('IDR')
                    ->sortable()
                    ->color('success')
                    ->alignment(Alignment::End)
                    ->summarize(
                        Tables\Columns\Summarizers\Sum::make()->money('IDR')
                    ),

                Tables\Columns\TextColumn::make('tgl_bayar')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('paymentMethod.name')
                    ->label('Metode Pembayaran')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('warning'),

                Tables\Columns\ImageColumn::make('image')
                    ->label('Bukti')
                    ->square()
                    ->size(60)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('kategori_transaksi')
                    ->label('Tipe')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn (string $state): string => Str::title(str_replace('_', ' ', $state)))
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_method_id')
                    ->label('Metode Pembayaran')
                    ->relationship('paymentMethod', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                
                Tables\Filters\Filter::make('tgl_bayar')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('Dari Tanggal')
                            ->placeholder('Pilih tanggal mulai'),
                        Forms\Components\DatePicker::make('date_until')
                            ->label('Sampai Tanggal')
                            ->placeholder('Pilih tanggal akhir'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['date_from'], fn(Builder $query, $date): Builder => $query->whereDate('tgl_bayar', '>=', $date))
                            ->when($data['date_until'], fn(Builder $query, $date): Builder => $query->whereDate('tgl_bayar', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['date_from'] ?? null) {
                            $indicators['from'] = 'Dari: ' . \Carbon\Carbon::parse($data['date_from'])->format('d M Y');
                        }
                        if ($data['date_until'] ?? null) {
                            $indicators['until'] = 'Sampai: ' . \Carbon\Carbon::parse($data['date_until'])->format('d M Y');
                        }
                        return $indicators;
                    }),
                
                Tables\Filters\Filter::make('nominal_range')
                    ->label('Rentang Nominal')
                    ->form([
                        Forms\Components\TextInput::make('nominal_from')
                            ->label('Nominal Minimum')
                            ->numeric()
                            ->prefix('IDR'),
                        Forms\Components\TextInput::make('nominal_until')
                            ->label('Nominal Maximum')
                            ->numeric()
                            ->prefix('IDR'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['nominal_from'], fn(Builder $query, $amount): Builder => $query->where('nominal', '>=', $amount))
                            ->when($data['nominal_until'], fn(Builder $query, $amount): Builder => $query->where('nominal', '<=', $amount));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['nominal_from'] ?? null) {
                            $indicators['from'] = 'Min: IDR ' . number_format($data['nominal_from'], 0, ',', '.');
                        }
                        if ($data['nominal_until'] ?? null) {
                            $indicators['until'] = 'Max: IDR ' . number_format($data['nominal_until'], 0, ',', '.');
                        }
                        return $indicators;
                    }),
                
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat')
                        ->color('info')
                        ->tooltip('Lihat detail pendapatan'),
                    Tables\Actions\EditAction::make()
                        ->label('Edit')
                        ->color('warning')
                        ->tooltip('Edit pendapatan'),
                    Tables\Actions\ReplicateAction::make()
                        ->label('Duplikasi')
                        ->color('gray')
                        ->tooltip('Duplikasi pendapatan')
                        ->form([
                            Forms\Components\DatePicker::make('tgl_bayar')
                                ->label('Tanggal Pendapatan Baru')
                                ->default(now())
                                ->required(),
                            Forms\Components\TextInput::make('name')
                                ->label('Nama Pendapatan')
                                ->required(),
                        ])
                        ->beforeReplicaSaved(function (array $data): array {
                            $data['tgl_bayar'] = $data['tgl_bayar'] ?? now();
                            return $data;
                        }),
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->color('danger')
                        ->tooltip('Hapus pendapatan'),
                    Tables\Actions\RestoreAction::make()
                        ->label('Pulihkan')
                        ->color('success')
                        ->tooltip('Pulihkan pendapatan'),
                    Tables\Actions\ForceDeleteAction::make()
                        ->label('Hapus Permanen')
                        ->color('danger')
                        ->tooltip('Hapus permanen'),
                ])
                    ->tooltip('Aksi Pendapatan')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Pendapatan Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus pendapatan yang dipilih?')
                        ->modalSubmitActionLabel('Ya, hapus'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Hapus Permanen')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Permanen Pendapatan')
                        ->modalDescription('Tindakan ini tidak dapat dibatalkan!')
                        ->modalSubmitActionLabel('Ya, hapus permanen'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Pulihkan Terpilih')
                        ->requiresConfirmation()
                        ->modalHeading('Pulihkan Pendapatan')
                        ->modalDescription('Pendapatan yang dipilih akan dipulihkan.')
                        ->modalSubmitActionLabel('Ya, pulihkan'),
                ])->label('Aksi Massal'),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Pendapatan Pertama')
                    ->icon('heroicon-o-plus')
            ])
            ->poll('60s')
            ->striped()
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10, 25, 50]);
    }


    public static function getNavigationGroup(): ?string
    {
        return 'Finance';
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPendapatanLains::route('/'),
            'create' => Pages\CreatePendapatanLain::route('/create'),
            'edit' => Pages\EditPendapatanLain::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getNavigationBadge(): ?string
    {
        // Menampilkan jumlah pendapatan aktif (tidak termasuk yang di-trash)
        return static::getModel()::whereNull('deleted_at')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $count = static::getModel()::whereNull('deleted_at')->count();
        
        if ($count > 50) {
            return 'success';
        } elseif ($count > 20) {
            return 'warning';
        } else {
            return 'primary';
        }
    }
    
    public static function getNavigationBadgeTooltip(): ?string
    {
        $totalRevenue = static::getModel()::whereNull('deleted_at')->sum('nominal');
        $formattedRevenue = 'IDR ' . number_format($totalRevenue, 0, ',', '.');
        
        return "Pendapatan lain perusahaan.\nTotal: {$formattedRevenue}";
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }
}
