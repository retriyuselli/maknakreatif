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

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Pengeluaran')
                ->description('Detail pengeluaran operasional')
                ->icon('heroicon-o-banknotes')
                ->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Listrik, Internet, ATK, dll.')
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->prefix('Rp. ')
                            ->numeric()
                            ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') : '')
                            ->dehydrateStateUsing(fn ($state) => (int) str_replace(['.', ','], '', $state))
                            ->placeholder('0')
                            ->inputMode('numeric')
                            ->columnSpan(1),
                    ]),
                ]),
            
            Forms\Components\Section::make('Detail Transaksi')
                ->description('Informasi pembayaran dan dokumen')
                ->icon('heroicon-o-document-text')
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\Select::make('payment_method_id')
                            ->relationship('paymentMethod', 'name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->is_cash ? 'Kas/Tunai' : ($record->bank_name ? "{$record->bank_name} - {$record->no_rekening}" : $record->name))
                            ->label('Sumber pembayaran')
                            ->searchable()
                            ->preload()
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
                            ->disabled()
                            ->required()
                            ->columnSpan(1),
                    ]),
                    Forms\Components\TextInput::make('no_nd')
                        ->numeric()
                        ->required()
                        ->prefix('ND-0')
                        ->label('Nomor Nota Dinas')
                        ->placeholder('001'),
                    Forms\Components\Textarea::make('note')
                        ->label('Catatan Tambahan')
                        ->required()
                        ->rows(3)
                        ->placeholder('Jelaskan detail pengeluaran atau keterangan tambahan lainnya...'),
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
                        ->helperText('Max 1MB. JPG, PNG, or PDF format.'),
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
                    ->formatStateUsing(fn ($state) => $state ? "ND-0{$state}" : 'N/A')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->copyable()
                    ->copyMessage('Nomor nota dinas berhasil disalin')
                    ->tooltip('Document Number'),
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
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Permanen Pengeluaran Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus pengeluaran yang dipilih secara permanen? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, hapus permanen'),
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
