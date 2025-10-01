<?php

namespace App\Filament\Resources\JournalBatchResource\RelationManagers;

use App\Models\ChartOfAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\RawJs;

class JournalEntriesRelationManager extends RelationManager
{
    protected static string $relationship = 'journalEntries';
    protected static ?string $title = 'Entri Jurnal';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('account_id')
                            ->label('Akun')
                            ->relationship('chartOfAccount', 'account_name')
                            ->getOptionLabelFromRecordUsing(fn (ChartOfAccount $record): string => 
                                "{$record->account_code} - {$record->account_name}")
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $account = ChartOfAccount::find($state);
                                    if ($account && in_array($account->normal_balance, ['DEBIT'])) {
                                        // For debit normal accounts, default to debit entry
                                        $set('entry_type', 'debit');
                                    } elseif ($account && in_array($account->normal_balance, ['KREDIT'])) {
                                        // For credit normal accounts, default to credit entry
                                        $set('entry_type', 'credit');
                                    }
                                }
                            }),

                        Forms\Components\Select::make('entry_type')
                            ->label('Jenis Entry')
                            ->options([
                                'debit' => 'Debit',
                                'credit' => 'Kredit',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                // Reset amount when switching between debit/credit
                                if ($state === 'debit') {
                                    $set('credit_amount', 0);
                                } else {
                                    $set('debit_amount', 0);
                                }
                            }),
                    ]),

                Forms\Components\Textarea::make('description')
                    ->label('Keterangan')
                    ->required()
                    ->rows(2)
                    ->columnSpanFull(),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('debit_amount')
                            ->label('Jumlah Debit')
                            ->numeric()
                            ->prefix('IDR')
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->default(0)
                            ->live()
                            ->disabled(fn (callable $get) => $get('entry_type') === 'credit'),

                        Forms\Components\TextInput::make('credit_amount')
                            ->label('Jumlah Kredit')
                            ->numeric()
                            ->prefix('IDR')
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->default(0)
                            ->live()
                            ->disabled(fn (callable $get) => $get('entry_type') === 'debit'),
                    ]),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('reference_type')
                            ->label('Jenis Referensi')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('reference_id')
                            ->label('ID Referensi')
                            ->numeric(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make('chartOfAccount.account_code')
                    ->label('Kode Akun')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('chartOfAccount.account_name')
                    ->label('Nama Akun')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Keterangan')
                    ->searchable()
                    ->limit(30)
                    ->wrap(),

                Tables\Columns\TextColumn::make('debit_amount')
                    ->label('Debit')
                    ->money('IDR')
                    ->alignEnd()
                    ->color('success')
                    ->getStateUsing(fn ($record) => $record->debit_amount > 0 ? $record->debit_amount : null),

                Tables\Columns\TextColumn::make('credit_amount')
                    ->label('Kredit')
                    ->money('IDR')
                    ->alignEnd()
                    ->color('info')
                    ->getStateUsing(fn ($record) => $record->credit_amount > 0 ? $record->credit_amount : null),

                Tables\Columns\TextColumn::make('reference_type')
                    ->label('Referensi')
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('account_id')
                    ->label('Akun')
                    ->relationship('chartOfAccount', 'account_name')
                    ->getOptionLabelFromRecordUsing(fn (ChartOfAccount $record): string => 
                        "{$record->account_code} - {$record->account_name}"),
            ])
            ->headerActions([
                Tables\Actions\Action::make('show_totals')
                    ->label('Total & Saldo')
                    ->icon('heroicon-o-calculator')
                    ->color('info')
                    ->action(function () {
                        $this->ownerRecord->calculateTotals();
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Total Jurnal')
                            ->body(sprintf(
                                "Total Debit: Rp %s\nTotal Kredit: Rp %s\nStatus: %s",
                                number_format($this->ownerRecord->total_debit, 0, ',', '.'),
                                number_format($this->ownerRecord->total_credit, 0, ',', '.'),
                                $this->ownerRecord->isBalanced() ? 'SEIMBANG ✅' : 'TIDAK SEIMBANG ❌'
                            ))
                            ->color($this->ownerRecord->isBalanced() ? 'success' : 'warning')
                            ->duration(5000)
                            ->send();
                    }),
                    
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Entry')
                    ->mutateFormDataUsing(function (array $data): array {
                        // Set the other amount to 0 based on entry type
                        if (isset($data['entry_type'])) {
                            if ($data['entry_type'] === 'debit') {
                                $data['debit_amount'] = $data['debit_amount'] ?? 0;
                                $data['credit_amount'] = 0;
                            } else {
                                $data['credit_amount'] = $data['credit_amount'] ?? 0;
                                $data['debit_amount'] = 0;
                            }
                            unset($data['entry_type']); // Remove helper field
                        }
                        
                        // Set transaction date from parent batch
                        $data['transaction_date'] = $this->ownerRecord->transaction_date;
                        $data['created_by'] = 1; // Default user for now
                        
                        return $data;
                    })
                    ->after(function () {
                        // Recalculate batch totals after adding entry
                        $this->ownerRecord->calculateTotals();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->fillForm(function ($record): array {
                        $data = $record->toArray();
                        // Set entry_type based on current amounts for editing
                        $data['entry_type'] = $record->debit_amount > 0 ? 'debit' : 'credit';
                        return $data;
                    })
                    ->mutateFormDataUsing(function (array $data): array {
                        // Set the other amount to 0 based on entry type
                        if (isset($data['entry_type'])) {
                            if ($data['entry_type'] === 'debit') {
                                $data['debit_amount'] = $data['debit_amount'] ?? 0;
                                $data['credit_amount'] = 0;
                            } else {
                                $data['credit_amount'] = $data['credit_amount'] ?? 0;
                                $data['debit_amount'] = 0;
                            }
                            unset($data['entry_type']); // Remove helper field
                        }
                        return $data;
                    })
                    ->after(function () {
                        // Recalculate batch totals after editing
                        $this->ownerRecord->calculateTotals();
                    }),
                    
                Tables\Actions\DeleteAction::make()
                    ->after(function () {
                        // Recalculate batch totals after deleting
                        $this->ownerRecord->calculateTotals();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->after(function () {
                            // Recalculate batch totals after bulk delete
                            $this->ownerRecord->calculateTotals();
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Entry Pertama')
                    ->icon('heroicon-o-plus'),
            ]);
    }
}
