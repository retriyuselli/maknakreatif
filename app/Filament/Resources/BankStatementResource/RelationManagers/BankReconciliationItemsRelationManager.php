<?php

namespace App\Filament\Resources\BankStatementResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BankReconciliationItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'bankReconciliationItems';

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
                    
                Forms\Components\TextInput::make('description')
                    ->label('Keterangan')
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\TextInput::make('debit')
                    ->label('Debit')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
                    
                Forms\Components\TextInput::make('credit')
                    ->label('Credit')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
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
                    ->wrap(),
                    
                Tables\Columns\TextColumn::make('debit')
                    ->label('Debit')
                    ->money('IDR')
                    ->alignEnd()
                    ->color('danger')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('credit')
                    ->label('Credit')
                    ->money('IDR')
                    ->alignEnd()
                    ->color('success')
                    ->sortable(),
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
                    ->label('Tambah Item'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('row_number')
            ->emptyStateHeading('Belum ada data rekonsiliasi')
            ->emptyStateDescription('Upload file Excel atau tambah item rekonsiliasi secara manual.')
            ->emptyStateIcon('heroicon-o-document-text');
    }
}
