<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VendorResource\Pages;
use App\Models\Category;
use App\Models\Vendor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\RawJs;
use Illuminate\Support\Str;
use Filament\Support\Enums\FontWeight;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationGroup = 'Shop';
    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Vendor Management')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Basic Information')
                            ->icon('heroicon-m-information-circle')
                            ->schema([
                                Forms\Components\Section::make('Vendor Identity')
                                    ->description('Basic information about the vendor')
                                    ->schema([
                                        Forms\Components\Grid::make()
                                            ->columns(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('name')
                                                    ->required()
                                                    ->unique(ignoreRecord: true)
                                                    ->live(debounce: 500)
                                                    ->afterStateUpdated(function ($state, Forms\Set $set, ?Vendor $record) {
                                                        // Add null check for $state
                                                        if ($state === null) {
                                                            $set('slug', '');
                                                            return;
                                                        }
                                                        
                                                        $slug = Str::slug($state);
                                                        
                                                        // Check if slug exists
                                                        $exists = Vendor::where('slug', $slug)
                                                            ->when($record, fn ($query) => $query->where('id', '!=', $record->id))
                                                            ->exists();
                                                            
                                                        // If exists, append timestamp
                                                        if ($exists) {
                                                            $slug = $slug . '-' . now()->timestamp;
                                                        }
                                                        
                                                        $set('slug', $slug);
                                                    })
                                                    ->minLength(3)
                                                    ->maxLength(255)
                                                    ->placeholder('nama vendor / nama pengantin_lokasi_pax'),

                                                Forms\Components\TextInput::make('slug')
                                                    ->disabled()
                                                    ->dehydrated()
                                                    ->required()
                                                    ->unique(ignoreRecord: true),

                                                Forms\Components\Select::make('category_id')
                                                    ->relationship('category', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->createOptionForm([
                                                        Forms\Components\TextInput::make('name')
                                                            ->required()
                                                            ->maxLength(255)
                                                            ->live(debounce: 500)
                                                            ->afterStateUpdated(fn ($state, Forms\Set $set) => 
                                                            // Add null check here too
                                                            $set('slug', $state ? Str::slug($state) : '')
                                                        ),
                                                        Forms\Components\TextInput::make('slug')
                                                            ->disabled()
                                                            ->dehydrated()
                                                            ->unique(Category::class, 'slug', ignoreRecord: true),
                                                        Forms\Components\Toggle::make('is_active')
                                                            ->required(),
                                                    ]),

                                                Forms\Components\Select::make('status')
                                                    ->options([
                                                        'vendor' => 'Vendor',
                                                        'product' => 'Product',
                                                    ])
                                                    ->required(),
                                                
                                                Forms\Components\TextInput::make('pic_name')
                                                    ->label('PIC Name')
                                                    ->required(),

                                                Forms\Components\TextInput::make('phone')
                                                    ->tel()
                                                    ->required()
                                                    ->prefix('+62')
                                                    // Ubah regex menjadi lebih fleksibel
                                                    // ->regex('/^[0-9]{8,15}$/') // Lebih fleksibel dari 9-15 menjadi 8-15 digit
                                                    ->placeholder('812XXXXXXXX')
                                                    ->helperText('Enter number without leading zero'),

                                                Forms\Components\TextInput::make('address')
                                                    ->required(),
                                            ]),
                                    ])
                                    ->collapsible(),

                                Forms\Components\Section::make('Business Details')
                                    ->description('Additional business information')
                                    ->schema([
                                        Forms\Components\Grid::make()
                                            ->columns(2)
                                            ->schema([
                                                Forms\Components\RichEditor::make('description')
                                                    ->columnSpanFull()
                                                    ->minLength(10)
                                                    ->required()
                                                    ->label('Description')
                                                    ->maxLength(20000),
                                            ]),
                                    ])
                                    ->collapsible(),
                            ]),

                        Forms\Components\Tabs\Tab::make('Financial Information')
                            ->icon('heroicon-m-currency-dollar')
                            ->schema([
                                Forms\Components\Section::make('Pricing')
                                    ->description('Manage vendor pricing and profit calculations')
                                    ->schema([
                                        Forms\Components\Grid::make()
                                            ->columns(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('harga_publish')
                                                    ->label('Published Price')
                                                    ->numeric()
                                                    ->required()
                                                    ->prefix('Rp')
                                                    ->mask(RawJs::make('$money($input)'))
                                                    ->stripCharacters(',')
                                                    ->live(onBlur: true)
                                                    ->default(0)
                                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                                        static::calculateProfitMetrics($set, $get);
                                                    }),

                                                Forms\Components\TextInput::make('harga_vendor')
                                                    ->label('Vendor Price')
                                                    ->numeric()
                                                    ->required()
                                                    ->default(0)
                                                    ->prefix('Rp')
                                                    ->mask(RawJs::make('$money($input)'))
                                                    ->stripCharacters(',')
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                                        static::calculateProfitMetrics($set, $get);
                                                    })
                                                    ->rules(['min:0']),
                                                    
                                                Forms\Components\TextInput::make('profit_margin')
                                                    ->label('Profit Margin')
                                                    ->numeric()
                                                    ->suffix('%')
                                                    ->disabled()
                                                    ->dehydrated(false),
                                                
                                                Forms\Components\TextInput::make('profit_amount')
                                                    ->label('Profit Amount')
                                                    ->prefix('Rp. ')
                                                    ->mask(RawJs::make('$money($input)'))
                                                    ->stripCharacters(',')
                                                    ->numeric()
                                                    ->disabled()
                                                    ->dehydrated(false),
                                            ]),
                                    ])
                                    ->collapsible(),

                                Forms\Components\Section::make('Banking Information')
                                    ->description('Vendor banking details')
                                    ->schema([
                                        Forms\Components\Grid::make()
                                            ->columns(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('bank_name')
                                                    ->label('Bank Name')
                                                    ->prefix('Bank '),

                                                Forms\Components\TextInput::make('bank_account')
                                                    ->label('Account Number')
                                                    ->numeric(),

                                                Forms\Components\TextInput::make('account_holder')
                                                    ->label('Account Holder Name')
                                                    ->helperText('Enter name exactly as it appears on the bank account')
                                                    ->columnSpanFull(),
                                            ]),
                                    ])
                                    ->collapsible(),
                            ]),

                        Forms\Components\Tabs\Tab::make('Documents')
                            ->icon('heroicon-m-document-text')
                            ->schema([
                                Forms\Components\Section::make('Contract Documents')
                                    ->description('Upload and manage vendor contracts')
                                    ->schema([
                                        Forms\Components\FileUpload::make('kontrak_kerjasama')
                                            ->label('Partnership Agreement')
                                            ->directory('vendor-contracts')
                                            ->preserveFilenames()
                                            ->acceptedFileTypes(['application/pdf'])
                                            ->maxSize(10240) // 10MB
                                            ->downloadable()
                                            ->openable()
                                            ->helperText('Upload PDF file (max 10MB)')
                                            ->columnSpanFull(),
                                    ])
                                    ->collapsible(),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->copyable()
                    ->formatStateUsing(fn (string $state): string => \Illuminate\Support\Str::title($state))
                    ->copyMessage('Vendor copied')
                    ->description(fn (Vendor $record): string => 
                        $record->category?->name ?? '-')
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('id')
                    ->label('SKU/ID'),

                Tables\Columns\TextColumn::make('pic_name')
                    ->label('PIC')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Phone number copied')
                    ->copyMessageDuration(1500)
                    ->formatStateUsing(fn (string $state) => '+62 ' . $state),

                Tables\Columns\TextColumn::make('harga_publish')
                    ->label('Published Price')
                    ->money('IDR')
                    ->sortable()
                    ->alignment('end'),

                Tables\Columns\TextColumn::make('harga_vendor')
                    ->label('Vendor Price')
                    ->money('IDR')
                    ->sortable()
                    ->alignment('end'),

                Tables\Columns\TextColumn::make('profit_amount')
                    ->label('Profit')
                    ->money('IDR')
                    ->state(function (Vendor $record): float {
                        return $record->harga_publish - $record->harga_vendor;
                    })
                    ->alignment('end')
                    ->color(fn (Vendor $record): string => 
                        ($record->harga_publish - $record->harga_vendor) > 0 ? 'success' : 'danger'
                    ),

                Tables\Columns\TextColumn::make('bank_name')
                    ->label('Bank')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('bank_account')
                    ->label('Account Number')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created Date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->label('Updated Date')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->requiresConfirmation(),
                    Tables\Actions\Action::make('duplicate')
                        ->label('Duplicate')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->modalHeading('Duplicate Vendor')
                        ->modalDescription('Are you sure you want to duplicate this vendor? The name and slug will be modified to ensure uniqueness.')
                        ->modalSubmitActionLabel('Yes, duplicate')
                        ->action(function (Vendor $record) {
                            $attributesToDuplicate = $record->getAttributes();
                            unset(
                                $attributesToDuplicate['id'],
                                $attributesToDuplicate['slug'], // Slug will be regenerated
                                $attributesToDuplicate['created_at'],
                                $attributesToDuplicate['updated_at'],
                                $attributesToDuplicate['deleted_at']
                            );

                            $newVendor = new Vendor($attributesToDuplicate);
                            $newVendor->name = $record->name . ' (Copy)';

                            // Generate unique slug
                            $baseSlug = Str::slug($newVendor->name);
                            $newSlug = $baseSlug;
                            $counter = 1;
                            while (Vendor::where('slug', $newSlug)->exists()) {
                                $newSlug = $baseSlug . '-' . $counter++;
                            }
                            $newVendor->slug = $newSlug;
                            $newVendor->save();

                            Notification::make()
                                ->title('Vendor Duplicated')
                                ->body("Vendor '{$record->name}' has been successfully duplicated as '{$newVendor->name}'.")
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    Tables\Actions\ForceDeleteAction::make()
                        ->requiresConfirmation(),
                    Tables\Actions\RestoreAction::make(),
                ])->icon('heroicon-m-ellipsis-vertical')
                  ->tooltip('Actions'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->requiresConfirmation(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-building-storefront')
            ->emptyStateHeading('No vendors yet')
            ->emptyStateDescription('Create your first vendor to get started.')
            ->emptyStateActions([
                Tables\Actions\Action::make('create')
                    ->label('Create vendor')
                    ->url(route('filament.admin.resources.vendors.create'))
                    ->icon('heroicon-m-plus')
                    ->button(),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10, 25, 50])
            ->poll('60s');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Tabs::make('Vendor Information')
                    ->tabs([
                        Infolists\Components\Tabs\Tab::make('Basic Information')
                            ->icon('heroicon-m-information-circle')
                            ->schema([
                                Infolists\Components\Section::make('Vendor Identity')
                                    ->schema([
                                        Infolists\Components\Grid::make(4)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('name')
                                                    ->label('Vendor Name'),
                                                
                                                Infolists\Components\TextEntry::make('category.name')
                                                    ->label('Category'),

                                                Infolists\Components\TextEntry::make('pic_name')
                                                    ->label('PIC Name'),

                                                Infolists\Components\TextEntry::make('phone')
                                                    ->label('Phone Number')
                                                    ->formatStateUsing(fn (string $state) => '+62 ' . $state),

                                                Infolists\Components\TextEntry::make('address')
                                                    ->columnSpanFull(),
                                            ]),
                                    ]),

                                Infolists\Components\Section::make('Business Details')
                                    ->schema([
                                        Infolists\Components\Grid::make(2)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('description')
                                                    ->markdown()
                                                    ->columnSpanFull(),
                                            ]),
                                    ]),
                            ]),

                        Infolists\Components\Tabs\Tab::make('Financial Information')
                            ->icon('heroicon-m-currency-dollar')
                            ->schema([
                                Infolists\Components\Section::make('Pricing Details')
                                    ->schema([
                                        Infolists\Components\Grid::make(2)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('harga_publish')
                                                    ->label('Published Price')
                                                    ->money('IDR'),

                                                Infolists\Components\TextEntry::make('harga_vendor')
                                                    ->label('Vendor Price')
                                                    ->money('IDR'),

                                                Infolists\Components\TextEntry::make('profit_amount')
                                                    ->label('Profit Amount')
                                                    ->state(function (Vendor $record): float {
                                                        return $record->harga_publish - $record->harga_vendor;
                                                    })
                                                    ->money('IDR')
                                                    ->color(fn (Vendor $record): string => 
                                                        ($record->harga_publish - $record->harga_vendor) > 0 ? 'success' : 'danger'
                                                    ),

                                                Infolists\Components\TextEntry::make('profit_margin')
                                                    ->label('Profit Margin')
                                                    ->state(function (Vendor $record): float {
                                                        if ($record->harga_publish > 0) {
                                                            return (($record->harga_publish - $record->harga_vendor) / $record->harga_publish) * 100;
                                                        }
                                                        return 0;
                                                    })
                                                    ->suffix('%')
                                                    ->numeric(2),
                                            ]),
                                    ]),

                                Infolists\Components\Section::make('Banking Information')
                                    ->schema([
                                        Infolists\Components\Grid::make(4)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('bank_name')
                                                    ->label('Bank Name')
                                                    ->prefix('Bank '),

                                                Infolists\Components\TextEntry::make('bank_account')
                                                    ->label('Account Number'),

                                                Infolists\Components\TextEntry::make('account_holder')
                                                    ->label('Account Holder'),
                                            ]),
                                    ]),
                            ]),

                        Infolists\Components\Tabs\Tab::make('Documents')
                            ->icon('heroicon-m-document-text')
                            ->schema([
                                Infolists\Components\Section::make('Contract Documents')
                                    ->schema([
                                        Infolists\Components\Grid::make(1)
                                            ->schema([
                                                Infolists\Components\ViewEntry::make('kontrak_kerjasama')
                                                    ->label('Partnership Agreement')
                                                    ->view('filament.infolists.components.file-view')
                                                    ->columnSpanFull(),
                                            ]),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
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
            'index' => Pages\ListVendors::route('/'),
            'create' => Pages\CreateVendor::route('/create'),
            'view' => Pages\ViewVendor::route('/{record}'),
            'edit' => Pages\EditVendor::route('/{record}/edit'),
        ];
    }    

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Data vendor yang telah dibuat dan dikelola';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'name',
            'pic_name',
            'phone',
            'bank_name',
            'bank_account',
            'account_holder',
            'address',
            'category.name',
        ];
    }

    protected static function calculateProfitMetrics(Forms\Set $set, Forms\Get $get): void
    {
        try {
            $publishPrice = (float) str_replace([',', '.'], '', $get('harga_publish') ?? '0');
            $vendorPrice = (float) str_replace([',', '.'], '', $get('harga_vendor') ?? '0');
            
            if ($publishPrice > 0) {
                $profit = $publishPrice - $vendorPrice;
                $margin = ($profit / $publishPrice) * 100;
                
                $set('profit_amount', $profit);
                $set('profit_margin', round($margin, 2));
            } else {
                $set('profit_amount', 0);
                $set('profit_margin', 0);
            }
        } catch (\Exception $e) {
            // Set default values if calculation fails
            $set('profit_amount', 0);
            $set('profit_margin', 0);
        }
    }

    public static function getModelLabel(): string
    {
        return __('Vendor');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Vendors');
    }

    public static function getNavigationLabel(): string
    {
        return __('Vendors');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return static::mutateFormData($data);
    }

    protected static function mutateFormData(array $data): array
    {
    // Clean up phone number format with better handling
    if (isset($data['phone'])) {
        $phone = preg_replace('/[^0-9]/', '', $data['phone']);
        // Handle if phone starts with 62
        if (str_starts_with($phone, '62')) {
            $phone = substr($phone, 2);
        }
        // Handle if phone starts with 0
        if (str_starts_with($phone, '0')) {
            $phone = substr($phone, 1);
        }
        $data['phone'] = $phone;
    }

    // Better price handling
    if (isset($data['harga_publish'])) {
        $data['harga_publish'] = (float) str_replace([',', '.'], '', $data['harga_publish']);
    }
    if (isset($data['harga_vendor'])) {
        $data['harga_vendor'] = (float) str_replace([',', '.'], '', $data['harga_vendor']);
    }

    // Handle empty strings for numeric fields
    if (empty($data['stock'])) {
        $data['stock'] = 0;
    }

    // Clean up bank account with better handling
    if (isset($data['bank_account'])) {
        $data['bank_account'] = preg_replace('/[^0-9]/', '', $data['bank_account']);
        if (empty($data['bank_account'])) {
            unset($data['bank_account']); // Remove if empty instead of saving empty string
        }
    }

    return $data;
    }

    public static function getNavigationSortOrder(): int
    {
        return 1;
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-building-storefront';
    }

    protected function mutateFormDataBeforeUpdate(array $data): array
    {
        try {
            $mutatedData = static::mutateFormData($data);
            
            // Log the before and after data
            Log::info('Vendor Update - Original Data:', $data);
            Log::info('Vendor Update - Mutated Data:', $mutatedData);
            
            return $mutatedData;
        } catch (\Exception $e) {
            Log::error('Vendor Update Error:', [
                'message' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }
}
