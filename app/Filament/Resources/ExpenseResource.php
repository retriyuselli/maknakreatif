<?php

namespace App\Filament\Resources;

use App\Enums\TransactionCategoryUangKeluar;
use App\Enums\TransactionCategoryUangMasuk;
use App\Filament\Clusters\Pengeluaran;
use App\Filament\Resources\ExpenseResource\Pages;
use App\Filament\Resources\ExpenseResource\Widgets\ExpenseOverview;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\RawJs;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = 'Pengeluaran Wedding';
    protected static ?string $cluster = Pengeluaran::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('order_id')
                    ->relationship('order', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record?->name ?? 'No Name')
                    ->required()
                    ->preload()
                    ->disabled()
                    ->label('Project')
                    ->searchable(),
                Forms\Components\Select::make('vendor_id')
                    ->relationship('vendor', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record?->name ?? 'No Vendor')
                    ->disabled()
                    ->required()
                    ->label('Vendor')
                    ->searchable(),
                Forms\Components\TextInput::make('note')
                    ->required()
                    ->disabled()
                    ->label('Keterangan pembayaran')
                    ->maxLength(255),
                Forms\Components\TextInput::make('no_nd')
                    ->required()
                    ->disabled()
                    ->prefix('ND-0')
                    ->label('Nomor Nota Dinas')
                    ->numeric(),
                Forms\Components\Select::make('kategori_transaksi')
                    ->options([
                        'uang_masuk' => 'Uang Masuk',
                        'uang_keluar' => 'Uang Keluar',
                    ])
                    ->default('uang_keluar')
                    ->disabled()
                    ->label('Tipe Transaksi')
                    ->required(),
                Forms\Components\DatePicker::make('date_expense')
                    ->date()
                    ->disabled()
                    ->label('Tanggal pembayaran'),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->label('Jumlah pembayaran')
                    ->disabled()
                    ->prefix('Rp. ')
                    ->numeric()
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(','),
                Forms\Components\Select::make('payment_method_id')
                    ->relationship('paymentMethod', 'no_rekening')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record?->no_rekening ?? 'No Account')
                    ->disabled()
                    ->label('Sumber pembayaran')
                    ->required(),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->disabled()
                    ->directory('expense_wedding')
                    ->acceptedFileTypes(['image/*', 'application/pdf']) // Allow images and PDFs
                    ->label('Invoice'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.prospect.name_event')
                    ->numeric()
                    ->searchable()
                    ->label('Project')
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Project copied')
                    ->formatStateUsing(fn ($state) => $state ?? 'No Project'),
                    
                Tables\Columns\TextColumn::make('vendor.name')
                    ->searchable()
                    ->label('Vendor')
                    ->copyable()
                    ->copyMessage('Vendor copied')
                    ->formatStateUsing(fn ($state) => $state ?? 'No Vendor'),
                    
                Tables\Columns\TextColumn::make('note')
                    ->searchable()
                    ->label('Keterangan')
                    ->wrap()
                    ->limit(30),
                    
                Tables\Columns\TextColumn::make('no_nd')
                    ->searchable()
                    ->label('Nomor ND'),
                    
                Tables\Columns\TextColumn::make('kategori_transaksi')
                    ->searchable()
                    ->label('Kategori Pengeluaran'),
                Tables\Columns\TextColumn::make('paymentMethod.bank_name')
                    ->searchable()
                    ->label('Sumber Pembayaran')
                    ->description(fn ($record) => $record->paymentMethod?->no_rekening ?? 'N/A')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ?? 'No Bank'),
                    
                Tables\Columns\TextColumn::make('date_expense')
                    ->date('d M Y')
                    ->sortable()
                    ->label('Tanggal')
                    ->color('primary'),
                    
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->formatStateUsing(fn (string $state): string => 'Rp. ' . number_format($state, 0, ',', '.'))
                    ->label('Nominal')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->formatStateUsing(fn (string $state): string => 'Rp. ' . number_format($state, 0, ',', '.')),
                    ])
                    ->sortable()
                    ->alignment('right')
                    ->color(fn ($state) => $state > 5000000 ? 'danger' : 'success'),
                    
                Tables\Columns\ImageColumn::make('image')
                    ->square()
                    ->label('Proof'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Basic CRUD Actions
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalDescription('Apakah Anda yakin ingin menghapus expense yang dipilih? Data tidak bisa dikembalikan.')
                        ->modalSubmitActionLabel('Ya, Hapus')
                        ->modalCancelActionLabel('Batal'),
                        
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Pulihkan Terpilih')
                        ->icon('heroicon-o-arrow-path')
                        ->color('success'),
                        
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Hapus Permanen')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalDescription('PERHATIAN: Data akan dihapus secara permanen dan tidak dapat dikembalikan!')
                        ->modalSubmitActionLabel('Ya, Hapus Permanen')
                        ->modalCancelActionLabel('Batal'),

                    // Export Actions
                    Tables\Actions\BulkAction::make('export_excel')
                        ->label('Export ke Excel')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->action(function ($records) {
                            return response()->streamDownload(function () use ($records) {
                                $csv = "Vendor,Keterangan,No ND,Tanggal,Nominal,Sumber Pembayaran\n";
                                foreach ($records as $record) {
                                    $csv .= sprintf(
                                        '%s,%s,%s,%s,%s,%s',
                                        $record->vendor?->name ?? 'N/A',
                                        $record->note,
                                        $record->no_nd,
                                        $record->date_expense?->format('d/m/Y') ?? 'N/A',
                                        number_format($record->amount, 0, ',', '.'),
                                        $record->paymentMethod?->bank_name ?? 'N/A'
                                    ) . "\n";
                                }
                                echo $csv;
                            }, 'expense_export_' . now()->format('Y-m-d_H-i-s') . '.csv');
                        })
                        ->deselectRecordsAfterCompletion(),

                    // Financial Actions
                    Tables\Actions\BulkAction::make('calculate_total')
                        ->label('Hitung Total')
                        ->icon('heroicon-o-calculator')
                        ->color('info')
                        ->action(function ($records) {
                            $total = $records->sum('amount');
                            $count = $records->count();
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Kalkulasi Selesai')
                                ->body("Total dari {$count} expense terpilih: Rp " . number_format($total, 0, ',', '.'))
                                ->success()
                                ->icon('heroicon-o-banknotes')
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    // Status Update Actions
                    Tables\Actions\BulkAction::make('mark_verified')
                        ->label('Tandai Terverifikasi')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $updated = 0;
                            foreach ($records as $record) {
                                // Assuming there's a verified_at field or similar
                                $record->update(['verified_at' => now()]);
                                $updated++;
                            }
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Verifikasi Berhasil')
                                ->body("{$updated} expense telah ditandai sebagai terverifikasi")
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalDescription('Tandai expense terpilih sebagai terverifikasi?')
                        ->deselectRecordsAfterCompletion(),

                    // Period Actions
                    Tables\Actions\BulkAction::make('update_period')
                        ->label('Update Periode')
                        ->icon('heroicon-o-calendar')
                        ->color('warning')
                        ->form([
                            Forms\Components\DatePicker::make('new_date')
                                ->label('Tanggal Baru')
                                ->required()
                                ->default(now()),
                        ])
                        ->action(function ($records, array $data) {
                            $updated = 0;
                            foreach ($records as $record) {
                                $record->update(['date_expense' => $data['new_date']]);
                                $updated++;
                            }
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Update Berhasil')
                                ->body("{$updated} expense telah diupdate tanggalnya")
                                ->success()
                                ->send();
                        })
                        ->modalSubmitActionLabel('Update')
                        ->modalCancelActionLabel('Batal')
                        ->deselectRecordsAfterCompletion(),

                    // Duplicate Action
                    Tables\Actions\BulkAction::make('duplicate')
                        ->label('Duplikasi')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('gray')
                        ->action(function ($records) {
                            $duplicated = 0;
                            foreach ($records as $record) {
                                $newRecord = $record->replicate();
                                $newRecord->note = $record->note . ' (Copy)';
                                $newRecord->no_nd = $record->no_nd . '-COPY';
                                $newRecord->date_expense = now();
                                $newRecord->save();
                                $duplicated++;
                            }
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Duplikasi Berhasil')
                                ->body("{$duplicated} expense telah diduplikasi")
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalDescription('Duplikasi expense terpilih dengan tanggal hari ini?')
                        ->deselectRecordsAfterCompletion(),

                    // Generate Report
                    Tables\Actions\BulkAction::make('generate_report')
                        ->label('Buat Laporan')
                        ->icon('heroicon-o-document-text')
                        ->color('primary')
                        ->form([
                            Forms\Components\TextInput::make('report_title')
                                ->label('Judul Laporan')
                                ->default('Laporan Pengeluaran')
                                ->required(),
                            Forms\Components\Textarea::make('report_notes')
                                ->label('Catatan Laporan')
                                ->placeholder('Tambahkan catatan untuk laporan ini...')
                                ->rows(3),
                        ])
                        ->action(function ($records, array $data) {
                            $total = $records->sum('amount');
                            $vendors = $records->pluck('vendor.name')->filter()->unique();
                            
                            $reportContent = [
                                'title' => $data['report_title'],
                                'generated_at' => now()->format('d/m/Y H:i:s'),
                                'total_records' => $records->count(),
                                'total_amount' => number_format($total, 0, ',', '.'),
                                'vendors_involved' => $vendors->count(),
                                'notes' => $data['report_notes'] ?? '',
                                'records' => $records->map(function ($record) {
                                    return [
                                        'vendor' => $record->vendor?->name ?? 'N/A',
                                        'note' => $record->note,
                                        'amount' => number_format($record->amount, 0, ',', '.'),
                                        'date' => $record->date_expense?->format('d/m/Y') ?? 'N/A',
                                    ];
                                })->toArray()
                            ];
                            
                            return response()->streamDownload(function () use ($reportContent) {
                                echo "=== {$reportContent['title']} ===\n\n";
                                echo "Dibuat pada: {$reportContent['generated_at']}\n";
                                echo "Total Records: {$reportContent['total_records']}\n";
                                echo "Total Amount: Rp {$reportContent['total_amount']}\n";
                                echo "Vendor Terlibat: {$reportContent['vendors_involved']}\n\n";
                                
                                if (!empty($reportContent['notes'])) {
                                    echo "Catatan: {$reportContent['notes']}\n\n";
                                }
                                
                                echo "=== DETAIL EXPENSES ===\n";
                                foreach ($reportContent['records'] as $record) {
                                    echo "- {$record['vendor']}: {$record['note']} (Rp {$record['amount']}) - {$record['date']}\n";
                                }
                            }, 'expense_report_' . now()->format('Y-m-d_H-i-s') . '.txt');
                        })
                        ->modalSubmitActionLabel('Generate')
                        ->modalCancelActionLabel('Batal')
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
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
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            ExpenseOverview::class,
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Finance';
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
        return 'Pengeluaran wedding yang dikeluarkan untuk berbagai keperluan proyek, termasuk pembayaran vendor dan biaya lainnya';
    }
}
