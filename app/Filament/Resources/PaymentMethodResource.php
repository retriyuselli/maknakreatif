<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentMethodResource\Pages;
use App\Filament\Resources\PaymentMethodResource\RelationManagers;
use App\Filament\Resources\PaymentMethodResource\Widgets;
use App\Models\PaymentMethod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Carbon;
use Filament\Resources\Resource;
use Filament\Support\RawJs;

class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?string $navigationLabel = 'Daftar Rekening';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Rekening')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->placeholder('nama pemilik rekening')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('bank_name')
                            ->prefix('Bank ')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('cabang')
                            ->placeholder('cabang bank (opsional)')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('no_rekening')
                            ->required()
                            ->numeric(),
                        Forms\Components\Toggle::make('is_cash')
                            ->required(),
                    ])->columns(2),
                Forms\Components\Section::make('Saldo Awal')
                    ->description('Isi jika rekening ini memiliki saldo sebelum dicatat di sistem. Saldo ini akan menjadi titik awal perhitungan.')
                    ->schema([
                        Forms\Components\TextInput::make('opening_balance')
                            ->label('Saldo Awal (Opening Balance)')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->default(0)
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(','),
                        Forms\Components\DatePicker::make('opening_balance_date')
                            ->label('Tanggal Saldo Awal')
                            ->default(now())
                            ->native(false)
                            ->displayFormat('d M Y'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_cash')
                    ->label('Tunai')
                    ->boolean()
                    ->trueIcon('heroicon-o-banknotes')
                    ->falseIcon('heroicon-o-credit-card')
                    ->trueColor('warning')
                    ->falseColor('info'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Rekening')
                    ->searchable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('bank_name')
                    ->label('Bank')
                    ->searchable()
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('no_rekening')
                    ->label('Nomor Rekening')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Nomor rekening disalin')
                    ->fontFamily('mono'),
                Tables\Columns\TextColumn::make('opening_balance')
                    ->label('Saldo Awal')
                    ->money('idr')
                    ->sortable()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('opening_balance_date')
                    ->label('Tgl Pembukuan')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('saldo')
                    ->label('Saldo Saat Ini')
                    ->money('idr')
                    ->sortable()
                    ->weight('bold')
                    ->color(fn ($state) => $state < 0 ? 'danger' : ($state == 0 ? 'warning' : 'success'))
                    ->description(fn ($record) => 'Perubahan: ' . 
                        ($record->perubahan_saldo >= 0 ? '+' : '') . 
                        'Rp ' . number_format(abs($record->perubahan_saldo), 0, ',', '.')),
                Tables\Columns\TextColumn::make('status_perubahan')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'naik' => 'success',
                        'turun' => 'danger',
                        'tetap' => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'naik' => 'heroicon-o-arrow-trending-up',
                        'turun' => 'heroicon-o-arrow-trending-down',
                        'tetap' => 'heroicon-o-minus',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'naik' => 'Naik',
                        'turun' => 'Turun',
                        'tetap' => 'Tetap',
                    }),
                Tables\Columns\TextColumn::make('cabang')
                    ->label('Cabang')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diupdate')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('is_cash')
                    ->label('Tampilkan Hanya Uang Tunai')
                    ->query(fn (Builder $query): Builder => $query->where('is_cash', true))
                    ->toggle(),
                Filter::make('saldo_positif')
                    ->label('Saldo Positif')
                    ->query(function (Builder $query): Builder {
                        return $query->whereRaw('
                            (opening_balance + 
                            COALESCE((SELECT SUM(nominal) FROM data_pembayarans WHERE payment_method_id = payment_methods.id AND tgl_bayar >= opening_balance_date AND deleted_at IS NULL), 0) +
                            COALESCE((SELECT SUM(nominal) FROM pendapatan_lains WHERE payment_method_id = payment_methods.id AND tgl_bayar >= opening_balance_date AND deleted_at IS NULL), 0) -
                            COALESCE((SELECT SUM(amount) FROM expenses WHERE payment_method_id = payment_methods.id AND date_expense >= opening_balance_date AND deleted_at IS NULL), 0) -
                            COALESCE((SELECT SUM(amount) FROM expense_ops WHERE payment_method_id = payment_methods.id AND date_expense >= opening_balance_date AND deleted_at IS NULL), 0) -
                            COALESCE((SELECT SUM(amount) FROM pengeluaran_lains WHERE payment_method_id = payment_methods.id AND date_expense >= opening_balance_date AND deleted_at IS NULL), 0)
                            ) > 0
                        ');
                    }),
                Filter::make('saldo_negatif')
                    ->label('Saldo Negatif')
                    ->query(function (Builder $query): Builder {
                        return $query->whereRaw('
                            (opening_balance + 
                            COALESCE((SELECT SUM(nominal) FROM data_pembayarans WHERE payment_method_id = payment_methods.id AND tgl_bayar >= opening_balance_date AND deleted_at IS NULL), 0) +
                            COALESCE((SELECT SUM(nominal) FROM pendapatan_lains WHERE payment_method_id = payment_methods.id AND tgl_bayar >= opening_balance_date AND deleted_at IS NULL), 0) -
                            COALESCE((SELECT SUM(amount) FROM expenses WHERE payment_method_id = payment_methods.id AND date_expense >= opening_balance_date AND deleted_at IS NULL), 0) -
                            COALESCE((SELECT SUM(amount) FROM expense_ops WHERE payment_method_id = payment_methods.id AND date_expense >= opening_balance_date AND deleted_at IS NULL), 0) -
                            COALESCE((SELECT SUM(amount) FROM pengeluaran_lains WHERE payment_method_id = payment_methods.id AND date_expense >= opening_balance_date AND deleted_at IS NULL), 0)
                            ) < 0
                        ');
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view_detail')
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn ($record) => static::getUrl('view', ['record' => $record]))
                    ->tooltip('Lihat detail lengkap rekening dengan tab Uang Masuk, Uang Keluar, dan Laporan'),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('export_transaksi')
                        ->label('Export Transaksi')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('gray')
                        ->action(function ($record) {
                            \Filament\Notifications\Notification::make()
                                ->title('Export Transaksi')
                                ->body('Fitur export akan segera tersedia.')
                                ->info()
                                ->send();
                        }),
                ])
                ->label('Aksi Lainnya')
                ->color('gray')
                ->icon('heroicon-o-ellipsis-vertical')
                ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-credit-card')
            ->emptyStateHeading('Tidak ada rekening ditemukan')
            ->emptyStateDescription('Silakan buat rekening baru untuk memulai mencatat transaksi keuangan.')
            ->emptyStateActions([
                Tables\Actions\Action::make('create')
                    ->label('Buat Rekening Baru')
                    ->url(static::getUrl('create'))
                    ->icon('heroicon-o-plus')
                    ->button(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginationPageOptions([10, 25, 50])
            ->striped()
            ->description('Kelola semua rekening bank dan kas tunai. Saldo dihitung otomatis berdasarkan transaksi masuk dan keluar.');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            Widgets\PaymentMethodStatsWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentMethods::route('/'),
            'create' => Pages\CreatePaymentMethod::route('/create'),
            'view' => Pages\PaymentMethod::route('/{record}'),
            'edit' => Pages\EditPaymentMethod::route('/{record}/edit'),
        ];
    }
}
