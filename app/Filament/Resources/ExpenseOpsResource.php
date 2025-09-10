<?php

namespace App\Filament\Resources;
use App\Enums\TransactionCategoryUangKeluar;
use App\Enums\TransactionCategoryUangMasuk;
use App\Exports\ExpenseOpsExport;
use App\Filament\Clusters\Pengeluaran;
use App\Filament\Resources\ExpenseOpsResource\Pages;
use App\Filament\Resources\ExpenseOpsResource\Widgets\ExpenseOpsOverview;
use App\Models\ExpenseOps;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\RawJs;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class ExpenseOpsResource extends Resource
{
    protected static ?string $model = ExpenseOps::class;
    protected static ?string $navigationLabel = 'Pengeluaran Operasional';
    protected static ?string $navigationIcon = 'heroicon-o-cog-8-tooth';
    protected static ?string $cluster = Pengeluaran::class;

    /**
     * Safely convert any value to float for calculations
     */
    private static function safeFloatVal($value)
    {
        if (is_null($value)) {
            return 0.0;
        }
        
        if (is_numeric($value)) {
            return floatval($value);
        }
        
        if (is_string($value)) {
            // Remove any non-numeric characters except dots and commas
            $cleaned = preg_replace('/[^\d.,]/', '', $value);
            // Remove commas (thousand separators)
            $cleaned = str_replace(',', '', $cleaned);
            // Handle empty string after cleaning
            if ($cleaned === '' || $cleaned === '.') {
                return 0.0;
            }
            return floatval($cleaned);
        }
        
        if (is_array($value)) {
            // If somehow we get an array, return 0
            return 0.0;
        }
        
        // Fallback for any other data type
        return 0.0;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Pengeluaran')
                ->description('Detail pengeluaran operasional')
                ->icon('heroicon-o-banknotes')
                ->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Pengeluaran')
                            ->required()
                            ->maxLength(255)
                            ->live()
                            ->placeholder('Akan terisi otomatis dari detail nota dinas atau isi manual')
                            ->helperText('Terisi otomatis dari "Keperluan + Event" pada detail nota dinas yang dipilih')
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->label('Nominal')
                            ->prefix('Rp. ')
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->inputMode('numeric')
                            ->placeholder('0')
                            ->columnSpan(1),
                    ]),
                ]),
            
            Forms\Components\Section::make('Detail Transaksi & Nota Dinas')
                ->description('Informasi pembayaran melalui Nota Dinas dan dokumentasi')
                ->icon('heroicon-o-document-text')
                ->schema([
                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\Select::make('nota_dinas_id')
                                ->label('Nota Dinas')
                                ->options(function () {
                                    // Filter NotaDinas yang memiliki detail dengan jenis_pengeluaran 'operasional'
                                    return \App\Models\NotaDinas::whereIn('status', ['disetujui', 'diajukan'])
                                        ->whereHas('details', function ($query) {
                                            $query->where('jenis_pengeluaran', 'operasional');
                                        })
                                        ->orderBy('created_at', 'desc')
                                        ->pluck('no_nd', 'id')
                                        ->toArray();
                                })
                                ->searchable()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    // Reset related fields when nota dinas changes
                                    if (!$state) {
                                        $set('nota_dinas_detail_id', null);
                                        $set('vendor_id', null);
                                        $set('bank_name', null);
                                        $set('account_holder', null);
                                        $set('bank_account', null);
                                        $set('amount', null);
                                        $set('note', null);
                                        $set('name', null);
                                    }
                                })
                                ->columnSpan(1),

                            Forms\Components\Select::make('nota_dinas_detail_id')
                                ->label('Detail Nota Dinas')
                                ->options(function (callable $get) {
                                    $notaDinasId = $get('nota_dinas_id');
                                    if (!$notaDinasId) return [];

                                    try {
                                        $currentDetailId = $get('nota_dinas_detail_id');
                                        
                                        // Get used detail IDs from other ExpenseOps records
                                        $usedDetailIds = \App\Models\ExpenseOps::whereNotNull('nota_dinas_detail_id')
                                            ->when($get('id'), function($query) use ($get) {
                                                return $query->where('id', '!=', $get('id'));
                                            })
                                            ->pluck('nota_dinas_detail_id')
                                            ->toArray();

                                        // Single optimized query - Filter only 'operasional' jenis_pengeluaran
                                        $availableDetails = \App\Models\NotaDinasDetail::with('vendor')
                                            ->where('nota_dinas_id', $notaDinasId)
                                            ->where('jenis_pengeluaran', 'operasional') // Filter hanya untuk jenis pengeluaran 'operasional'
                                            ->whereNotIn('id', $usedDetailIds)
                                            ->whereHas('vendor')
                                            ->get();

                                        // Preserve current selection
                                        if ($currentDetailId && !$availableDetails->contains('id', $currentDetailId)) {
                                            $currentDetail = \App\Models\NotaDinasDetail::with('vendor')->find($currentDetailId);
                                            if ($currentDetail && $currentDetail->vendor) {
                                                $availableDetails->prepend($currentDetail);
                                            }
                                        }

                                        return $availableDetails->mapWithKeys(function ($detail) use ($usedDetailIds) {
                                            $vendorName = $detail->vendor->name ?? 'N/A';
                                            $keperluan = $detail->keperluan ?? 'N/A';
                                            $jumlah = number_format($detail->jumlah_transfer, 0, ',', '.');
                                            
                                            $usedIndicator = in_array($detail->id, $usedDetailIds) ? ' (Tersedia kembali)' : '';
                                            
                                            $label = "{$vendorName} | {$keperluan} | Rp {$jumlah}{$usedIndicator}";
                                            return [$detail->id => $label];
                                        })->toArray();
                                        
                                    } catch (\Exception $e) {
                                        \Illuminate\Support\Facades\Log::error('Error in nota_dinas_detail_id options: ' . $e->getMessage());
                                        return [];
                                    }
                                })
                                ->searchable()
                                ->reactive()
                                ->live()
                                ->helperText(function (callable $get) {
                                    try {
                                        $notaDinasId = $get('nota_dinas_id');
                                        if (!$notaDinasId) return 'Pilih Nota Dinas terlebih dahulu';
                                        
                                        $usedDetailIds = \App\Models\ExpenseOps::whereNotNull('nota_dinas_detail_id')
                                            ->when($get('id'), function($query) use ($get) {
                                                return $query->where('id', '!=', $get('id'));
                                            })
                                            ->pluck('nota_dinas_detail_id')
                                            ->toArray();
                                        
                                        $actualUsedCount = count($usedDetailIds);
                                        $totalCount = \App\Models\NotaDinasDetail::where('nota_dinas_id', $notaDinasId)
                                            ->where('jenis_pengeluaran', 'operasional') // Filter hanya untuk jenis pengeluaran 'operasional'
                                            ->count();
                                        
                                        return "Pilih detail nota dinas 'Operasional' yang akan dibayar (Sudah dipilih: {$actualUsedCount}/{$totalCount})";
                                        
                                    } catch (\Exception $e) {
                                        return 'Pilih detail nota dinas yang akan dibayar';
                                    }
                                })
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    try {
                                        if (!$state) {
                                            $set('vendor_id', null);
                                            $set('bank_name', null);
                                            $set('account_holder', null);
                                            $set('bank_account', null);
                                            $set('amount', null);
                                            $set('note', null);
                                            $set('name', null);
                                            return;
                                        }

                                        // Fetch NotaDinasDetail and populate related fields
                                        $notaDinasDetail = \App\Models\NotaDinasDetail::with('vendor')->find($state);
                                        if ($notaDinasDetail) {
                                            $set('vendor_id', $notaDinasDetail->vendor_id);
                                            $set('bank_name', $notaDinasDetail->bank_name ?? $notaDinasDetail->vendor->bank_name);
                                            $set('account_holder', $notaDinasDetail->account_holder ?? $notaDinasDetail->vendor->account_holder);
                                            $set('bank_account', $notaDinasDetail->bank_account ?? $notaDinasDetail->vendor->bank_account);
                                            $set('amount', self::safeFloatVal($notaDinasDetail->jumlah_transfer ?? 0));
                                            $set('note', $notaDinasDetail->keperluan ?? null);
                                            
                                            // Auto-populate name from keperluan + event
                                            $nameComponents = [];
                                            if ($notaDinasDetail->keperluan) {
                                                $nameComponents[] = $notaDinasDetail->keperluan;
                                            }
                                            if ($notaDinasDetail->event && $notaDinasDetail->event !== $notaDinasDetail->keperluan) {
                                                $nameComponents[] = $notaDinasDetail->event;
                                            }
                                            
                                            $autoName = !empty($nameComponents) ? implode(' - ', $nameComponents) : 'Pengeluaran Operasional';
                                            $set('name', $autoName);
                                            
                                            // Auto-populate no_nd from NotaDinas
                                            $notaDinas = $notaDinasDetail->notaDinas;
                                            if ($notaDinas) {
                                                $set('no_nd', $notaDinas->no_nd);
                                            }
                                        }
                                    } catch (\Exception $e) {
                                        \Illuminate\Support\Facades\Log::error('Error in afterStateUpdated: ' . $e->getMessage());
                                    }
                                })
                                ->required()
                                ->columnSpan(2),

                            Forms\Components\Hidden::make('vendor_id'),
                        ]),

                    Forms\Components\Grid::make(4)
                        ->schema([
                            Forms\Components\TextInput::make('bank_name')
                                ->label('Bank')
                                ->required()
                                ->live()
                                ->columnSpan(1),

                            Forms\Components\TextInput::make('account_holder')
                                ->label('Nama Rekening')
                                ->required()
                                ->live()
                                ->columnSpan(1),
                            
                            Forms\Components\TextInput::make('bank_account')
                                ->label('Nomor Rekening')
                                ->required()
                                ->live()
                                ->columnSpan(1),

                            Forms\Components\DatePicker::make('tanggal_transfer')
                                ->label('Tanggal Transfer')
                                ->default(now())
                                ->required()
                                ->helperText(new \Illuminate\Support\HtmlString('<span style="color: #ef4444;">Sesuaikan tanggal transfer</span>'))
                                ->columnSpan(1),
                        ]),

                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\Select::make('payment_method_id')
                                ->relationship('paymentMethod', 'name')
                                ->getOptionLabelFromRecordUsing(fn ($record) => $record->is_cash ? 'Kas/Tunai' : ($record->bank_name ? "{$record->bank_name} - {$record->no_rekening}" : $record->name))
                                ->label('Sumber pembayaran')
                                ->searchable()
                                ->preload()
                                ->helperText(new \Illuminate\Support\HtmlString('<span style="color: #ef4444;">Sesuaikan rekening transfer</span>'))
                                ->required()
                                ->columnSpan(1),
                            
                            Forms\Components\DatePicker::make('date_expense')
                                ->label('Tanggal Pengeluaran')
                                ->date()
                                ->required()
                                ->native(false)
                                ->displayFormat('d M Y')
                                ->default(now())
                                ->columnSpan(1),
                            
                            Forms\Components\Select::make('kategori_transaksi')
                                ->options([
                                    'uang_keluar' => 'Uang Keluar',
                                ])
                                ->default('uang_keluar')
                                ->label('Tipe Transaksi')
                                ->required()
                                ->disabled()
                                ->columnSpan(1),
                        ]),

                    Forms\Components\Grid::make(1)
                        ->schema([
                            Forms\Components\TextInput::make('no_nd')
                                ->label('Nomor Nota Dinas')
                                ->required()
                                ->live()
                                ->helperText('Akan otomatis terisi setelah memilih detail nota dinas'),
                            
                            Forms\Components\Textarea::make('note')
                                ->label('Catatan Tambahan / Keperluan')
                                ->required()
                                ->rows(3)
                                ->live()
                                ->helperText('Akan otomatis terisi dari detail nota dinas, dapat diedit'),
                            
                            Forms\Components\FileUpload::make('image')
                                ->label('Bukti Pembayaran')
                                ->image()
                                ->imageEditor()
                                ->directory('expense-ops/' . date('Y/m'))
                                ->visibility('private')
                                ->downloadable()
                                ->openable()
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf'])
                                ->maxSize(1280)
                                ->helperText('Max 1MB. JPG, PNG, or PDF format.')
                                ->required(),
                        ]),
                ])
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => \Illuminate\Support\Str::title($state))
                    ->tooltip('Expense Name')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->prefix('Rp. ')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? 'Rp. ' . number_format($state, 0, ',', '.') : 'Rp. 0')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->formatStateUsing(fn($state): string => 'Total: Rp. ' . number_format($state, 0, ',', '.'))
                    ])
                    ->toggleable(),
                Tables\Columns\TextColumn::make('kategori_transaksi')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'uang_masuk' => 'success',
                        'uang_keluar' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'uang_masuk' => 'Uang Masuk',
                        'uang_keluar' => 'Uang Keluar',
                        default => $state,
                    })
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('paymentMethod.name')
                    ->label('Sumber Pembayaran')
                    ->formatStateUsing(function ($record) {
                        $method = $record->paymentMethod;
                        if (!$method) return 'N/A';
                        return $method->is_cash ? 'Kas/Tunai' : ($method->bank_name ? "{$method->bank_name}" : $method->name);
                    })
                    ->description(fn (ExpenseOps $record): string => $record->paymentMethod?->no_rekening ?? 'N/A')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->tooltip('Metode Pembayaran (No. Rekening)'),
                Tables\Columns\TextColumn::make('date_expense')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable()
                    ->tooltip('Expense Date'),
                Tables\Columns\TextColumn::make('no_nd')
                    ->label('Nota Dinas')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->copyable()
                    ->copyMessage('Nomor nota dinas berhasil disalin')
                    ->tooltip('Document Number'),
                    
                Tables\Columns\TextColumn::make('notaDinas.status')
                    ->label('Status ND')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'diajukan' => 'warning',
                        'disetujui' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'diajukan' => 'Diajukan',
                        'disetujui' => 'Disetujui',
                        default => $state,
                    })
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('vendor.name')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->tooltip('Vendor/Supplier'),
                    
                Tables\Columns\TextColumn::make('bank_name')
                    ->label('Bank Transfer')
                    ->searchable()
                    ->toggleable()
                    ->description(fn (ExpenseOps $record): string => $record->account_holder ?? 'N/A')
                    ->tooltip('Bank & Account Holder'),
                    
                Tables\Columns\TextColumn::make('tanggal_transfer')
                    ->label('Tgl Transfer')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable()
                    ->tooltip('Transfer Date'),
                Tables\Columns\TextColumn::make('note')
                    ->searchable()
                    ->toggleable()
                    ->wrap()
                    ->tooltip('Additional Notes'),
                Tables\Columns\ImageColumn::make('image')
                    ->alignCenter()
                    ->square()
                    ->label('Bukti')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->defaultImageUrl(url('/images/placeholder.png'))
                    ->tooltip('Receipt Image'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->tooltip('Created Date'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip('Last Update'),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip('Deletion Date'),
            ])
            ->defaultSort('date_expense', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('payment_method_id')
                    ->relationship('paymentMethod', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->label('Payment Method'),
                Tables\Filters\SelectFilter::make('kategori_transaksi')
                    ->options([
                        'uang_masuk' => 'Uang Masuk',
                        'uang_keluar' => 'Uang Keluar',
                    ])
                    ->multiple()
                    ->label('Transaction Category'),
                Tables\Filters\Filter::make('date_expense')
                    ->form([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\DatePicker::make('date_from')
                                ->label('Date From'),
                            Forms\Components\DatePicker::make('date_until')
                                ->label('Date Until')
                                ->default(now()),
                        ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['date_from'], fn(Builder $query, $date): Builder => $query->whereDate('date_expense', '>=', $date))
                            ->when($data['date_until'], fn(Builder $query, $date): Builder => $query->whereDate('date_expense', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['date_from'] ?? null) {
                            $indicators['from'] = 'From ' . Carbon::parse($data['date_from'])->toFormattedDateString();
                        }
                        if ($data['date_until'] ?? null) {
                            $indicators['until'] = 'Until ' . Carbon::parse($data['date_until'])->toFormattedDateString();
                        }
                        return $indicators;
                    })
                    ->label('Expense Date Range'),
                Tables\Filters\SelectFilter::make('amount')
                    ->label('Amount Range')
                    ->options([
                        'low' => 'Low (< Rp. 1,000,000)',
                        'medium' => 'Medium (Rp. 1,000,000 - 5,000,000)',
                        'high' => 'High (> Rp. 5,000,000)',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'low' => $query->where('amount', '<', 1000000),
                            'medium' => $query->whereBetween('amount', [1000000, 5000000]),
                            'high' => $query->where('amount', '>', 5000000),
                            default => $query,
                        };
                    }),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->filtersFormColumns(2)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->modalWidth('lg')
                        ->tooltip('Lihat detail pengeluaran'),
                    Tables\Actions\EditAction::make()
                        ->tooltip('Edit pengeluaran'),
                    Tables\Actions\Action::make('duplicate')
                        ->label('Duplikat')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('warning')
                        ->action(function (ExpenseOps $record, array $data): void {
                            ExpenseOps::create([
                                'name' => $record->name . ' (Copy)',
                                'amount' => $record->amount,
                                'payment_method_id' => $record->payment_method_id,
                                'date_expense' => now(),
                                'kategori_transaksi' => $record->kategori_transaksi,
                                'no_nd' => $record->no_nd + 1,
                                'note' => $record->note,
                            ]);
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Duplikat Pengeluaran')
                        ->modalDescription('Apakah Anda yakin ingin menduplikat pengeluaran ini?')
                        ->tooltip('Duplikat pengeluaran ini'),
                    Tables\Actions\Action::make('download_receipt')
                        ->label('Download Bukti')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->url(fn(ExpenseOps $record): ?string => $record->image ? Storage::url($record->image) : null, shouldOpenInNewTab: true)
                        ->visible(fn(ExpenseOps $record): bool => $record->image !== null)
                        ->tooltip('Download bukti pembayaran'),
                    Tables\Actions\DeleteAction::make()
                        ->requiresConfirmation()
                        ->tooltip('Hapus pengeluaran'),
                    Tables\Actions\RestoreAction::make()
                        ->tooltip('Pulihkan pengeluaran'),
                    Tables\Actions\ForceDeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Permanen Pengeluaran')
                        ->modalDescription('Apakah Anda yakin ingin menghapus pengeluaran ini secara permanen? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, hapus permanen')
                        ->tooltip('Hapus permanen pengeluaran'),
                ])
                    ->tooltip('Aksi Pengeluaran')
                    ->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Hapus Permanen')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Permanen Pengeluaran Terpilih')
                        ->modalDescription('PERHATIAN: Data akan dihapus secara permanen dan tidak dapat dikembalikan!')
                        ->modalSubmitActionLabel('Ya, Hapus Permanen')
                        ->modalCancelActionLabel('Batal'),
                    BulkAction::make('export')
                        ->label('Export ke Excel')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(function (Collection $records) {
                            return Excel::download(new ExpenseOpsExport($records), 'pengeluaran-operasional-' . date('Y-m-d') . '.xlsx');
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('mark_as_uang_keluar')
                        ->label('Tandai sebagai Uang Keluar')
                        ->icon('heroicon-o-arrow-down')
                        ->color('danger')
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                $record->update(['kategori_transaksi' => 'uang_keluar']);
                            });
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ])->label('Aksi Massal'),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Buat Pengeluaran Pertama')
                    ->icon('heroicon-o-plus-circle'),
            ])
            ->emptyStateHeading('Belum Ada Pengeluaran Operasional')
            ->emptyStateDescription('Mulai dengan membuat pengeluaran operasional pertama Anda.')
            ->emptyStateIcon('heroicon-o-banknotes')
            ->poll('60s')
            ->striped()
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10, 25, 50]);
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
            'index' => Pages\ListExpenseOps::route('/'),
            'create' => Pages\CreateExpenseOps::route('/create'),
            'edit' => Pages\EditExpenseOps::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [ExpenseOpsOverview::class];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Finance';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        // Menampilkan jumlah total record sebagai badge
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        // Memberikan warna pada badge untuk visibilitas yang lebih baik
        // Pilihan lain: 'primary', 'success', 'danger', 'info'
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Pengeluaran operasional harian kantor';
    }
}
