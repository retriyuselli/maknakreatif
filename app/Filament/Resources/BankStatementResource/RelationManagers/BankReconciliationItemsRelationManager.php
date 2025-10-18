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
                    ->extraInputAttributes([
                        'style' => 'font-family: monospace;'
                    ])
                    ->dehydrateStateUsing(function (?string $state): ?string {
                        if (!$state) return $state;
                        
                        // Clean up excessive whitespace but preserve intentional line breaks
                        $cleaned = preg_replace('/[ \t]+/', ' ', $state); // Replace multiple spaces/tabs with single space
                        $cleaned = preg_replace('/\n\s*\n/', "\n", $cleaned); // Remove empty lines
                        return trim($cleaned);
                    }),
                    
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
                    ->wrap()
                    ->formatStateUsing(function (string $state): string {
                        return $this->formatDescription($state);
                    })
                    ->extraAttributes([
                        'class' => 'fi-ta-text-item-description',
                        'style' => 'white-space: pre-line; word-break: break-word; font-family: ui-monospace, SFMono-Regular, "SF Mono", Consolas, "Liberation Mono", Menlo, monospace; font-size: 0.875rem; line-height: 1.4;'
                    ]),
                    
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

    /**
     * Format description text by cleaning excessive whitespace and organizing content
     */
    private function formatDescription(string $description): string
    {
        // Remove excessive whitespace first
        $cleaned = preg_replace('/\s+/', ' ', trim($description));
        
        // Try to identify patterns and format accordingly
        // Pattern 1: "NUMBER NAME DETAILS" format
        if (preg_match('/^(\d+)\s+([A-Z\s]+?)\s+([A-Z0-9\/\s]+)$/i', $cleaned, $matches)) {
            $number = trim($matches[1]);
            $name = trim($matches[2]);
            $details = trim($matches[3]);
            
            return "{$number} {$name}\n{$details}";
        }
        
        // Pattern 2: Long transaction codes or references at the end
        if (preg_match('/^(.+?)\s+([A-Z0-9]{15,})$/i', $cleaned, $matches)) {
            $mainText = trim($matches[1]);
            $code = trim($matches[2]);
            
            return "{$mainText}\n{$code}";
        }
        
        // Default: just clean excessive whitespace
        return $cleaned;
    }
}
