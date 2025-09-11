<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use App\Filament\Resources\EmployeeResource\RelationManagers\OrdersRelationManager;
use App\Models\Employee;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Support\RawJs;
use Filament\Support\Enums\FontWeight;
use App\Filament\Resources\EmployeeResource\Widgets;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationGroup = 'Master';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationLabel = 'Employees';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Employee Information')
                    ->tabs([
                        Tabs\Tab::make('Basic Information')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Section::make('Personal Details')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('name')
                                                    ->required()
                                                    ->placeholder('Full name (first and last name)')
                                                    ->maxLength(255)
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                        $set('slug', Str::slug($state));
                                                    }),
                                                
                                                Forms\Components\TextInput::make('slug')
                                                    ->disabled()
                                                    ->dehydrated()
                                                    ->maxLength(255),
                                
                                                Forms\Components\DatePicker::make('date_of_birth')
                                                    ->label('Date of Birth')
                                                    ->required()
                                                    ->maxDate(now()->subYears(18))
                                                    ->displayFormat('d M Y'),
                                
                                                Forms\Components\FileUpload::make('photo')
                                                    ->label('Profile Photo')
                                                    ->image()
                                                    ->openable()
                                                    ->downloadable()
                                                    ->directory('employee-photos')
                                                    ->imageCropAspectRatio('1:1')
                                                    ->imageResizeMode('cover'),
                                            ]),
                                    ]),
                                
                                Section::make('Contact Information')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('email')
                                                    ->email()
                                                    ->required()
                                                    ->unique(ignoreRecord: true)
                                                    ->maxLength(255),
                                
                                                Forms\Components\TextInput::make('phone')
                                                    ->tel()
                                                    ->required()
                                                    ->maxLength(20)
                                                    ->prefix('+62')
                                                    ->telRegex('/^[0-9]{9,15}$/')
                                                    ->placeholder('8xxxxxxxxx'),
                                
                                                Forms\Components\TextInput::make('instagram')
                                                    ->prefix('@')
                                                    ->maxLength(255),
                                
                                                Forms\Components\Textarea::make('address')
                                                    ->required()
                                                    ->rows(2)
                                                    ->columnSpanFull(),
                                            ]),
                                    ]),
                            ]),
                            
                        Tabs\Tab::make('Employment Details')
                            ->icon('heroicon-o-briefcase')
                            ->schema([
                                Section::make('Position & Role')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Forms\Components\Select::make('position')
                                                    ->required()
                                                    ->options([
                                                        'Account Manager' => 'Account Manager',
                                                        'Event Manager' => 'Event Manager',
                                                        'Crew' => 'Crew',
                                                        'Finance' => 'Finance',
                                                        'Founder' => 'Founder',
                                                        'Co Founder' => 'Co Founder',
                                                        'Other' => 'Other',
                                                    ])
                                                    ->searchable(),
                                
                                                Forms\Components\Select::make('user_id')
                                                    ->relationship('user', 'name')
                                                    ->label('Associated User Account')
                                                    ->preload()
                                                    ->searchable()
                                                    ->createOptionForm([
                                                        Forms\Components\TextInput::make('name')
                                                            ->required(),
                                                        Forms\Components\TextInput::make('email')
                                                            ->required()
                                                            ->email(),
                                                        Forms\Components\TextInput::make('password')
                                                            ->password()
                                                            ->required()
                                                            ->confirmed(),
                                                        Forms\Components\TextInput::make('password_confirmation')
                                                            ->password()
                                                            ->required(),
                                                    ]),
                                
                                                Forms\Components\DatePicker::make('date_of_join')
                                                    ->label('Joining Date')
                                                    ->required()
                                                    ->displayFormat('d M Y')
                                                    ->default(now()),
                                
                                                Forms\Components\DatePicker::make('date_of_out')
                                                    ->label('Last Working Date')
                                                    ->displayFormat('d M Y')
                                                    ->minDate(fn (Forms\Get $get) => $get('date_of_join')),
                                            ]),
                                    ]),
                                
                                Section::make('Compensation & Banking')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('salary')
                                                    ->numeric()
                                                    ->required()
                                                    ->prefix('Rp')
                                                    ->mask(RawJs::make('$money($input)'))
                                                    ->stripCharacters(','),
                                
                                                Forms\Components\TextInput::make('bank_name')
                                                    ->required()
                                                    ->maxLength(255),
                                
                                                Forms\Components\TextInput::make('no_rek')
                                                    ->label('Account Number')
                                                    ->required()
                                                    ->numeric()
                                                    ->minLength(10)
                                                    ->maxLength(20),
                                            ]),
                                    ]),
                            ]),
                            
                        Tabs\Tab::make('Documents & Notes')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Grid::make(1)
                                    ->schema([
                                        Forms\Components\FileUpload::make('kontrak')
                                            ->label('Employment Contract')
                                            ->directory('employee-contracts')
                                            ->acceptedFileTypes(['application/pdf'])
                                            ->openable()
                                            ->downloadable(),
                                
                                        Forms\Components\Textarea::make('note')
                                            ->label('Additional Notes')
                                            ->placeholder('Add any special considerations or notes about this employee')
                                            ->rows(3),
                                
                                        // Ubah semua bagian yang menggunakan record secara langsung saat create
                                        Forms\Components\Placeholder::make('created_at')
                                            ->label('Created At')
                                            ->content(fn (?Employee $record): ?string => 
                                                $record?->created_at?->diffForHumans())
                                            ->hidden(fn (?Employee $record) => $record === null),

                                        Forms\Components\Placeholder::make('updated_at')
                                            ->label('Last Updated')
                                            ->content(fn (?Employee $record): ?string => 
                                                $record?->updated_at?->diffForHumans())
                                            ->hidden(fn (?Employee $record) => $record === null),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Basic column untuk foto profil
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Photo')
                    ->circular()
                    ->defaultImageUrl(fn (Employee $record) => 
                        $record->name ? "https://ui-avatars.com/api/?name=" . urlencode($record->name) . "&color=FFFFFF&background=6366F1" : null),
                
                // Column nama yang sederhana tanpa manipulasi data kompleks
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->description(fn (Employee $record): string => 
                        $record->position ?? ''),
                
                // // Kolom email dan telepon sebagai kolom terpisah
                // Tables\Columns\TextColumn::make('email')
                //     ->label('Email')
                //     ->searchable()
                //     ->sortable()
                //     ->description(fn (Employee $record): string => 
                //         $record->phone ?? '')
                //     ->icon('heroicon-m-envelope')
                //     ->wrap(),
                    
                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->prefix('+62')
                    ->description(fn (Employee $record): string => 
                        $record->email ?? '')
                    ->searchable(),
                
                // Date columns yang lebih aman
                Tables\Columns\TextColumn::make('date_of_join')
                    ->label('Join Date')
                    ->date('d M Y')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('date_of_out')
                    ->label('End Date')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('Active'),
                
                // Status sebagai boolean sederhana
                Tables\Columns\IconColumn::make('active_status')
                    ->label('Status')
                    ->boolean()
                    ->getStateUsing(function ($record) {
                        // Employee aktif jika sudah join dan belum out atau tanggal out di masa depan
                        if (empty($record->date_of_join)) {
                            return false;
                        }
                        
                        $joinDate = $record->date_of_join instanceof \Carbon\Carbon 
                            ? $record->date_of_join 
                            : \Carbon\Carbon::parse($record->date_of_join);
                            
                        // Jika belum join
                        if ($joinDate->isFuture()) {
                            return false;
                        }
                        
                        // Jika tidak ada tanggal keluar
                        if (empty($record->date_of_out)) {
                            return true;
                        }
                        
                        $outDate = $record->date_of_out instanceof \Carbon\Carbon
                            ? $record->date_of_out
                            : \Carbon\Carbon::parse($record->date_of_out);
                            
                        // Aktif jika tanggal keluar masih di masa depan
                        return $outDate->isFuture();
                    })
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                
                // Finansial
                Tables\Columns\TextColumn::make('salary')
                    ->label('Salary')
                    ->money('IDR')
                    ->sortable(),
                    
                // Data bank sebagai kolom terpisah
                Tables\Columns\TextColumn::make('bank_name')
                    ->label('Bank')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('no_rek')
                    ->label('Account Number')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                // Timestamps
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filter posisi
                Tables\Filters\SelectFilter::make('position')
                    ->options([
                        'Account Manager' => 'Account Manager',
                        'Event Manager' => 'Event Manager',
                        'Crew' => 'Crew',
                        'Finance' => 'Finance',
                        'Founder' => 'Founder',
                        'Co Founder' => 'Co Founder',
                        'Other' => 'Other',
                    ])
                    ->multiple(),
                
                // Filter status aktif/nonaktif
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Employment Status')
                    ->placeholder('All Employees')
                    ->trueLabel('Active Employees')
                    ->falseLabel('Former Employees')
                    ->queries(
                        true: fn (Builder $query) => $query->where(function ($query) {
                            $query->where('date_of_join', '<=', now())
                                ->where(function ($query) {
                                    $query->whereNull('date_of_out')
                                        ->orWhere('date_of_out', '>=', now());
                                });
                        }),
                        false: fn (Builder $query) => $query->where('date_of_out', '<', now()),
                        blank: fn (Builder $query) => $query
                    ),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date_of_join', 'desc')
            ->striped()
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10, 25, 50]);
    }

    public static function getRelations(): array
    {
        return [
            OrdersRelationManager::class,
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::query()
            ->where('date_of_join', '<=', now())
            ->where(function (Builder $query) {
                $query->whereNull('date_of_out')
                    ->orWhere('date_of_out', '>=', now());
            })
            ->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getWidgets(): array
    {
        return [
            Widgets\EmployeeOverviewWidget::class,
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'phone', 'position'];
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Karyawan aktif';
    }
}
