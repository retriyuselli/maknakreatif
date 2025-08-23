<?php

namespace App\Filament\Resources;
use App\Filament\Resources\UserResource\Widgets\AccountManagerStats;
use App\Filament\Resources\UserResource\Widgets\UserExpirationWidget;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\Widgets\UserStatsOverview;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationGroup = 'Master';

    /**
     * Check if current user is super admin
     */
    public static function isSuperAdmin(): bool
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user) return false;
        
        return $user->hasRole('super_admin');
    }

    /**
     * Check if target user is super admin
     */
    public static function isTargetUserSuperAdmin($record): bool
    {
        if (!$record) return false;
        
        return $record->hasRole('super_admin');
    }

    /**
     * Apply query restrictions based on user role
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // If current user is not super_admin, hide super_admin users from the list
        if (!static::isSuperAdmin()) {
            $query->whereDoesntHave('roles', function (Builder $query) {
                $query->where('name', 'super_admin');
            });
        }
        
        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->relationship('status', 'status_name')
                    ->required()
                    ->preload()
                    ->searchable(),
                Forms\Components\FileUpload::make('avatar_url')
                    ->image()
                    ->directory('avatars')
                    ->required(),
                Forms\Components\DateTimePicker::make('expire_date')
                    ->label('Tanggal Kedaluwarsa')
                    ->helperText('Kosongkan jika user tidak memiliki batas waktu')
                    ->displayFormat('d/m/Y H:i')
                    ->seconds(false),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('SKU/ID'),
                Tables\Columns\ImageColumn::make('avatar_url')
                    ->defaultImageUrl(function ($record) {
                        // Generate default avatar based on user's name initials
                        $name = $record->name ?? 'User';
                        $initials = collect(explode(' ', $name))
                            ->map(fn($word) => strtoupper(substr($word, 0, 1)))
                            ->take(2)
                            ->implode('');
                        
                        // Use UI Avatars service to generate default avatar
                        return "https://ui-avatars.com/api/?name={$initials}&background=3b82f6&color=ffffff&size=128";
                    })
                    ->circular()
                    ->size(40),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('expire_date')
                    ->label('Tanggal Kedaluwarsa')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Tidak ada batas')
                    ->sortable()
                    ->color(function ($record) {
                        if (!$record->expire_date) return 'gray';
                        if (method_exists($record, 'isExpired') && $record->isExpired()) return 'danger';
                        if (method_exists($record, 'isExpiringSoon') && $record->isExpiringSoon()) return 'warning';
                        return 'success';
                    })
                    ->badge(function ($record) {
                        if (!$record->expire_date) return false;
                        return (method_exists($record, 'isExpired') && $record->isExpired()) || 
                               (method_exists($record, 'isExpiringSoon') && $record->isExpiringSoon());
                    })
                    ->formatStateUsing(function ($state, $record) {
                        if (!$state) return 'Tidak ada batas';
                        if (method_exists($record, 'isExpired') && $record->isExpired()) return $state . ' (Kedaluwarsa)';
                        if (method_exists($record, 'isExpiringSoon') && $record->isExpiringSoon()) {
                            $days = method_exists($record, 'getDaysUntilExpiration') ? $record->getDaysUntilExpiration() : 0;
                            return $state . " ($days hari lagi)";
                        }
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('status.status_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('closing')
                    ->numeric(),
                Tables\Columns\TextColumn::make('firstEmployee.salary')
                    ->money('IDR')
                    ->sortable()
                    ->label('Salary')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('firstEmployee.date_of_birth')
                    ->date()
                    ->sortable()
                    ->label('Date of Birth')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('firstEmployee.date_of_join')
                    ->date()
                    ->sortable()
                    ->label('Join Date')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('firstEmployee.date_of_out')
                    ->date()
                    ->sortable()
                    ->label('Out Date')
                    ->placeholder('Active') // Tampil jika null
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()
                        ->visible(function ($record) {
                            // Super admin can edit anyone
                            if (static::isSuperAdmin()) {
                                return true;
                            }
                            // Non-super admin cannot edit super admin users
                            return !static::isTargetUserSuperAdmin($record);
                        }),
                    Tables\Actions\DeleteAction::make()
                        ->visible(function ($record) {
                            // Super admin can delete anyone
                            if (static::isSuperAdmin()) {
                                return true;
                            }
                            // Non-super admin cannot delete super admin users
                            return !static::isTargetUserSuperAdmin($record);
                        }),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function ($records) {
                            // Filter out super admin users if current user is not super admin
                            if (!static::isSuperAdmin()) {
                                $records = $records->filter(function ($record) {
                                    return !static::isTargetUserSuperAdmin($record);
                                });
                            }
                            
                            // Delete the filtered records
                            $records->each->delete();
                        })
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

    public static function getWidgets(): array
    {
        return [
            AccountManagerStats::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
