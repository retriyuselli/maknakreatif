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
use Illuminate\Database\Eloquent\Collection;
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

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'vendor' => 'primary',
                        'product' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'vendor' => 'Vendor',
                        'product' => 'Product',
                        default => ucfirst($state),
                    }),

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

                Tables\Columns\TextColumn::make('usage_status')
                    ->label('Usage Status')
                    ->badge()
                    ->getStateUsing(function (Vendor $record): string {
                        $productCount = $record->productVendors()->count();
                        $expenseCount = $record->vendors()->count();
                        $notaDinasCount = $record->notaDinasDetails()->count();
                        
                        if ($productCount > 0 || $expenseCount > 0 || $notaDinasCount > 0) {
                            return 'In Use';
                        }
                        return 'Available';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'In Use' => 'warning',
                        'Available' => 'success',
                        default => 'gray',
                    })
                    ->tooltip(function (Vendor $record): string {
                        $productCount = $record->productVendors()->count();
                        $expenseCount = $record->vendors()->count();
                        $notaDinasCount = $record->notaDinasDetails()->count();
                        
                        $details = [];
                        if ($productCount > 0) {
                            $details[] = "{$productCount} product(s)";
                        }
                        if ($expenseCount > 0) {
                            $details[] = "{$expenseCount} expense(s)";
                        }
                        if ($notaDinasCount > 0) {
                            $details[] = "{$notaDinasCount} nota dinas detail(s)";
                        }
                        
                        if (!empty($details)) {
                            return 'Used in: ' . implode(', ', $details);
                        }
                        return 'Not used in any products, expenses, or nota dinas details';
                    })
                    ->sortable(false)
                    ->searchable(false)
                    ->toggleable(),

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

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'vendor' => 'Vendor',
                        'product' => 'Product',
                    ])
                    ->multiple(),

                Tables\Filters\Filter::make('usage_status')
                    ->label('Usage Status')
                    ->form([
                        Forms\Components\Select::make('usage')
                            ->label('Filter by Usage')
                            ->options([
                                'in_use' => 'In Use',
                                'available' => 'Available',
                            ])
                            ->placeholder('All Vendors'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['usage'] === 'in_use',
                            fn (Builder $query): Builder => $query->whereHas('productVendors')
                                ->orWhereHas('vendors') // expenses
                                ->orWhereHas('notaDinasDetails'), // nota dinas details
                        )->when(
                            $data['usage'] === 'available',
                            fn (Builder $query): Builder => $query->whereDoesntHave('productVendors')
                                ->whereDoesntHave('vendors') // expenses
                                ->whereDoesntHave('notaDinasDetails'), // nota dinas details
                        );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if ($data['usage']) {
                            return 'Usage: ' . ($data['usage'] === 'in_use' ? 'In Use' : 'Available');
                        }
                        return null;
                    }),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->icon('heroicon-m-trash')
                        ->tooltip('Delete vendor')
                        ->requiresConfirmation()
                        ->modalHeading('Delete Vendor')
                        ->modalDescription('Are you sure you want to delete this vendor? This action cannot be undone.')
                        ->modalSubmitActionLabel('Yes, delete')
                        ->modalIcon('heroicon-o-exclamation-triangle')
                        ->modalIconColor('danger')
                        ->visible(function (Vendor $record): bool {
                            $productCount = $record->productVendors()->count();
                            $expenseCount = $record->vendors()->count();
                            $notaDinasCount = $record->notaDinasDetails()->count();
                            return $productCount === 0 && $expenseCount === 0 && $notaDinasCount === 0;
                        })
                        ->before(function (?Vendor $record) {
                            if (!$record) {
                                Notification::make()
                                    ->danger()
                                    ->title('Error')
                                    ->body('Vendor data not found. Please refresh the page and try again.')
                                    ->persistent()
                                    ->send();
                                return false;
                            }
                            
                            Notification::make()
                                ->info()
                                ->title('Processing')
                                ->body('Validating vendor for deletion...')
                                ->send();
                        })
                        ->action(function (?Vendor $record) {
                            if (!$record) {
                                Notification::make()
                                    ->danger()
                                    ->title('Deletion Failed')
                                    ->body('Vendor data not found. May have been already deleted or moved.')
                                    ->persistent()
                                    ->send();
                                return false;
                            }

                            try {
                                $record->refresh();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->danger()
                                    ->title('Deletion Failed')
                                    ->body('Cannot access vendor data. May have been deleted by another user.')
                                    ->persistent()
                                    ->send();
                                return false;
                            }

                            // Double check for associations
                            $productCount = $record->productVendors()->count();
                            $expenseCount = $record->vendors()->count();
                            
                            if ($productCount > 0 || $expenseCount > 0) {
                                $details = [];
                                if ($productCount > 0) {
                                    $details[] = "{$productCount} product(s)";
                                }
                                if ($expenseCount > 0) {
                                    $details[] = "{$expenseCount} expense(s)";
                                }
                                
                                Notification::make()
                                    ->danger()
                                    ->title('Deletion Not Allowed')
                                    ->body("Vendor '{$record->name}' cannot be deleted because it is being used in " . implode(' and ', $details) . ". Please remove these associations first.")
                                    ->persistent()
                                    ->send();
                                return false;
                            }
                            
                            try {
                                $vendorName = $record->name ?? 'Unknown Vendor';
                                $record->delete();
                                
                                Notification::make()
                                    ->success()
                                    ->title('Vendor Successfully Deleted')
                                    ->body("'{$vendorName}' has been deleted from the system.")
                                    ->duration(5000)
                                    ->send();
                                    
                                return true;
                                
                            } catch (\Illuminate\Database\QueryException $e) {
                                $errorCode = $e->getCode();
                                if ($errorCode === '23000') {
                                    Notification::make()
                                        ->danger()
                                        ->title('Deletion Failed - Data Constraint')
                                        ->body('This vendor cannot be deleted because it is referenced by other data in the system.')
                                        ->persistent()
                                        ->send();
                                } else {
                                    Notification::make()
                                        ->danger()
                                        ->title('Database Error')
                                        ->body('A database error occurred while deleting the vendor. Please try again later.')
                                        ->persistent()
                                        ->send();
                                }
                                return false;
                                
                            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                                Notification::make()
                                    ->warning()
                                    ->title('Vendor Already Deleted')
                                    ->body('This vendor appears to have been already deleted by another user.')
                                    ->send();
                                return false;
                                
                            } catch (\Exception $e) {
                                Log::error('Vendor deletion failed', [
                                    'vendor_id' => $record->id ?? 'unknown',
                                    'error' => $e->getMessage(),
                                    'trace' => $e->getTraceAsString()
                                ]);
                                
                                Notification::make()
                                    ->danger()
                                    ->title('Unexpected Error')
                                    ->body('An unexpected error occurred while deleting the vendor. System administrator has been notified.')
                                    ->persistent()
                                    ->send();
                                return false;
                            }
                        }),

                    Tables\Actions\Action::make('cannot_delete')
                        ->label('Cannot Delete')
                        ->icon('heroicon-m-shield-exclamation')
                        ->color('gray')
                        ->tooltip('This vendor cannot be deleted because it is being used')
                        ->visible(function (Vendor $record): bool {
                            $productCount = $record->productVendors()->count();
                            $expenseCount = $record->vendors()->count();
                            $notaDinasCount = $record->notaDinasDetails()->count();
                            return $productCount > 0 || $expenseCount > 0 || $notaDinasCount > 0;
                        })
                        ->action(function (Vendor $record) {
                            $productCount = $record->productVendors()->count();
                            $expenseCount = $record->vendors()->count();
                            $notaDinasCount = $record->notaDinasDetails()->count();
                            
                            $details = [];
                            if ($productCount > 0) {
                                $details[] = "{$productCount} product(s)";
                            }
                            if ($expenseCount > 0) {
                                $details[] = "{$expenseCount} expense(s)";
                            }
                            if ($notaDinasCount > 0) {
                                $details[] = "{$notaDinasCount} nota dinas detail(s)";
                            }
                            
                            Notification::make()
                                ->warning()
                                ->title('Cannot Delete Vendor')
                                ->body("'{$record->name}' cannot be deleted because it has associated " . implode(' and ', $details) . ". Please remove these associations first.")
                                ->persistent()
                                ->send();
                        }),
                    Tables\Actions\Action::make('view_usage')
                        ->label('View Usage')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->modalHeading(fn (Vendor $record) => 'Usage Details for: ' . $record->name)
                        ->modalDescription('See where this vendor is currently being used')
                        ->modalContent(function (Vendor $record) {
                            $productCount = $record->productVendors()->count();
                            $expenseCount = $record->vendors()->count();
                            
                            $content = '<div class="space-y-4">';
                            
                            if ($productCount > 0) {
                                $products = $record->productVendors()
                                    ->with('product')
                                    ->get()
                                    ->groupBy('product.name');
                                
                                $content .= '<div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">';
                                $content .= '<h3 class="font-semibold text-yellow-800 mb-2">Used in Products (' . $productCount . ' items)</h3>';
                                $content .= '<ul class="list-disc list-inside text-yellow-700 space-y-1">';
                                
                                foreach ($products as $productName => $items) {
                                    $totalQty = $items->sum('quantity');
                                    $content .= '<li>' . $productName . ' (Quantity: ' . $totalQty . ')</li>';
                                }
                                
                                $content .= '</ul></div>';
                            }
                            
                            if ($expenseCount > 0) {
                                $content .= '<div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">';
                                $content .= '<h3 class="font-semibold text-blue-800 mb-2">Related Expenses</h3>';
                                $content .= '<p class="text-blue-700">' . $expenseCount . ' expense transaction(s) are associated with this vendor.</p>';
                                $content .= '</div>';
                            }
                            
                            if ($productCount === 0 && $expenseCount === 0) {
                                $content .= '<div class="p-4 bg-green-50 border border-green-200 rounded-lg">';
                                $content .= '<h3 class="font-semibold text-green-800 mb-2">No Usage Found</h3>';
                                $content .= '<p class="text-green-700">This vendor is not currently used in any products or expenses and can be safely deleted.</p>';
                                $content .= '</div>';
                            }
                            
                            $content .= '</div>';
                            
                            return new \Illuminate\Support\HtmlString($content);
                        })
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close'),
                    Tables\Actions\Action::make('view_products')
                        ->label('View Products')
                        ->icon('heroicon-o-shopping-bag')
                        ->color('success')
                        ->modalHeading(fn (Vendor $record) => 'Products using: ' . $record->name)
                        ->modalDescription('Detailed list of all products that use this vendor')
                        ->visible(fn (Vendor $record) => $record->productVendors()->count() > 0)
                        ->modalContent(function (Vendor $record) {
                            $productVendors = $record->productVendors()
                                ->with(['product.category'])
                                ->orderBy('created_at', 'desc')
                                ->get();
                            
                            $content = '<div class="space-y-4">';
                            
                            if ($productVendors->count() > 0) {
                                $content .= '<div class="p-4 bg-green-50 border border-green-200 rounded-lg">';
                                $content .= '<h3 class="font-semibold text-green-800 mb-4">Products List (' . $productVendors->count() . ' entries)</h3>';
                                
                                // Group by product
                                $groupedProducts = $productVendors->groupBy('product.name');
                                
                                foreach ($groupedProducts as $productName => $items) {
                                    $product = $items->first()->product;
                                    $totalQuantity = $items->sum('quantity');
                                    
                                    $content .= '<div class="mb-4 p-3 bg-white border border-green-300 rounded-lg">';
                                    $content .= '<div class="flex justify-between items-start mb-2">';
                                    $content .= '<h4 class="font-medium text-green-900">' . $productName . '</h4>';
                                    $content .= '<span class="text-sm text-green-600 bg-green-100 px-2 py-1 rounded">Total Qty: ' . $totalQuantity . '</span>';
                                    $content .= '</div>';
                                    
                                    if ($product && $product->category) {
                                        $content .= '<p class="text-sm text-green-700 mb-2"><strong>Category:</strong> ' . $product->category->name . '</p>';
                                    }
                                    
                                    // Detail per entry
                                    $content .= '<div class="text-sm text-green-600">';
                                    $content .= '<strong>Usage Details:</strong>';
                                    $content .= '<ul class="list-disc list-inside mt-1 ml-2">';
                                    
                                    foreach ($items as $item) {
                                        $content .= '<li>Quantity: ' . $item->quantity;
                                        if ($item->price) {
                                            $content .= ' | Price: Rp ' . number_format($item->price, 0, ',', '.');
                                        }
                                        if ($item->created_at) {
                                            $content .= ' | Added: ' . $item->created_at->format('d M Y');
                                        }
                                        $content .= '</li>';
                                    }
                                    
                                    $content .= '</ul>';
                                    $content .= '</div>';
                                    $content .= '</div>';
                                }
                            } else {
                                $content .= '<div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">';
                                $content .= '<p class="text-gray-600">This vendor is not used in any products.</p>';
                                $content .= '</div>';
                            }
                            
                            $content .= '</div>';
                            
                            return new \Illuminate\Support\HtmlString($content);
                        })
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close'),
                    Tables\Actions\Action::make('view_expenses')
                        ->label('View Expenses')
                        ->icon('heroicon-o-banknotes')
                        ->color('warning')
                        ->modalHeading(fn (Vendor $record) => 'Expenses for: ' . $record->name)
                        ->modalDescription('Detailed list of all expenses related to this vendor')
                        ->visible(fn (Vendor $record) => $record->vendors()->count() > 0)
                        ->modalContent(function (Vendor $record) {
                            $expenses = $record->vendors()
                                ->orderBy('created_at', 'desc')
                                ->get();
                            
                            $content = '<div class="space-y-4">';
                            
                            if ($expenses->count() > 0) {
                                $totalAmount = $expenses->sum('amount');
                                
                                $content .= '<div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">';
                                $content .= '<h3 class="font-semibold text-yellow-800 mb-4">Expenses List (' . $expenses->count() . ' transactions)</h3>';
                                $content .= '<p class="text-yellow-700 mb-4"><strong>Total Amount:</strong> Rp ' . number_format($totalAmount, 0, ',', '.') . '</p>';
                                
                                foreach ($expenses as $expense) {
                                    $content .= '<div class="mb-3 p-3 bg-white border border-yellow-300 rounded-lg">';
                                    $content .= '<div class="flex justify-between items-start mb-2">';
                                    $content .= '<h4 class="font-medium text-yellow-900">' . ($expense->description ?? 'No Description') . '</h4>';
                                    $content .= '<span class="text-sm text-yellow-600 bg-yellow-100 px-2 py-1 rounded">Rp ' . number_format($expense->amount, 0, ',', '.') . '</span>';
                                    $content .= '</div>';
                                    
                                    $content .= '<div class="text-sm text-yellow-600 space-y-1">';
                                    if ($expense->transaction_date) {
                                        $content .= '<p><strong>Date:</strong> ' . \Carbon\Carbon::parse($expense->transaction_date)->format('d M Y') . '</p>';
                                    }
                                    if ($expense->category_uang_keluar) {
                                        $content .= '<p><strong>Category:</strong> ' . ucfirst(str_replace('_', ' ', $expense->category_uang_keluar)) . '</p>';
                                    }
                                    if ($expense->created_at) {
                                        $content .= '<p><strong>Recorded:</strong> ' . $expense->created_at->format('d M Y H:i') . '</p>';
                                    }
                                    $content .= '</div>';
                                    $content .= '</div>';
                                }
                            } else {
                                $content .= '<div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">';
                                $content .= '<p class="text-gray-600">This vendor has no related expenses.</p>';
                                $content .= '</div>';
                            }
                            
                            $content .= '</div>';
                            
                            return new \Illuminate\Support\HtmlString($content);
                        })
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close'),
                    Tables\Actions\Action::make('view_nota_dinas')
                        ->label('View Nota Dinas')
                        ->icon('heroicon-o-document-text')
                        ->color('info')
                        ->modalHeading(fn (Vendor $record) => 'Nota Dinas for: ' . $record->name)
                        ->modalDescription('Detailed list of all nota dinas details related to this vendor')
                        ->visible(fn (Vendor $record) => $record->notaDinasDetails()->count() > 0)
                        ->modalContent(function (Vendor $record) {
                            $notaDinasDetails = $record->notaDinasDetails()
                                ->with('notaDinas')
                                ->orderBy('created_at', 'desc')
                                ->get();
                            
                            $content = '<div class="space-y-4">';
                            
                            if ($notaDinasDetails->count() > 0) {
                                $totalAmount = $notaDinasDetails->sum('jumlah_transfer');
                                
                                $content .= '<div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">';
                                $content .= '<h3 class="font-semibold text-blue-800 mb-4">Nota Dinas Details (' . $notaDinasDetails->count() . ' entries)</h3>';
                                $content .= '<p class="text-blue-700 mb-4"><strong>Total Transfer Amount:</strong> Rp ' . number_format($totalAmount, 0, ',', '.') . '</p>';
                                
                                foreach ($notaDinasDetails as $detail) {
                                    $content .= '<div class="mb-3 p-3 bg-white border border-blue-300 rounded-lg">';
                                    $content .= '<div class="flex justify-between items-start mb-2">';
                                    $content .= '<h4 class="font-medium text-blue-900">' . ($detail->keperluan ?? 'No Description') . '</h4>';
                                    $content .= '<span class="text-sm text-blue-600 bg-blue-100 px-2 py-1 rounded">Rp ' . number_format($detail->jumlah_transfer, 0, ',', '.') . '</span>';
                                    $content .= '</div>';
                                    
                                    $content .= '<div class="text-sm text-blue-600 space-y-1">';
                                    if ($detail->event) {
                                        $content .= '<p><strong>Event:</strong> ' . $detail->event . '</p>';
                                    }
                                    if ($detail->invoice_number) {
                                        $content .= '<p><strong>Invoice:</strong> ' . $detail->invoice_number . '</p>';
                                    }
                                    if ($detail->status_invoice) {
                                        $statusLabel = ucfirst(str_replace('_', ' ', $detail->status_invoice));
                                        $statusColor = match($detail->status_invoice) {
                                            'sudah_dibayar' => 'text-green-600',
                                            'menunggu' => 'text-yellow-600',
                                            'belum_dibayar' => 'text-red-600',
                                            default => 'text-blue-600'
                                        };
                                        $content .= '<p><strong>Status:</strong> <span class="' . $statusColor . '">' . $statusLabel . '</span></p>';
                                    }
                                    if ($detail->payment_stage) {
                                        $content .= '<p><strong>Payment Stage:</strong> ' . ucfirst(str_replace('_', ' ', $detail->payment_stage)) . '</p>';
                                    }
                                    if ($detail->jenis_pengeluaran) {
                                        $content .= '<p><strong>Type:</strong> ' . ucfirst(str_replace('_', ' ', $detail->jenis_pengeluaran)) . '</p>';
                                    }
                                    if ($detail->created_at) {
                                        $content .= '<p><strong>Created:</strong> ' . $detail->created_at->format('d M Y H:i') . '</p>';
                                    }
                                    $content .= '</div>';
                                    $content .= '</div>';
                                }
                            } else {
                                $content .= '<div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">';
                                $content .= '<p class="text-gray-600">This vendor has no related nota dinas details.</p>';
                                $content .= '</div>';
                            }
                            
                            $content .= '</div>';
                            
                            return new \Illuminate\Support\HtmlString($content);
                        })
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close'),
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
                        ->icon('heroicon-m-trash')
                        ->requiresConfirmation()
                        ->modalHeading('Delete Selected Vendors')
                        ->modalDescription('Are you sure you want to delete the selected vendors? This action cannot be undone.')
                        ->modalSubmitActionLabel('Yes, delete selected')
                        ->action(function (Collection $records) {
                            $deletedCount = 0;
                            $protectedVendors = [];
                            $errorVendors = [];
                            
                            foreach ($records as $vendor) {
                                try {
                                    // Check if vendor can be deleted
                                    $productCount = $vendor->productVendors()->count();
                                    $expenseCount = $vendor->vendors()->count();
                                    $notaDinasCount = $vendor->notaDinasDetails()->count();
                                    
                                    if ($productCount > 0 || $expenseCount > 0 || $notaDinasCount > 0) {
                                        $details = [];
                                        if ($productCount > 0) {
                                            $details[] = "{$productCount} product(s)";
                                        }
                                        if ($expenseCount > 0) {
                                            $details[] = "{$expenseCount} expense(s)";
                                        }
                                        if ($notaDinasCount > 0) {
                                            $details[] = "{$notaDinasCount} nota dinas detail(s)";
                                        }
                                        $protectedVendors[] = "• {$vendor->name}: " . implode(', ', $details);
                                        continue;
                                    }
                                    
                                    // Attempt to delete
                                    $vendor->delete();
                                    $deletedCount++;
                                    
                                } catch (\Exception $e) {
                                    $errorVendors[] = "{$vendor->name}: {$e->getMessage()}";
                                }
                            }
                            
                            // Show results
                            if ($deletedCount > 0) {
                                Notification::make()
                                    ->success()
                                    ->title('Vendors Deleted')
                                    ->body("{$deletedCount} vendor(s) have been successfully deleted.")
                                    ->send();
                            }
                            
                            if (!empty($protectedVendors)) {
                                Notification::make()
                                    ->warning()
                                    ->title('Some Vendors Could Not Be Deleted')
                                    ->body("The following vendors cannot be deleted because they are being used:\n\n" . implode("\n", $protectedVendors) . "\n\nPlease remove these associations first.")
                                    ->persistent()
                                    ->send();
                            }
                            
                            if (!empty($errorVendors)) {
                                Notification::make()
                                    ->danger()
                                    ->title('Deletion Errors')
                                    ->body("Errors occurred while deleting some vendors:\n\n" . implode("\n", $errorVendors))
                                    ->persistent()
                                    ->send();
                            }
                            
                            if ($deletedCount === 0 && empty($protectedVendors) && empty($errorVendors)) {
                                Notification::make()
                                    ->info()
                                    ->title('No Action Taken')
                                    ->body('No valid data found for deletion.')
                                    ->send();
                            }
                        }),
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

                                                Infolists\Components\TextEntry::make('status')
                                                    ->label('Status')
                                                    ->badge()
                                                    ->color(fn (string $state): string => match ($state) {
                                                        'vendor' => 'primary',
                                                        'product' => 'success',
                                                        default => 'gray',
                                                    })
                                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                                        'vendor' => 'Vendor',
                                                        'product' => 'Product',
                                                        default => ucfirst($state),
                                                    }),

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

                        Infolists\Components\Tabs\Tab::make('Usage Information')
                            ->icon('heroicon-m-chart-bar')
                            ->schema([
                                Infolists\Components\Section::make('Vendor Usage Overview')
                                    ->schema([
                                        Infolists\Components\Grid::make(3)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('products_count')
                                                    ->label('Used in Products')
                                                    ->state(function (Vendor $record): int {
                                                        return $record->productVendors()->count();
                                                    })
                                                    ->badge()
                                                    ->color(fn (int $state): string => $state > 0 ? 'warning' : 'success')
                                                    ->suffix(' items'),

                                                Infolists\Components\TextEntry::make('expenses_count')
                                                    ->label('Related Expenses')
                                                    ->state(function (Vendor $record): int {
                                                        return $record->vendors()->count();
                                                    })
                                                    ->badge()
                                                    ->color(fn (int $state): string => $state > 0 ? 'info' : 'gray')
                                                    ->suffix(' transactions'),

                                                Infolists\Components\TextEntry::make('deletion_status')
                                                    ->label('Deletion Status')
                                                    ->state(function (Vendor $record): string {
                                                        $productCount = $record->productVendors()->count();
                                                        $expenseCount = $record->vendors()->count();
                                                        
                                                        if ($productCount > 0 || $expenseCount > 0) {
                                                            return 'Protected';
                                                        }
                                                        return 'Can be deleted';
                                                    })
                                                    ->badge()
                                                    ->color(function (Vendor $record): string {
                                                        $productCount = $record->productVendors()->count();
                                                        $expenseCount = $record->vendors()->count();
                                                        
                                                        if ($productCount > 0 || $expenseCount > 0) {
                                                            return 'danger';
                                                        }
                                                        return 'success';
                                                    }),
                                            ]),
                                    ]),

                                Infolists\Components\Section::make('Usage Details')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('usage_details')
                                            ->label('Detailed Usage Information')
                                            ->state(function (Vendor $record): string {
                                                $productCount = $record->productVendors()->count();
                                                $expenseCount = $record->vendors()->count();
                                                
                                                $details = [];
                                                
                                                if ($productCount > 0) {
                                                    $productNames = $record->productVendors()
                                                        ->with('product')
                                                        ->get()
                                                        ->pluck('product.name')
                                                        ->unique()
                                                        ->take(5);
                                                    
                                                    $details[] = "Products ({$productCount} total): " . $productNames->implode(', ') . 
                                                        ($productCount > 5 ? ' and ' . ($productCount - 5) . ' more...' : '');
                                                }
                                                
                                                if ($expenseCount > 0) {
                                                    $details[] = "Expenses: {$expenseCount} transaction(s)";
                                                }
                                                
                                                if (empty($details)) {
                                                    return 'This vendor is not currently used in any products or expenses and can be safely deleted.';
                                                }
                                                
                                                return implode("\n\n", $details) . 
                                                    "\n\nNote: This vendor cannot be deleted while these associations exist.";
                                            })
                                            ->markdown()
                                            ->columnSpanFull(),
                                    ])
                                    ->collapsible(),
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
            'status',
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
