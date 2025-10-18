<?php

namespace App\Filament\Resources\BankStatementResource\RelationManagers;

use App\Models\BankReconciliationItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class BankReconciliationItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'reconciliationItems';
    
    protected static ?string $model = BankReconciliationItem::class;

    protected static ?string $title = 'Data Rekonsiliasi';
    protected static ?string $modelLabel = 'Item Rekonsiliasi';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('date')
                    ->label('Tanggal')
                    ->required()
                    ->native(false),
                    
                Forms\Components\Textarea::make('description')
                    ->label('Keterangan')
                    ->required()
                    ->maxLength(500)
                    ->rows(3)
                    ->formatStateUsing(function (?string $state): ?string {
                        // Clean excessive whitespace when loading data into form
                        if (!$state) return $state;
                        return preg_replace('/\s+/', ' ', trim($state));
                    })
                    ->dehydrateStateUsing(function (?string $state): ?string {
                        // Clean excessive whitespace when saving
                        if (!$state) return $state;
                        return preg_replace('/\s+/', ' ', trim($state));
                    }),
                    
                Forms\Components\Select::make('transaction_direction')
                    ->label('Jenis Transaksi')
                    ->options([
                        'masuk' => 'Uang Masuk (Penerimaan)',
                        'keluar' => 'Uang Keluar (Pengeluaran)',
                    ])
                    ->required()
                    ->helperText('Pilih jenis transaksi: Masuk untuk penerimaan, Keluar untuk pengeluaran')
                    ->afterStateHydrated(function (Forms\Components\Select $component, ?Model $record) {
                        if ($record && ($record->debit > 0 || $record->credit > 0)) {
                            $component->state($record->debit > 0 ? 'keluar' : 'masuk');
                        }
                    }),
                    
                Forms\Components\TextInput::make('amount')
                    ->label('Jumlah')
                    ->numeric()
                    ->prefix('Rp')
                    ->step(0.01)
                    ->minValue(0)
                    ->required()
                    ->helperText('Masukkan nominal transaksi')
                    ->afterStateHydrated(function (Forms\Components\TextInput $component, ?Model $record) {
                        if ($record) {
                            $amount = $record->debit > 0 ? $record->debit : $record->credit;
                            $component->state($amount);
                        }
                    })
                    ->dehydrated(false), // Don't save this field directly
                    
                // Hidden fields for actual debit/credit values
                Forms\Components\Hidden::make('debit')
                    ->dehydrateStateUsing(function ($state, callable $get) {
                        $direction = $get('transaction_direction');
                        $amount = $get('amount');
                        return ($direction === 'keluar') ? floatval($amount) : 0;
                    }),
                    
                Forms\Components\Hidden::make('credit')
                    ->dehydrateStateUsing(function ($state, callable $get) {
                        $direction = $get('transaction_direction');
                        $amount = $get('amount');
                        return ($direction === 'masuk') ? floatval($amount) : 0;
                    }),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make('row_number')
                    ->label('No')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('description')
                    ->label('Keterangan')
                    ->searchable()
                    ->limit(50)
                    ->wrap()
                    ->formatStateUsing(function (string $state): string {
                        // Clean excessive whitespace for table display
                        return preg_replace('/\s+/', ' ', trim($state));
                    }),
                    
                // Optional: Show original debit/credit for technical users
                Tables\Columns\TextColumn::make('debit')
                    ->label('Debit (Bank)')
                    ->money('IDR')
                    ->alignEnd()
                    ->color('danger')
                    ->sortable()
                    // ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip('Format standar bank: Debit = Uang Keluar'),
                    
                Tables\Columns\TextColumn::make('credit')
                    ->label('Credit (Bank)')
                    ->money('IDR')
                    ->alignEnd()
                    ->color('success')
                    ->sortable()
                    // ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip('Format standar bank: Credit = Uang Masuk'),
            ])
            ->filters([
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('date_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['date_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Item')
                    ->visible(function (): bool {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        return $user && $user->hasRole('super_admin');
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(function (): bool {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        return $user && $user->hasRole('super_admin');
                    }),
                Tables\Actions\DeleteAction::make()
                    ->visible(function (): bool {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        return $user && $user->hasRole('super_admin');
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(function (): bool {
                            /** @var \App\Models\User $user */
                            $user = Auth::user();
                            return $user && $user->hasRole('super_admin');
                        }),
                ])->visible(function (): bool {
                    /** @var \App\Models\User $user */
                    $user = Auth::user();
                    return $user && $user->hasRole('super_admin');
                }),
            ])
            ->defaultSort('row_number')
            ->emptyStateHeading('Belum ada data rekonsiliasi')
            ->emptyStateDescription('Upload file Excel atau tambah item rekonsiliasi secara manual.')
            ->emptyStateIcon('heroicon-o-document-text');
    }
}
