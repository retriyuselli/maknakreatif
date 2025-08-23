<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BankStatementResource\Pages;
use App\Filament\Resources\BankStatementResource\Widgets\BankStatementOverview;
use App\Models\BankStatement; 
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Filament\Support\RawJs; 
use Illuminate\Support\Facades\Auth; 


class BankStatementResource extends Resource
{
    protected static ?string $model = BankStatement::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes'; 
    protected static ?string $navigationGroup = 'Finance'; 
    protected static ?string $navigationLabel = 'Rekening Koran';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Rekening')
                    ->description('Pilih rekening bank yang akan digunakan untuk rekening koran')
                    ->schema([
                        Forms\Components\Select::make('payment_method_id')
                            ->relationship(
                                'paymentMethod', 
                                'no_rekening',
                                fn ($query) => $query->whereNotNull('no_rekening')
                                    ->where('no_rekening', '!=', '')
                                    ->whereNotNull('bank_name')
                                    ->where('bank_name', '!=', '')
                                    ->orderBy('bank_name')
                                    ->orderBy('no_rekening')
                            )
                            ->searchable(['bank_name', 'no_rekening'])
                            ->preload()
                            ->required()
                            ->label('Rekening Bank')
                            ->placeholder('Pilih rekening bank...')
                            ->helperText('Pilih rekening bank yang memiliki nomor rekening valid')
                            ->getOptionLabelFromRecordUsing(fn ($record) => 
                                $record->no_rekening && $record->bank_name 
                                    ? "{$record->bank_name} - {$record->no_rekening}" . 
                                      ($record->cabang ? " - {$record->name}" : '')
                                    : 'Data rekening tidak lengkap'
                            )
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Metode Pembayaran')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('bank_name')
                                    ->label('Nama Bank')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Contoh: BCA, Mandiri, BNI'),
                                Forms\Components\TextInput::make('no_rekening')
                                    ->label('Nomor Rekening')
                                    ->required()
                                    ->maxLength(50)
                                    ->placeholder('Masukkan nomor rekening'),
                                Forms\Components\TextInput::make('cabang')
                                    ->label('Cabang')
                                    ->maxLength(255)
                                    ->placeholder('Nama cabang (opsional)'),
                            ])
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $paymentMethod = \App\Models\PaymentMethod::find($state);
                                    if ($paymentMethod) {
                                        $set('branch', $paymentMethod->cabang);
                                        
                                        // Auto-fill some fields if available
                                        if ($paymentMethod->bank_name) {
                                            // You can add more auto-fill logic here
                                        }
                                    }
                                } else {
                                    $set('branch', null);
                                }
                            })
                            ->live(),
                        
                        Forms\Components\TextInput::make('branch')
                            ->label('Cabang')
                            ->maxLength(255)
                            ->placeholder('Cabang akan terisi otomatis dari rekening yang dipilih')
                            ->helperText('Informasi cabang dari rekening yang dipilih'),
                    ])->columns(1),

                Forms\Components\Section::make('Periode Rekening Koran')
                    ->description('Tentukan periode waktu untuk rekening koran')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('period_start')
                                    ->label('Periode Awal')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d M Y')
                                    ->placeholder('Pilih tanggal mulai')
                                    ->helperText('Tanggal awal periode rekening koran')
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        // Auto-set period_end to 30 days after period_start if not set
                                        if ($state && !$get('period_end')) {
                                            $endDate = \Carbon\Carbon::parse($state)->addDays(29);
                                            $set('period_end', $endDate->format('Y-m-d'));
                                        }
                                    }),
                                Forms\Components\DatePicker::make('period_end')
                                    ->label('Periode Akhir')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d M Y')
                                    ->placeholder('Pilih tanggal akhir')
                                    ->helperText('Tanggal akhir periode rekening koran')
                                    ->afterOrEqual('period_start'),
                            ]),
                        
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('set_current_month')
                                ->label('Bulan Ini')
                                ->icon('heroicon-o-calendar')
                                ->color('primary')
                                ->action(function (callable $set) {
                                    $now = \Carbon\Carbon::now();
                                    $set('period_start', $now->startOfMonth()->format('Y-m-d'));
                                    $set('period_end', $now->endOfMonth()->format('Y-m-d'));
                                }),
                            Forms\Components\Actions\Action::make('set_last_month')
                                ->label('Bulan Lalu')
                                ->icon('heroicon-o-calendar-days')
                                ->color('gray')
                                ->action(function (callable $set) {
                                    $lastMonth = \Carbon\Carbon::now()->subMonth();
                                    $set('period_start', $lastMonth->startOfMonth()->format('Y-m-d'));
                                    $set('period_end', $lastMonth->endOfMonth()->format('Y-m-d'));
                                }),
                            Forms\Components\Actions\Action::make('set_last_30_days')
                                ->label('30 Hari Terakhir')
                                ->icon('heroicon-o-clock')
                                ->color('success')
                                ->action(function (callable $set) {
                                    $now = \Carbon\Carbon::now();
                                    $set('period_start', $now->subDays(30)->format('Y-m-d'));
                                    $set('period_end', $now->format('Y-m-d'));
                                }),
                        ])->extraAttributes(['class' => 'mt-4']),
                    ]),

                Forms\Components\Section::make('File Rekening Koran')
                    ->description('Upload file rekening koran dari bank')
                    ->schema([
                        Forms\Components\Select::make('source_type')
                            ->label('Tipe Sumber File')
                            ->options([
                                'pdf' => 'PDF - File rekening koran PDF dari bank',
                                'excel' => 'Excel - File spreadsheet (.xlsx, .xls)',
                                'manual_input' => 'Input Manual - Entry data secara manual',
                            ])
                            ->required()
                            ->default('pdf')
                            ->helperText('Pilih jenis file yang akan diupload')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Clear file when changing source type
                                if ($state === 'manual_input') {
                                    $set('file_path', null);
                                }
                            }),
                        
                        Forms\Components\FileUpload::make('file_path')
                            ->label('Upload File Rekening Koran')
                            ->disk('public')
                            ->directory('bank-statements')
                            ->acceptedFileTypes(function (callable $get) {
                                $sourceType = $get('source_type');
                                return match($sourceType) {
                                    'pdf' => ['application/pdf'],
                                    'excel' => [
                                        'application/vnd.ms-excel',
                                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                        'text/csv'
                                    ],
                                    default => ['application/pdf', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
                                };
                            })
                            ->maxSize(10240) // 10MB
                            ->helperText(function (callable $get) {
                                $sourceType = $get('source_type');
                                return match($sourceType) {
                                    'pdf' => 'Upload file PDF rekening koran (max 10MB)',
                                    'excel' => 'Upload file Excel/CSV (max 10MB)',
                                    'manual_input' => 'File tidak diperlukan untuk input manual',
                                    default => 'Upload file rekening koran (max 10MB)'
                                };
                            })
                            ->required(fn (callable $get) => $get('source_type') !== 'manual_input')
                            ->visible(fn (callable $get) => $get('source_type') !== 'manual_input')
                            ->deletable(true)
                            ->downloadable()
                            ->previewable(false)
                            ->loadingIndicatorPosition('left')
                            ->removeUploadedFileButtonPosition('right')
                            ->uploadButtonPosition('left')
                            ->uploadProgressIndicatorPosition('left'),
                        
                        Forms\Components\Placeholder::make('file_info')
                            ->label('Informasi File')
                            ->content(function ($record, callable $get) {
                                if ($record && $record->file_path) {
                                    $filePath = storage_path('app/public/' . $record->file_path);
                                    if (file_exists($filePath)) {
                                        $fileSize = filesize($filePath);
                                        $formattedSize = $fileSize > 1024 * 1024 
                                            ? round($fileSize / (1024 * 1024), 2) . ' MB'
                                            : round($fileSize / 1024, 2) . ' KB';
                                        
                                        return new \Illuminate\Support\HtmlString(
                                            '<div class="space-y-2">' .
                                            '<div><strong>Ukuran:</strong> ' . $formattedSize . '</div>' .
                                            '<div><strong>Diupload:</strong> ' . $record->created_at->format('d M Y H:i') . '</div>' .
                                            '<div><a href="' . Storage::url($record->file_path) . '" target="_blank" class="text-primary-600 hover:text-primary-700 underline font-medium">📄 Buka File</a></div>' .
                                            '</div>'
                                        );
                                    }
                                }
                                return 'Belum ada file yang diupload';
                            })
                            ->visible(fn ($record) => $record && filled($record->file_path))
                            ->extraAttributes(['class' => 'text-sm bg-gray-50 p-3 rounded-lg']),
                    ]),

                Forms\Components\Section::make('Detail Finansial')
                    ->description('Informasi saldo dan transaksi dari rekening koran')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('opening_balance')
                                    ->label('Saldo Awal')
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->placeholder('0')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->inputMode('numeric')
                                    ->helperText('Saldo awal periode'),
                                
                                Forms\Components\TextInput::make('closing_balance')
                                    ->label('Saldo Akhir')
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->placeholder('0')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->inputMode('numeric')
                                    ->helperText('Saldo akhir periode'),
                                
                                Forms\Components\Placeholder::make('balance_difference')
                                    ->label('Selisih Saldo')
                                    ->content(function (callable $get) {
                                        $openingRaw = $get('opening_balance') ?? '';
                                        $closingRaw = $get('closing_balance') ?? '';
                                        
                                        // Handle both formatted (with dots) and raw numbers
                                        $opening = $openingRaw ? (float) str_replace(['.', ',', ' '], '', $openingRaw) : 0;
                                        $closing = $closingRaw ? (float) str_replace(['.', ',', ' '], '', $closingRaw) : 0;
                                        
                                        $difference = $closing - $opening;
                                        
                                        if ($difference == 0) {
                                            return new \Illuminate\Support\HtmlString(
                                                '<div class="text-gray-600 font-medium text-lg">IDR 0</div>'
                                            );
                                        }
                                        
                                        $color = $difference > 0 ? 'text-green-600' : 'text-red-600';
                                        $sign = $difference > 0 ? '+' : '';
                                        
                                        return new \Illuminate\Support\HtmlString(
                                            '<div class="' . $color . ' font-semibold text-lg">' .
                                            $sign . 'IDR ' . number_format($difference, 0, ',', '.') .
                                            '</div>'
                                        );
                                    }),
                            ]),
                        
                        Forms\Components\Fieldset::make('Transaksi Debit')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('no_of_debit')
                                            ->label('Jumlah Transaksi Debit')
                                            ->numeric()
                                            ->placeholder('0')
                                            ->suffix('transaksi')
                                            ->helperText('Total jumlah transaksi debit'),
                                        
                                        Forms\Components\TextInput::make('tot_debit')
                                            ->label('Total Nominal Debit')
                                            ->numeric()
                                            ->prefix('IDR')
                                            ->placeholder('0')
                                            ->mask(RawJs::make('$money($input)'))
                                            ->stripCharacters(',')
                                            ->inputMode('numeric')
                                            ->helperText('Total nilai transaksi debit'),
                                    ]),
                            ]),
                        
                        Forms\Components\Fieldset::make('Transaksi Kredit')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('no_of_credit')
                                            ->label('Jumlah Transaksi Kredit')
                                            ->numeric()
                                            ->placeholder('0')
                                            ->suffix('transaksi')
                                            ->helperText('Total jumlah transaksi kredit'),
                                        
                                        Forms\Components\TextInput::make('tot_credit')
                                            ->label('Total Nominal Kredit')
                                            ->numeric()
                                            ->prefix('IDR')
                                            ->mask(RawJs::make('$money($input)'))
                                            ->placeholder('0')
                                            ->stripCharacters(',')
                                            ->inputMode('numeric')
                                            ->helperText('Total nilai transaksi kredit'),
                                    ]),
                            ]),
                    ]),
                Forms\Components\Hidden::make('status')->default('pending'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('period_start', 'desc') // Menambahkan default sorting
            ->columns([
                Tables\Columns\TextColumn::make('paymentMethod.no_rekening')
                    ->label('No. Rekening')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($record) {
                        if ($record->paymentMethod) {
                            return $record->paymentMethod->bank_name . ' - ' . $record->paymentMethod->no_rekening;
                        }
                        return '-';
                    }),
                Tables\Columns\TextColumn::make('period_start')
                    ->label('Tanggal Mulai')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('period_end')
                    ->label('Tanggal Akhir')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('branch')
                    ->label('Cabang')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('opening_balance')
                    ->label('Saldo Awal')
                    ->money('IDR')
                    ->sortable()
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('closing_balance')
                    ->label('Saldo Akhir')
                    ->money('IDR')
                    ->sortable()
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('no_of_debit')
                    ->label('Jumlah Debit')
                    ->numeric()
                    ->sortable()
                    ->alignEnd()
                    ->suffix(' transaksi')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('tot_debit')
                    ->label('Total Debit')
                    ->money('IDR')
                    ->sortable()
                    ->alignEnd()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('no_of_credit')
                    ->label('Jumlah Kredit')
                    ->numeric()
                    ->sortable()
                    ->alignEnd()
                    ->suffix(' transaksi')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('tot_credit')
                    ->label('Total Kredit')
                    ->money('IDR')
                    ->sortable()
                    ->alignEnd()
                    ->color('success'),
                Tables\Columns\TextColumn::make('source_type')
                    ->label('Tipe Sumber')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pdf' => 'danger',
                        'excel' => 'success',
                        'manual_input' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => \App\Models\BankStatement::getSourceTypeOptions()[$state] ?? $state),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'processing' => 'warning',
                        'parsed' => 'success',
                        'failed' => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn (string $state) => \App\Models\BankStatement::getStatusOptions()[$state] ?? $state),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_method_id')
                    ->relationship(
                        'paymentMethod', 
                        'no_rekening',
                        fn ($query) => $query->whereNotNull('no_rekening')->where('no_rekening', '!=', '')
                    )
                    ->label('Rekening Bank')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->placeholder('Pilih Rekening Bank')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->no_rekening ? ($record->bank_name . ' - ' . $record->no_rekening) : 'Nomor rekening tidak tersedia'),

                Tables\Filters\Filter::make('period_date')
                    ->form([
                        Forms\Components\DatePicker::make('period_start_from')
                            ->label('Periode Mulai Dari')
                            ->native(false),
                        Forms\Components\DatePicker::make('period_end_until')
                            ->label('Periode Selesai Hingga')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['period_start_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('period_start', '>=', $date),
                            )
                            ->when(
                                $data['period_end_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('period_end', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['period_start_from'] ?? null) {
                            $indicators['period_start_from'] = 'Periode mulai dari ' . Carbon::parse($data['period_start_from'])->format('d M Y');
                        }
                        if ($data['period_end_until'] ?? null) {
                            $indicators['period_end_until'] = 'Periode selesai hingga ' . Carbon::parse($data['period_end_until'])->format('d M Y');
                        }
                        return $indicators;
                    }),

                Tables\Filters\SelectFilter::make('source_type')
                    ->label('Sumber File')
                    ->options(BankStatement::getSourceTypeOptions()),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(BankStatement::getStatusOptions())
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat Detail')
                        ->color('info')
                        ->tooltip('Lihat detail rekening koran'),
                    Tables\Actions\EditAction::make()
                        ->label('Edit')
                        ->color('warning')
                        ->tooltip('Edit rekening koran'),
                    Tables\Actions\Action::make('download')
                        ->label('Unduh File')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->url(fn (BankStatement $record): string => $record->file_path ? Storage::url($record->file_path) : '#')
                        ->openUrlInNewTab()
                        ->visible(fn (BankStatement $record): bool => !empty($record->file_path))
                        ->tooltip('Unduh file rekening koran'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->color('danger')
                        ->tooltip('Hapus rekening koran')
                        ->modalHeading('Hapus Rekening Koran')
                        ->modalDescription('Apakah Anda yakin ingin menghapus rekening koran ini? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus'),
                ])
                    ->tooltip('Aksi Rekening Koran')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Rekening Koran Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus rekening koran yang dipilih?')
                        ->modalSubmitActionLabel('Ya, hapus'),
                ])->label('Aksi Massal'),
            ])
            ->striped()
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10, 25, 50])
            ->emptyStateIcon('heroicon-o-banknotes')
            ->emptyStateHeading('Belum ada rekening koran')
            ->emptyStateDescription('Mulai dengan membuat rekening koran pertama Anda untuk melacak transaksi keuangan.')
            ->emptyStateActions([
                Tables\Actions\Action::make('create')
                    ->label('Buat Rekening Koran Baru')
                    ->url(static::getUrl('create'))
                    ->icon('heroicon-o-plus')
                    ->button(),
            ]);
    }

    // public static function getEloquentQuery(): Builder
    // {
    //     return parent::getEloquentQuery()->withCount('transactions');
    // }

    public static function getRelations(): array
    {
        return [
            // TransactionRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBankStatements::route('/'),
            'create' => Pages\CreateBankStatement::route('/create'),
            'edit' => Pages\EditBankStatement::route('/{record}/edit'),
        ];
    }

    protected static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['uploaded_at'] = Carbon::now();
        return $data;
    }

    protected static function mutateFormDataBeforeFill(array $data): array
    {
        // Pastikan field numerik diformat dengan benar saat dimuat untuk edit
        return $data;
    }

    protected static function mutateFormDataBeforeSave(array $data): array
    {
        // Bersihkan masalah formatting sebelum menyimpan
        $numericFields = ['opening_balance', 'closing_balance', 'tot_debit', 'tot_credit'];
        
        foreach ($numericFields as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                // Hapus format dan konversi ke angka
                $data[$field] = (float) str_replace(['.', ',', ' ', 'IDR'], '', $data[$field]) ?: null;
            }
        }
        
        return $data;
    }

    public static function getNavigationBadge(): ?string
    {
        // Menampilkan jumlah total rekening koran sebagai badge
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        // Memberikan warna pada badge untuk visibilitas yang lebih baik
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Total rekening koran yang terdaftar';
    }

    public static function getWidgets(): array
    {
        return [
            BankStatementOverview::class,
        ];
    }
}
