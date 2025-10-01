<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FixedAssetResource\Pages;
use App\Filament\Resources\FixedAssetResource\RelationManagers;
use App\Models\FixedAsset;
use App\Models\ChartOfAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\FontWeight;
use Filament\Support\RawJs;

class FixedAssetResource extends Resource
{
    protected static ?string $model = FixedAsset::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Aset Tetap';
    protected static ?string $navigationGroup = 'Akuntansi';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Aset')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('asset_code')
                                    ->label('Kode Aset')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->default(fn () => FixedAsset::generateAssetCode())
                                    ->maxLength(50)
                                    ->helperText('Kode unik untuk identifikasi aset. Akan dibuat otomatis berdasarkan kategori dan tahun.'),

                                Forms\Components\Select::make('category')
                                    ->label('Kategori')
                                    ->options(FixedAsset::CATEGORIES)
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if ($state && !$get('asset_code')) {
                                            $set('asset_code', FixedAsset::generateAssetCode($state));
                                        }
                                    })
                                    ->helperText('Pilih jenis aset sesuai kategori untuk pengelompokan dan kode otomatis.'),

                                Forms\Components\Select::make('condition')
                                    ->label('Kondisi')
                                    ->options(FixedAsset::CONDITIONS)
                                    ->default('GOOD')
                                    ->required()
                                    ->helperText('Kondisi fisik aset saat ini untuk evaluasi nilai dan perawatan.'),
                            ]),

                        Forms\Components\TextInput::make('asset_name')
                            ->label('Nama Aset')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->helperText('Nama deskriptif aset untuk identifikasi mudah. Contoh: "Laptop Dell Inspiron 15" atau "Meja Kerja Kayu Jati".'),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('location')
                                    ->label('Lokasi')
                                    ->maxLength(255)
                                    ->helperText('Tempat aset berada saat ini. Contoh: "Lantai 2 Ruang Keuangan" atau "Gudang A Rak 3".'),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->default(true)
                                    ->helperText('Status aset: Aktif (masih digunakan) atau Tidak Aktif (sudah tidak digunakan).'),
                            ]),
                    ]),

                Forms\Components\Section::make('Informasi Pembelian')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\DatePicker::make('purchase_date')
                                    ->label('Tanggal Pembelian')
                                    ->required()
                                    ->default(now())
                                    ->helperText('Tanggal resmi pembelian aset untuk menentukan awal perhitungan penyusutan.'),

                                Forms\Components\TextInput::make('purchase_price')
                                    ->label('Harga Pembelian')
                                    ->required()
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $set('current_book_value', $state);
                                    })
                                    ->helperText('Total biaya pembelian aset termasuk pajak, biaya pengiriman, dan instalasi.'),

                                Forms\Components\TextInput::make('salvage_value')
                                    ->label('Nilai Sisa')
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->default(0)
                                    ->helperText('Perkiraan nilai aset di akhir masa manfaat. Biasanya 10% dari harga beli atau 0. Contoh: Laptop Rp 10jt, nilai sisa Rp 1jt.'),
                            ]),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('supplier')
                                    ->label('Pemasok')
                                    ->maxLength(255)
                                    ->helperText('Nama perusahaan atau toko tempat membeli aset untuk referensi dan claim garansi.'),

                                Forms\Components\TextInput::make('invoice_number')
                                    ->label('Nomor Invoice')
                                    ->maxLength(255)
                                    ->helperText('Nomor faktur atau invoice pembelian untuk audit dan pelacakan dokumen.'),

                                Forms\Components\DatePicker::make('warranty_expiry')
                                    ->label('Masa Garansi Berakhir')
                                    ->helperText('Tanggal berakhirnya garansi dari supplier untuk perencanaan maintenance.'),
                            ]),
                    ]),

                Forms\Components\Section::make('Pengaturan Penyusutan')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('depreciation_method')
                                    ->label('Metode Penyusutan')
                                    ->options(FixedAsset::DEPRECIATION_METHODS)
                                    ->default('STRAIGHT_LINE')
                                    ->required()
                                    ->helperText('Metode perhitungan penyusutan. Garis Lurus = nilai penyusutan sama setiap bulan (paling umum). Saldo Menurun = penyusutan lebih besar di awal.'),

                                Forms\Components\TextInput::make('useful_life_years')
                                    ->label('Masa Manfaat (Tahun)')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->helperText('Perkiraan berapa tahun aset masih bisa digunakan secara produktif. Panduan: Komputer/Laptop 3-5 tahun, Furniture 5-10 tahun, Kendaraan 5-8 tahun, Bangunan 20-50 tahun.'),

                                Forms\Components\TextInput::make('useful_life_months')
                                    ->label('Bulan Tambahan')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->maxValue(11)
                                    ->helperText('Bulan tambahan selain tahun (0-11). Contoh: 3 tahun 6 bulan = Years: 3, Months: 6.'),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('chart_of_account_id')
                                    ->label('Akun Aset')
                                    ->relationship(
                                        'chartOfAccount',
                                        'account_name',
                                        fn (Builder $query) => $query->where('account_type', 'HARTA')
                                            ->where('is_active', true)
                                    )
                                    ->getOptionLabelFromRecordUsing(fn (ChartOfAccount $record): string => 
                                        "{$record->account_code} - {$record->account_name}")
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->helperText('Pilih akun neraca untuk mencatat nilai aset ini. Biasanya "Aset Tetap - [Kategori]".'),

                                Forms\Components\Select::make('depreciation_account_id')
                                    ->label('Akun Akumulasi Penyusutan')
                                    ->relationship(
                                        'depreciationAccount',
                                        'account_name',
                                        fn (Builder $query) => $query->where('account_type', 'HARTA')
                                            ->where('account_name', 'like', '%akumulasi%')
                                            ->where('is_active', true)
                                    )
                                    ->getOptionLabelFromRecordUsing(fn (ChartOfAccount $record): string => 
                                        "{$record->account_code} - {$record->account_name}")
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->helperText('Akun untuk mencatat akumulasi penyusutan. Akan mengurangi nilai aset di neraca.'),
                            ]),
                    ]),

                Forms\Components\Section::make('Status Saat Ini')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('accumulated_depreciation')
                                    ->label('Akumulasi Penyusutan')
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->default(0)
                                    ->readOnly()
                                    ->helperText('Total penyusutan yang sudah terjadi. Dihitung otomatis dari riwayat penyusutan bulanan.'),

                                Forms\Components\TextInput::make('current_book_value')
                                    ->label('Nilai Buku Saat Ini')
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->readOnly()
                                    ->helperText('Nilai aset saat ini = Harga Beli - Akumulasi Penyusutan. Dihitung otomatis.'),
                            ]),

                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Catatan tambahan tentang aset seperti spesifikasi detail, riwayat perbaikan, atau informasi penting lainnya.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('asset_code')
            ->columns([
                Tables\Columns\TextColumn::make('asset_code')
                    ->label('Kode Aset')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->copyable(),

                Tables\Columns\TextColumn::make('asset_name')
                    ->label('Nama Aset')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(30),

                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->formatStateUsing(fn ($state) => FixedAsset::CATEGORIES[$state] ?? $state)
                    ->color(fn ($state) => match($state) {
                        'BUILDING' => 'success',
                        'EQUIPMENT' => 'info',
                        'FURNITURE' => 'warning',
                        'VEHICLE' => 'danger',
                        'COMPUTER' => 'primary',
                        default => 'gray'
                    }),

                Tables\Columns\TextColumn::make('purchase_price')
                    ->label('Harga Beli')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('current_book_value')
                    ->label('Nilai Buku')
                    ->money('IDR')
                    ->sortable()
                    ->color(fn ($record) => $record->current_book_value <= $record->salvage_value ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('condition')
                    ->label('Kondisi')
                    ->badge()
                    ->formatStateUsing(fn ($state) => FixedAsset::CONDITIONS[$state] ?? $state)
                    ->color(fn ($state) => match($state) {
                        'EXCELLENT' => 'success',
                        'GOOD' => 'info',
                        'FAIR' => 'warning',
                        'POOR' => 'danger',
                        'DAMAGED' => 'gray',
                        default => 'gray'
                    }),

                Tables\Columns\TextColumn::make('location')
                    ->label('Lokasi')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('purchase_date')
                    ->label('Tanggal Beli')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->options(FixedAsset::CATEGORIES),

                Tables\Filters\SelectFilter::make('condition')
                    ->label('Kondisi')
                    ->options(FixedAsset::CONDITIONS),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Semua aset')
                    ->trueLabel('Hanya aktif')
                    ->falseLabel('Hanya tidak aktif'),

                Tables\Filters\Filter::make('needs_maintenance')
                    ->label('Perlu Maintenance')
                    ->query(fn (Builder $query): Builder => $query->needsMaintenance())
                    ->toggle(),

                Tables\Filters\Filter::make('purchase_date')
                    ->form([
                        Forms\Components\DatePicker::make('purchased_from')
                            ->label('Dibeli dari'),
                        Forms\Components\DatePicker::make('purchased_until')
                            ->label('Dibeli sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['purchased_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('purchase_date', '>=', $date),
                            )
                            ->when(
                                $data['purchased_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('purchase_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    
                    Tables\Actions\Action::make('depreciate')
                        ->label('Hitung Penyusutan')
                        ->icon('heroicon-o-calculator')
                        ->color('info')
                        ->action(function (FixedAsset $record) {
                            $monthlyDepreciation = $record->calculateMonthlyDepreciation();
                            $record->accumulated_depreciation += $monthlyDepreciation;
                            $record->updateBookValue();
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Penyusutan Dihitung')
                                ->body("Penyusutan bulanan: IDR " . number_format($monthlyDepreciation))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->visible(fn (FixedAsset $record) => !$record->isFullyDepreciated()),

                    Tables\Actions\Action::make('create_purchase_journal')
                        ->label('Buat Jurnal Pembelian')
                        ->icon('heroicon-o-document-plus')
                        ->color('info')
                        ->action(function (FixedAsset $record) {
                            $journal = $record->createPurchaseJournalEntry();
                            
                            if ($journal) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Jurnal Pembelian Dibuat')
                                    ->body("Batch: {$journal->batch_number}")
                                    ->success()
                                    ->actions([
                                        \Filament\Notifications\Actions\Action::make('view')
                                            ->label('Lihat Jurnal')
                                            ->url(fn (): string => route('filament.admin.resources.journal-batches.edit', $journal))
                                    ])
                                    ->send();
                            } else {
                                \Filament\Notifications\Notification::make()
                                    ->title('Jurnal Sudah Ada')
                                    ->body('Jurnal pembelian untuk aset ini sudah dibuat sebelumnya')
                                    ->warning()
                                    ->send();
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Buat Jurnal Pembelian Aset')
                        ->modalDescription('Ini akan membuat jurnal entry untuk pembelian aset tetap'),

                    Tables\Actions\Action::make('create_depreciation_journal')
                        ->label('Buat Jurnal Penyusutan')
                        ->icon('heroicon-o-document-text')
                        ->color('warning')
                        ->action(function (FixedAsset $record) {
                            $monthlyDepreciation = $record->calculateMonthlyDepreciation();
                            $journal = $record->createDepreciationJournalEntry($monthlyDepreciation);
                            
                            if ($journal) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Jurnal Penyusutan Dibuat')
                                    ->body("Batch: {$journal->batch_number}, Jumlah: IDR " . number_format($monthlyDepreciation))
                                    ->success()
                                    ->actions([
                                        \Filament\Notifications\Actions\Action::make('view')
                                            ->label('Lihat Jurnal')
                                            ->url(fn (): string => route('filament.admin.resources.journal-batches.edit', $journal))
                                    ])
                                    ->send();
                            } else {
                                \Filament\Notifications\Notification::make()
                                    ->title('Tidak Ada Penyusutan')
                                    ->body('Aset ini sudah sepenuhnya tersusut atau tidak ada penyusutan')
                                    ->warning()
                                    ->send();
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Buat Jurnal Penyusutan')
                        ->modalDescription('Ini akan membuat jurnal entry untuk penyusutan bulanan aset')
                        ->visible(fn (FixedAsset $record) => !$record->isFullyDepreciated()),

                    Tables\Actions\DeleteAction::make(),
                ])
                ->label('Aksi')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('gray')
                ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('bulk_depreciate')
                        ->label('Hitung Penyusutan untuk Terpilih')
                        ->icon('heroicon-o-calculator')
                        ->color('info')
                        ->action(function ($records) {
                            $total = 0;
                            foreach ($records as $record) {
                                if (!$record->isFullyDepreciated()) {
                                    $monthlyDepreciation = $record->calculateMonthlyDepreciation();
                                    $record->accumulated_depreciation += $monthlyDepreciation;
                                    $record->updateBookValue();
                                    $total += $monthlyDepreciation;
                                }
                            }
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Penyusutan Bulk Dihitung')
                                ->body("Total penyusutan: IDR " . number_format($total))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Aset Tetap Pertama')
                    ->icon('heroicon-o-plus'),
            ])
            ->defaultPaginationPageOption(25);
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
            'index' => Pages\ListFixedAssets::route('/'),
            'create' => Pages\CreateFixedAsset::route('/create'),
            'view' => Pages\ViewFixedAsset::route('/{record}'),
            'edit' => Pages\EditFixedAsset::route('/{record}/edit'),
            'depreciation-history' => Pages\DepreciationHistory::route('/{record}/depreciation-history'),
        ];
    }
}
