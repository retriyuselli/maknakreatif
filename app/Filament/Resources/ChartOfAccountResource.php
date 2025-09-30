<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChartOfAccountResource\Pages;
use App\Filament\Resources\ChartOfAccountResource\RelationManagers;
use App\Models\ChartOfAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Filament\Support\Enums\FontWeight;

class ChartOfAccountResource extends Resource
{
    protected static ?string $model = ChartOfAccount::class;
    protected static ?string $navigationIcon = 'heroicon-o-folder-open';
    protected static ?string $navigationLabel = 'Bagan Akun';
    protected static ?string $navigationGroup = 'Akuntansi';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Account Information')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('account_code')
                                    ->label('Account Code')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(20)
                                    ->placeholder('e.g., 110000000')
                                    ->helperText('Unique account code'),

                                Forms\Components\Select::make('account_type')
                                    ->label('Account Type')
                                    ->options(ChartOfAccount::ACCOUNT_TYPES)
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $normalBalance = ChartOfAccount::NORMAL_BALANCE[$state] ?? 'debit';
                                            $set('normal_balance', $normalBalance);
                                        }
                                    }),
                            ]),

                        Forms\Components\TextInput::make('account_name')
                            ->label('Account Name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('parent_id')
                                    ->label('Parent Account')
                                    ->relationship(
                                        'parent',
                                        'account_name',
                                        fn (Builder $query) => $query->where('level', '<', 3)
                                    )
                                    ->getOptionLabelFromRecordUsing(fn (ChartOfAccount $record): string => 
                                        "{$record->account_code} - {$record->account_name}")
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),

                                Forms\Components\TextInput::make('level')
                                    ->label('Level')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->maxValue(5),

                                Forms\Components\Select::make('normal_balance')
                                    ->label('Normal Balance')
                                    ->options([
                                        'debit' => 'Debit',
                                        'credit' => 'Credit',
                                    ])
                                    ->required(),
                            ]),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('account_code')
            ->columns([
                Tables\Columns\TextColumn::make('account_code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                Tables\Columns\TextColumn::make('account_name')
                    ->label('Account Name')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($record) {
                        $prefix = str_repeat('└── ', $record->level - 1);
                        return $prefix . $record->account_name;
                    }),

                Tables\Columns\TextColumn::make('account_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ChartOfAccount::ACCOUNT_TYPES[$state] ?? $state)
                    ->color(fn ($state) => match($state) {
                        'HARTA' => 'success',
                        'KEWAJIBAN' => 'warning',
                        'MODAL' => 'info',
                        'PENDAPATAN' => 'success',
                        'BEBAN_ATAS_PENDAPATAN' => 'danger',
                        'BEBAN_OPERASIONAL' => 'danger',
                        'PENDAPATAN_LAIN' => 'success',
                        'BEBAN_LAIN' => 'danger',
                        default => 'gray'
                    }),

                Tables\Columns\TextColumn::make('parent.account_name')
                    ->label('Parent')
                    ->placeholder('Main Account'),

                Tables\Columns\TextColumn::make('level')
                    ->label('Level')
                    ->badge(),

                Tables\Columns\TextColumn::make('normal_balance')
                    ->label('Normal Balance')
                    ->badge()
                    ->color(fn ($state) => $state === 'debit' ? 'success' : 'warning'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Deleted At')
                    ->dateTime()
                    ->placeholder('Not deleted')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('dependencies')
                    ->label('Dependencies')
                    ->getStateUsing(function (ChartOfAccount $record): string {
                        $journalCount = DB::table('journal_entries')->where('account_id', $record->id)->count();
                        $childrenCount = ChartOfAccount::where('parent_id', $record->id)->count();
                        
                        $dependencies = [];
                        if ($journalCount > 0) {
                            $dependencies[] = "{$journalCount} journals";
                        }
                        if ($childrenCount > 0) {
                            $dependencies[] = "{$childrenCount} children";
                        }
                        
                        return empty($dependencies) ? '-' : implode(', ', $dependencies);
                    })
                    ->badge()
                    ->color(function (string $state): string {
                        return $state === '-' ? 'success' : 'warning';
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('account_type')
                    ->label('Account Type')
                    ->options(ChartOfAccount::ACCOUNT_TYPES),

                Tables\Filters\SelectFilter::make('level')
                    ->label('Level')
                    ->options([
                        1 => 'Level 1 (Main)',
                        2 => 'Level 2 (Sub)',
                        3 => 'Level 3 (Detail)',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All accounts')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                TrashedFilter::make()
                    ->label('Deleted Status')
                    ->placeholder('Active accounts only')
                    ->trueLabel('With deleted accounts')
                    ->falseLabel('Deleted accounts only'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete Account')
                    ->modalDescription('This will move the account to trash. If this account has journal entries or child accounts, you can still restore it later.')
                    ->modalSubmitActionLabel('Move to Trash'),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make()
                    ->before(function (Tables\Actions\ForceDeleteAction $action, ChartOfAccount $record) {
                        // Check if account has related journal entries
                        $journalEntriesCount = DB::table('journal_entries')
                            ->where('account_id', $record->id)
                            ->count();
                        
                        if ($journalEntriesCount > 0) {
                            \Filament\Notifications\Notification::make()
                                ->title('Cannot Force Delete Account')
                                ->body("This account has {$journalEntriesCount} journal entries. Please delete or reassign them first.")
                                ->danger()
                                ->send();
                            
                            $action->cancel();
                        }
                        
                        // Check if account has child accounts
                        $childrenCount = ChartOfAccount::where('parent_id', $record->id)->count();
                        
                        if ($childrenCount > 0) {
                            \Filament\Notifications\Notification::make()
                                ->title('Cannot Force Delete Account')
                                ->body("This account has {$childrenCount} child accounts. Please delete or reassign them first.")
                                ->danger()
                                ->send();
                            
                            $action->cancel();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Force Delete Account')
                    ->modalDescription('Are you sure you want to permanently delete this account? This action cannot be undone.')
                    ->modalSubmitActionLabel('Yes, delete permanently'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->before(function (Tables\Actions\ForceDeleteBulkAction $action, \Illuminate\Database\Eloquent\Collection $records) {
                            $blockedAccounts = [];
                            
                            foreach ($records as $record) {
                                // Check journal entries
                                $journalEntriesCount = DB::table('journal_entries')
                                    ->where('account_id', $record->id)
                                    ->count();
                                
                                // Check child accounts
                                $childrenCount = ChartOfAccount::where('parent_id', $record->id)->count();
                                
                                if ($journalEntriesCount > 0 || $childrenCount > 0) {
                                    $blockedAccounts[] = [
                                        'name' => $record->account_name,
                                        'code' => $record->account_code,
                                        'journal_entries' => $journalEntriesCount,
                                        'children' => $childrenCount
                                    ];
                                }
                            }
                            
                            if (!empty($blockedAccounts)) {
                                $message = "Cannot force delete the following accounts:\n\n";
                                foreach ($blockedAccounts as $account) {
                                    $reasons = [];
                                    if ($account['journal_entries'] > 0) {
                                        $reasons[] = "{$account['journal_entries']} journal entries";
                                    }
                                    if ($account['children'] > 0) {
                                        $reasons[] = "{$account['children']} child accounts";
                                    }
                                    $message .= "• {$account['code']} - {$account['name']} (" . implode(', ', $reasons) . ")\n";
                                }
                                $message .= "\nPlease delete or reassign related records first.";
                                
                                \Filament\Notifications\Notification::make()
                                    ->title('Cannot Force Delete Accounts')
                                    ->body($message)
                                    ->danger()
                                    ->send();
                                
                                $action->cancel();
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Force Delete Accounts')
                        ->modalDescription('Are you sure you want to permanently delete these accounts? This action cannot be undone.')
                        ->modalSubmitActionLabel('Yes, delete permanently'),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
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
            'index' => Pages\ListChartOfAccounts::route('/'),
            'create' => Pages\CreateChartOfAccount::route('/create'),
            'edit' => Pages\EditChartOfAccount::route('/{record}/edit'),
        ];
    }
}
