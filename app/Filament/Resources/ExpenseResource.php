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
                    ->required()
                    ->preload()
                    ->disabled()
                    ->label('Project')
                    ->searchable(),
                Forms\Components\Select::make('vendor_id')
                    ->relationship('vendor', 'name')
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
                    ->copyMessage('Vendor copied'),
                    
                Tables\Columns\TextColumn::make('vendor.name')
                    ->searchable()
                    ->label('Vendor')
                    ->copyable()
                    ->copyMessage('Vendor copied'),
                    
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
                    ->badge(),
                    
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
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
