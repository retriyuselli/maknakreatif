<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\Category;
use App\Models\Vendor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\RawJs;
use Illuminate\Support\Str;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Actions\Action;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductExport; 
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationGroup = 'Shop';
    protected static ?string $navigationLabel = 'Product';
    protected static ?string $pluralModelLabel = 'Products';
    protected static ?string $modelLabel = 'Product';
    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Product Details')
                    ->tabs([
                        Tab::make('Basic Information')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->live(onBlur: true)
                                        ->maxLength(255)
                                        ->placeholder('nama pengantin_lokasi_pax')
                                        ->afterStateUpdated(fn (string $state, Set $set) => 
                                            $set('slug', Str::slug($state))
                                        ),
    
                                    Forms\Components\Hidden::make('slug')
                                        ->disabled()
                                        ->dehydrated()
                                        ->unique(ignoreRecord: true)
                                        ->helperText('Auto-generated from name'),
    
                                    Forms\Components\FileUpload::make('image')
                                        ->image()
                                        ->imageEditor()
                                        ->directory('products')
                                        ->downloadable(),

                                    Forms\Components\Select::make('category_id')
                                        ->relationship('category', 'name')
                                        ->searchable()
                                        ->required()
                                        ->preload()
                                        ->placeholder('Select a category')
                                        ->createOptionForm([
                                            
                                    Forms\Components\TextInput::make('name')
                                        ->required()
                                        ->maxLength(255)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn (string $state, Set $set) => 
                                            $set('slug', Str::slug($state))
                                        ),
                                    Forms\Components\TextInput::make('slug')
                                        ->disabled()
                                        ->dehydrated()
                                        ->maxLength(255)
                                        ->unique(Category::class, 'slug', ignoreRecord: true),
                                    Forms\Components\Textarea::make('description')
                                        ->maxLength(1000)
                                        ->placeholder('Category description')
                                        ])
                                        ->createOptionAction(
                                            fn (Action $action) => $action
                                                ->modalHeading('Create new category')
                                                ->modalSubmitActionLabel('Create category')
                                        ),
    
                                    Forms\Components\TextInput::make('pax')
                                        ->label('Capacity (pax)')
                                        ->required()
                                        ->numeric()
                                        ->minValue(1)
                                        ->default(1000)
                                        ->suffix('people')
                                        ->placeholder('1000'),
    
                                    Forms\Components\TextInput::make('price')
                                        ->prefix('Rp')
                                        ->readOnly()
                                        ->label('Product Price')
                                        ->reactive()
                                        ->live()
                                        ->dehydrated()
                                        ->mask(RawJs::make('$money($input)'))
                                        ->stripCharacters(',')
                                        ->helperText('Total Publish Price - Total Pengurangan'),
    
                                    Forms\Components\TextInput::make('stock')
                                        ->required()
                                        ->numeric()
                                        ->minValue(0)
                                        ->default(10)
                                        ->suffix('units')
                                        ->placeholder('0')
                                        ->helperText('pastikan di isi dengan angka 10'),

                                ]),
    
                                Section::make('Product Status')
                                    ->schema([
                                        Forms\Components\Toggle::make('is_active')
                                            ->label('Product Status')
                                            ->helperText('Toggle to enable/disable product visibility')
                                            ->default(true)
                                            ->onIcon('heroicon-s-check-circle')
                                            ->offIcon('heroicon-s-x-circle')
                                            ->onColor('success')
                                            ->offColor('danger'),
                                        Forms\Components\Toggle::make('is_approved')
                                            ->label('Approval Status')
                                            ->helperText('Toggle to approve/disapprove product')
                                            ->default(false)
                                            ->onIcon('heroicon-s-hand-thumb-up')
                                            ->offIcon('heroicon-s-hand-thumb-down')
                                            ->onColor('success')
                                            ->offColor('danger')
                                            ->visible(fn () => Auth::user()->hasRole('super_admin')),
                                    ])
                                    ->collapsible(),
                            ]),
    
                        Tab::make('Facilities & Vendors')
                            ->icon('heroicon-o-cube')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('product_price')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->label('Total Publish Price')
                                            ->readOnly()
                                            ->live()
                                            ->dehydrated(true) // pastikan field ini disimpan
                                            ->mask(RawJs::make('$money($input)'))
                                            ->stripCharacters(',')
                                            ->helperText('Automatically calculated from vendor prices')
                                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                                $set('price', (int)$get('publish_price') - (int)$get('pengurangan'));
                                            }),
                                        Forms\Components\TextInput::make('vendorTotal')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->label('Total Vendor Price')
                                            ->readOnly()
                                            ->mask(RawJs::make('$money($input)'))
                                            ->stripCharacters(',')
                                            ->helperText('Sum of all vendor prices')
                                    ]),
                                self::getVendorRepeater(),
                            ]),
                        Tab::make('Pengurangan Harga')
                            ->icon('heroicon-o-receipt-refund') // Changed icon
                            ->label('Pengurangan Harga (Jika Ada)') // Corrected typo
                            ->schema([
                                Forms\Components\TextInput::make('pengurangan')
                                    ->label('Total Pengurangan')
                                    ->readOnly() // supaya tidak bisa diketik
                                    ->default(0)
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->helperText('Automatically calculated from discount prices')
                                    ->afterStateHydrated(function ($component, $state, $record) {
                                        if ($record) {
                                            $total = $record->pengurangans->sum('amount');
                                            $component->state($total);
                                        }
                                    }),
                                self::getDiscountRepeater(),
                            ]),
                        Tab::make('Penambahan Harga')
                            ->icon('heroicon-o-receipt-refund') 
                            ->label('Penambahan Harga (Coming Soon)')
                            ->schema([]),
                    ])
                    ->columnSpanFull(),
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
                    ->formatStateUsing(fn (string $state): string => \Illuminate\Support\Str::title($state))
                    ->tooltip(fn (Product $record): string => $record->price)
                    ->copyable()
                    ->copyMessage('Product name copied')
                    ->copyMessageDuration(1500)
                    ->description(function (Product $record): string {
                        $priceValue = $record->price;
                        if ($priceValue === null || !is_numeric($priceValue)) {
                            return 'Rp -';
                        }
                        return 'Rp ' . number_format((float)$priceValue, 0, ',', '.');
                    }),
                
                Tables\Columns\TextColumn::make('id')
                    ->label('SKU/ID'),

                Tables\Columns\TextColumn::make('slug')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Vendors')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn (int $state): string => 
                        match(true) {
                            $state > 3 => 'success',
                            $state > 1 => 'info',
                            default => 'warning',
                        }
                    )
                    ->tooltip('Number of vendors associated with this product'),
                
                Tables\Columns\TextColumn::make('unique_orders_count')
                    ->label('In Orders')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('info')
                    ->tooltip('Number of unique orders this product is part of.'),

                Tables\Columns\TextColumn::make('total_quantity_sold')
                    ->label('Total Sold')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->tooltip('Total quantity of this product sold across all orders.'),
                
                Tables\Columns\TextColumn::make('price')
                    ->label('Total Price')
                    ->numeric()
                    ->prefix('Rp ')
                    ->sortable()
                    ->alignEnd()
                    ->badge(),
                
                Tables\Columns\TextColumn::make('product_price')
                    ->label('Harga Paket')
                    ->numeric()
                    ->prefix('Rp ')
                    ->sortable()
                    ->alignEnd()
                    ->badge(),

                Tables\Columns\TextColumn::make('pengurangan')
                    ->label('Pengurangan')
                    ->getStateUsing(fn ($record) => $record->pengurangans->sum('amount'))
                    ->prefix('Rp ')
                    ->numeric()
                    ->alignEnd()
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state == 0 ? 'warning' : 'success'),
                
                Tables\Columns\TextColumn::make('pax')
                    ->label('Capacity')
                    ->suffix(' pax')
                    ->alignCenter()
                    ->sortable()
                    ->numeric(
                        decimalPlaces: 0,
                        thousandsSeparator: '.',
                    )
                    ->color(fn (int $state): string => 
                        match(true) {
                            $state > 1000 => 'success',
                            $state > 500 => 'info',
                            default => 'gray',
                        }
                    ),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Status')
                    ->alignCenter()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable()
                    ->tooltip(fn (bool $state): string => 
                        $state ? 'Product is active' : 'Product is inactive'
                    ),

                Tables\Columns\IconColumn::make('is_approved')
                    ->boolean()
                    ->label('Approved')
                    ->alignCenter()
                    ->trueIcon('heroicon-s-hand-thumb-up')
                    ->falseIcon('heroicon-s-hand-thumb-down')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable()
                    ->tooltip(fn (bool $state): string =>
                        $state ? 'Product is approved' : 'Product is not approved'
                    ),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip(fn (Product $record): string => 
                        'Created: ' . $record->created_at->diffForHumans()
                    ),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->since()
                    ->color('gray'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),

                    // Aksi Preview Detail
                    Tables\Actions\Action::make('preview_details')
                        ->label('Preview Detail')
                        ->icon('heroicon-o-eye')
                        ->color('info') // Warna tombol/link
                        ->url(fn (Product $record): string => route('products.details', ['product' => $record, 'action' => 'preview'])) // <-- Use 'products.details'
                        ->openUrlInNewTab() // Buka di tab baru
                        ->tooltip('Lihat detail lengkap produk di tab baru'),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('duplicate')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->modalDescription('Do you want to duplicate this product and its vendor relations?')
                        ->modalSubmitActionLabel('Yes, duplicate product')
                        ->action(function (Product $record) {
                            // Duplicate main product
                            $attributes = $record->only([
                                'category_id',
                                'price',
                                'is_active',
                                'pax',
                            ]);
                            
                            $duplicate = new Product($attributes);
                            $duplicate->name = "{$record->name} (Copy)";
                            $duplicate->slug = Product::generateUniqueSlug($duplicate->name);
                            $duplicate->save();
                            
                            // Duplicate vendor relationships with all fields
                            foreach ($record->items as $item) {
                                $duplicate->items()->create([
                                    'vendor_id' => $item->vendor_id,
                                    'harga_publish' => $item->harga_publish,
                                    'quantity' => $item->quantity,
                                    'price_public' => $item->price_public,
                                    'total_price' => $item->total_price,
                                    'harga_vendor' => $item->harga_vendor,
                                    'description' => $item->description,
                                ]);
                            }
                            
                            Notification::make()
                                ->success()
                                ->title('Product duplicated successfully')
                                ->send();
                        })
                        ->tooltip('Duplicate this product'),

                    Tables\Actions\Action::make('approve')
                        ->label('Approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Product $record) {
                            $record->update(['is_approved' => true]);
                            Notification::make()->title('Product Approved')->success()->send();
                        })
                        ->visible(fn (Product $record): bool => !$record->is_approved && Auth::user()->hasRole('super_admin'))
                        ->tooltip('Approve this product'),

                    Tables\Actions\Action::make('disapprove')
                        ->label('Disapprove')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Product $record) {
                            $record->update(['is_approved' => false]);
                            Notification::make()->title('Product Disapproved')->warning()->send();
                        })
                        ->visible(fn (Product $record): bool => $record->is_approved && Auth::user()?->hasRole('super_admin'))
                        ->tooltip('Disapprove this product'),

                ])
                ->tooltip('Available actions')
                        ->tooltip('Available actions'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Ganti ExportBulkAction bawaan Filament
                    Tables\Actions\BulkAction::make('export_selected_maatwebsite')
                        ->label('Export Selected (Excel)')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (Collection $records) {
                            return Excel::download(new ProductExport($records->pluck('id')->toArray()), 'products_export_'.now()->format('YmdHis').'.xlsx');
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->requiresConfirmation(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title('Products Activated')
                                ->body(count($records) . ' product(s) have been activated.')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each->update(['is_active' => false]);
                            Notification::make()
                                ->title('Products Deactivated')
                                ->body(count($records) . ' product(s) have been deactivated.')
                                ->warning()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('approve_selected')
                        ->label('Approve Selected')
                        ->icon('heroicon-s-hand-thumb-up')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each->update(['is_approved' => true]);
                            Notification::make()
                                ->title('Products Approved')
                                ->body(count($records) . ' product(s) have been approved.')
                                ->success()
                                ->send();
                        })
                        ->visible(fn () => Auth::user()->hasRole('super_admin'))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateIcon('heroicon-o-shopping-bag')
            ->defaultPaginationPageOption(10)
            ->emptyStateActions([
                Tables\Actions\Action::make('create')
                    ->label('Create product')
                    ->url(route('filament.admin.resources.products.create'))
                    ->icon('heroicon-m-plus')
                    ->button(),
            ])
            ->paginationPageOptions([10, 25, 50]);
    }

    protected static function getVendorRepeater()
    {
        return Forms\Components\Repeater::make('items')
            ->relationship()
            ->schema([
                Forms\Components\Grid::make(4)
                    ->schema([
                        Forms\Components\Select::make('vendor_id')
                            ->relationship('vendor', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Select a vendor')
                            ->required()
                            ->live()
                            ->reactive()
                            ->afterStateHydrated(function (Set $set, Get $get, $state) {
                                if ($state) {
                                    self::updateVendorData($set, $state);
                                    self::calculatePrices($get, $set);
                                }
                            })
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                if ($state) {
                                    self::updateVendorData($set, $state);
                                    self::calculatePrices($get, $set);
                                }
                            })
                            ->columnSpan([
                                'md' => 5,
                            ]),

                        Forms\Components\TextInput::make('harga_publish')
                            ->label('Published Price')
                            ->prefix('Rp')
                            ->numeric()
                            ->reactive()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                self::calculatePrices($get, $set);
                            }),

                        Forms\Components\TextInput::make('quantity')
                            ->label('Quantity')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                self::calculatePrices($get, $set);
                            }),

                        Forms\Components\TextInput::make('price_public')
                            ->label('Public Price')
                            ->prefix('Rp')
                            ->disabled()
                            ->numeric()
                            ->reactive()
                            ->dehydrated()
                            ->mask(RawJs::make('$money($input)'))
                            ->helperText('Published price × quantity'),

                        Forms\Components\TextInput::make('harga_vendor')
                            ->label('Vendor Price')
                            ->prefix('Rp')
                            ->numeric()
                            ->reactive()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                self::calculatePrices($get, $set);
                            }),

                        Forms\Components\RichEditor::make('description')
                            ->label('Additional Notes')
                            ->columnSpanFull(),
                    ]),
            ])
            ->extraItemActions([
                Action::make('openVendor')
                    ->label('Open Vendor')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->color('info')
                    ->url(function (array $arguments, Repeater $component): ?string {
                        $itemData = $component->getRawItemState($arguments['item']);
                        $vendorId = $itemData['vendor_id'] ?? null;
                        if (!$vendorId) {
                            return null;
                        }
                        $vendor = Vendor::find($vendorId);
                        return $vendor ? VendorResource::getUrl('edit', ['record' => $vendor]) : null;
                    }, shouldOpenInNewTab: true)
                    ->hidden(fn (array $arguments, Repeater $component): bool => blank($component->getRawItemState($arguments['item'])['vendor_id'] ?? null)),
            ])
            ->defaultItems(1)
            ->collapsed()
            ->itemLabel(fn (array $state): ?string => 
                $state['vendor_id'] 
                    ? Vendor::find($state['vendor_id'])?->name ?? 'Unnamed Vendor'
                    : 'New Facility'
            )
            ->reorderable()
            ->cloneable()
            ->reactive()
            ->live()
            ->afterStateUpdated(function (Get $get, Set $set) {
                // $get is relative to the repeater's parent (the Tab)
                // $state (third argument, if defined) would be the array of items.
                // Let's get items directly using $get('items') if $state is not used.
                $itemsArray = $get('items') ?? []; // 'items' is the repeater name, $get('items') gets its state.

                // Calculate total product price from vendor items
                $totalProductPrice = collect($itemsArray)
                    ->sum(function ($item) {
                        $pricePublicStr = $item['price_public'] ?? '0';
                        if (!is_string($pricePublicStr) && !is_numeric($pricePublicStr)) {
                            $pricePublicStr = '0';
                        }
                        return (float) preg_replace('/[^0-9.]/', '', (string) $pricePublicStr);
                    });

                $set('product_price', $totalProductPrice); // Sets 'product_price' field in the same Tab

                // Calculate total vendor price from vendor items' harga_vendor
                $totalVendorPrice = collect($itemsArray)
                    ->sum(function ($item) {
                        $hargaVendorStr = $item['harga_vendor'] ?? '0';
                        // Ensure harga_vendor exists and is a string/numeric before trying to replace
                        if (!is_string($hargaVendorStr) && !is_numeric($hargaVendorStr)) {
                            $hargaVendorStr = '0';
                        }
                        return (float) preg_replace('/[^0-9.]/', '', (string) $hargaVendorStr);
                    });

                $set('vendorTotal', $totalVendorPrice); // Sets 'vendorTotal' field in the same Tab

                // Now, update the final product price.
                $penguranganVal = (float) preg_replace('/[^0-9.]/', '', $get('../pengurangan') ?? '0'); // Get 'pengurangan' from other Tab
                $finalPrice = $totalProductPrice - $penguranganVal;
                $set('../price', $finalPrice); // Set 'price' in the "Basic Information" Tab
            })
            ->columns(1);
    }

    protected static function updateVendorData(Set $set, $vendorId): void
    {
        $vendor = Vendor::find($vendorId);
        if ($vendor) {
            $set('harga_publish', $vendor->harga_publish);
            $set('harga_vendor', $vendor->harga_vendor);
            $set('description', $vendor->description);
        }
    }

    protected static function calculatePrices(Get $get, Set $set): void
    {
        // Get base values
        $harga_publish = (float)(preg_replace('/[^0-9.]/', '', $get('harga_publish') ?? 0));
        $quantity = (int)($get('quantity') ?? 1);
        // Calculate price_public (harga_publish * quantity)
        $price_public = $harga_publish * $quantity;
        $set('price_public', $price_public);

        // Update the total product price
        self::calculateTotalProductPrice($get, $set);
    }

    /**
     * Calculate the total product_price from all vendor items and update the final product price.
     * Triggered by changes in the vendor repeater.
     */
    protected static function calculateTotalProductPrice(Get $get, Set $set): void
    {
        // Get all items from the repeater
        $items = $get('../../items') ?? [];

        // Calculate total price from all items' price_public values
        $total_price = collect($items)
            ->sum(function ($item) {
                // Ensure price_public exists and is a string before trying to replace
                $pricePublicStr = $item['price_public'] ?? '0';
                if (!is_string($pricePublicStr) && !is_numeric($pricePublicStr)) {
                    $pricePublicStr = '0';
                }
                return (float)preg_replace('/[^0-9.]/', '', (string)$pricePublicStr);
            });

        // Set the overall product price and total_price for each item
        $set('../../product_price', $total_price);
        
        // Update total_price for each vendor item (ProductVendor.total_price)
        // This field is intended to store the aggregate product price this item was part of.
        if (is_array($items)) {
            foreach (array_keys($items) as $key) {
                 if (is_string($key) || is_int($key)) {
                    $set("../../items.{$key}.total_price", $total_price);
                }
            }
        }
        self::updateFinalProductPrice($get, $set);
    }

    /**
     * Updates the final sell price (Product.price) based on Product.product_price and Product.pengurangan.
     * Called when Product.product_price (from vendors) or Product.pengurangan (from discounts) changes.
     */
    protected static function updateFinalProductPrice(Get $get, Set $set): void
    {
        // Paths are relative to the repeater item context that initiated the chain of updates.
        $productPriceFromVendors = (float)preg_replace('/[^0-9.]/', '', $get('../../product_price') ?? '0');
        $totalPenguranganFromDiscounts = (float)preg_replace('/[^0-9.]/', '', $get('../../pengurangan') ?? '0');

        $finalPrice = $productPriceFromVendors - $totalPenguranganFromDiscounts;
        $set('../../price', $finalPrice); // Sets Product.price (in Basic Information tab)
    }

    protected static function getDiscountRepeater()
    {
        return Forms\Components\Repeater::make('itemsPengurangan')
            ->relationship()
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\TextInput::make('description')
                            ->label('Nama Vendor')
                            ->required()
                            ->columnSpan(3), // Full span for description
                        
                        Forms\Components\TextInput::make('amount')
                            ->label('Discount Value')
                            ->numeric()
                            ->required()
                            ->prefix('Rp')      // Always 'Rp' as it's a fixed amount
                            ->mask(RawJs::make('$money($input)')) // Always money mask
                            ->stripCharacters(',') // Always strip comma
                            ->rules(['min:0'])     // Simple min:0 rule
                            ->columnSpan(3), // Adjusted column span
                        
                        Forms\Components\RichEditor::make('notes')
                            ->label('Keterangan')
                            ->columnSpanFull(),
                    ]),
            ])
            ->defaultItems(0)
            ->collapsed()
            ->itemLabel(fn (array $state): ?string => 
                $state['description'] ?? 'New Discount Item'
            )
            ->reorderable()
            ->cloneable()
            // ->reactive() 
            // ->live()
            ->afterStateUpdated(function (Get $get, Set $set, $state) { // $state is the array of itemsPengurangan
                // $get is relative to the repeater's parent (the "Pengurangan Harga" Tab)
                $totalPengurangan = collect($state)
                    ->sum(function ($item) {
                        $amountStr = $item['amount'] ?? '0';
                        if (!is_string($amountStr) && !is_numeric($amountStr)) {
                            $amountStr = '0';
                        }
                        return (float) preg_replace('/[^0-9.]/', '', (string) $amountStr);
                    });

                // Set the 'pengurangan' field in the current Tab ("Pengurangan Harga")
                $set('pengurangan', $totalPengurangan);

                // Now, calculate and set the final 'price' field (in "Basic Information" Tab)
                $productPriceVal = (float) preg_replace('/[^0-9.]/', '', $get('../product_price') ?? '0'); // Get 'product_price' from other Tab
                $finalPrice = $productPriceVal - $totalPengurangan;
                $set('../price', $finalPrice); // Set 'price' in other Tab
            })
            ->addActionLabel('Add Discount')
            ->columns(1);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Data Produk yang telah dibuat dan dikelola';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
            'view' => Pages\ViewProduct::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount([
                'orders as unique_orders_count'
            ])
            // Bonus: Ini juga akan mengaktifkan kolom 'Total Sold'
            ->withSum('orderItems as total_quantity_sold', 'quantity');
    }

    // Ensure these lifecycle hooks call the server-side recalculation method
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return static::mutateFormDataBeforeSave($data);
    }

    protected function mutateFormDataBeforeUpdate(array $data): array
    {
        // Preserve existing image if not changed
        // This logic might need adjustment based on how FileUpload handles empty states
        // For now, we assume $data will not contain 'image' if it's not being updated.
        return static::mutateFormDataBeforeSave($data);
    }

    /**
     * Mutate form data before saving (both create and update).
     * This method recalculates product_price, pengurangan, and price on the server-side
     * based on the submitted repeater data to ensure data integrity.
     *
     * @param  array  $data
     * @return array
     */
    protected static function mutateFormDataBeforeSave(array $data): array
    {
        // Helper function to clean currency string values and convert to float
        $cleanCurrencyValue = function ($value): float {
            if ($value === null) {
                return 0.0;
            }
            // Remove all characters except digits and a period, then cast to float
            return (float) preg_replace('/[^0-9.]/', '', (string) $value);
        };

        // 1. Recalculate 'product_price' from 'items' (vendor repeater)
        $calculatedProductPrice = 0;
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                // 'price_public' is 'harga_publish' * 'quantity' for each vendor item
                $calculatedProductPrice += $cleanCurrencyValue($item['price_public'] ?? '0');
            }
        }
        $data['product_price'] = $calculatedProductPrice;

        // 2. Recalculate 'pengurangan' from 'itemsPengurangan' (discount repeater)
        $calculatedPengurangan = 0;
        if (isset($data['itemsPengurangan']) && is_array($data['itemsPengurangan'])) {
            foreach ($data['itemsPengurangan'] as $item) {
                $calculatedPengurangan += $cleanCurrencyValue($item['amount'] ?? '0');
            }
        }
        $data['pengurangan'] = $calculatedPengurangan;

        // 3. Recalculate final 'price'
        $data['price'] = $data['product_price'] - $data['pengurangan'];

        return $data;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Tabs::make('Product Details')
                    ->tabs([
                        Infolists\Components\Tabs\Tab::make('Basic Information')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Infolists\Components\Section::make('Product Name')
                                    ->schema([
                                        Infolists\Components\Grid::make(3)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('name')
                                                    ->placeholder('-'),
                                                Infolists\Components\TextEntry::make('pax')
                                                    ->label('Capacity (pax)')
                                                    ->suffix(' people')
                                                    ->placeholder('-'),
                                                Infolists\Components\TextEntry::make('stock')
                                                    ->weight('bold')
                                                    ->suffix(' units')
                                                    ->color(fn (string $state): string => $state > 0 ? 'primary' : 'danger'),
                                        ])
                                ]),
                                Infolists\Components\Section::make('Facilities')
                                    ->schema([
                                        Infolists\Components\Grid::make(2)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('product_price')
                                                    ->label('Total Publish Price')
                                                    ->weight('bold')
                                                    ->color('primary')
                                                    ->prefix('Rp ')
                                                    ->numeric()
                                                    ->helperText('Total Harga Publish Vendor')
                                                    ->placeholder('-'),
                                                Infolists\Components\TextEntry::make('pengurangan')
                                                    ->label('Pengurangan Harga')
                                                    ->weight('bold')
                                                    ->prefix('Rp ')
                                                    ->numeric()
                                                    ->helperText('Total Pengurangan')
                                                    ->color('danger')
                                                    ->placeholder('-'),
                                            ])
                                    ]),
                                Infolists\Components\Section::make('Product Status')
                                    ->schema([
                                        Infolists\Components\Grid::make(2)
                                            ->schema([
                                                Infolists\Components\IconEntry::make('is_active')
                                                    ->label('Product Status')
                                                    ->boolean(),
                                                Infolists\Components\IconEntry::make('is_approved')
                                                    ->label('Approval Status')
                                                    ->boolean()
                                                    ->visible(fn () => Auth::user()->hasRole('super_admin')),
                                        ])
                                    ])
                                    ->collapsible(),
                            ]),

                        Infolists\Components\Tabs\Tab::make('Facilities & Vendors')
                            ->icon('heroicon-o-cube')
                            ->schema([
                                Infolists\Components\Section::make()
                                    ->schema([
                                        Infolists\Components\TextEntry::make('product_price')
                                            ->label('Total Publish Price')
                                            ->weight('bold')
                                            ->color('primary') // Warna untuk total harga vendor
                                            ->prefix('Rp ')
                                                    ->numeric()
                                            ->helperText('Sum of all vendor prices'),
                                        Infolists\Components\TextEntry::make('calculatedPriceVendor')
                                            ->label('Total Vendor Cost')
                                            ->weight('bold')
                                            ->color('warning')
                                            ->prefix('Rp ')
                                            ->numeric()
                                            ->helperText('Sum of all vendor prices')
                                            ->state(function (Product $record): float {
                                                return $record->items->sum(function ($item) {
                                                    // Access the accessor: $item->harga_vendor * $item->quantity
                                                    return $item->harga_vendor;
                                                });
                                            }),
                                    ]),
                                Infolists\Components\Section::make()
                                    ->schema([
                                        Infolists\Components\RepeatableEntry::make('items')
                                            ->schema([
                                                Infolists\Components\TextEntry::make('vendor.name')
                                                    ->label('Vendor Name')
                                                    ->placeholder('-'),
                                                Infolists\Components\TextEntry::make('harga_publish')
                                                    ->label('Published Price')
                                                    ->weight('bold')
                                                    ->color('info')
                                                    ->prefix('Rp ')
                                                    ->numeric()
                                                    ->placeholder('-'),
                                                Infolists\Components\TextEntry::make('quantity')
                                                    ->placeholder('-')
                                                    ->color('gray'),
                                                Infolists\Components\TextEntry::make('price_public')
                                                    ->label('Calculated Public Price')
                                                    ->weight('bold')
                                                    ->color('primary')
                                                    ->prefix('Rp ')
                                                    ->numeric()
                                                    ->placeholder('-'),
                                                Infolists\Components\TextEntry::make('harga_vendor')
                                                    ->label('Vendor Unit Cost')
                                                    ->weight('bold')
                                                    ->color('warning')
                                                    ->prefix('Rp ')
                                                    ->numeric()
                                                    ->placeholder('-'),
                                                Infolists\Components\TextEntry::make('calculated_price_vendor')
                                                    ->label('Calculated Vendor Cost')
                                                    ->weight('bold')
                                                    ->color('warning')
                                                    ->prefix('Rp ')
                                                    ->numeric()
                                                    ->placeholder('-'), // Will use ProductVendor's accessor
                                                Infolists\Components\TextEntry::make('description')
                                                    ->label('Fasilitas')
                                                    ->columnSpanFull()
                                                    ->html()
                                                    ->placeholder('Keterangan Fasilitas'),
                                    ])
                                    ->columns(4) // Adjusted columns due to new entry
                                    ->grid(1)
                                    ->contained(true),
                                    ])
                            ]),

                        Infolists\Components\Tabs\Tab::make('Pengurangan Harga')
                            ->icon('heroicon-o-receipt-refund')
                            ->label('Pengurangan Harga (Jika Ada)')
                            ->schema([
                                Infolists\Components\TextEntry::make('pengurangan')
                                    ->label('Total Pengurangan')
                                    ->color('danger') // Warna untuk total pengurangan
                                    ->weight('bold')
                                    ->prefix('Rp ')
                                    ->numeric()
                                    ->placeholder('-')
                                    ->state(function (Product $record): float {
                                        // Jika 'pengurangan' adalah kolom di tabel Product
                                        // return $record->pengurangan ?? 0;
                                        // Jika 'pengurangan' dihitung dari relasi itemsPengurangan
                                        return $record->itemsPengurangan()->sum('amount');
                                    })
                                    ->helperText('Sum of all discount items'),
                                Infolists\Components\RepeatableEntry::make('itemsPengurangan')
                                    ->label('Discount Items')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('description')
                                            ->label('Description')
                                            ->placeholder('-')
                                            ->columnSpanFull(),
                                        Infolists\Components\TextEntry::make('amount')
                                            ->label('Discount Value')
                                            ->color('warning') // Warna untuk nilai diskon
                                            ->weight('bold')
                                            ->prefix('Rp ')
                                            ->numeric()
                                            ->placeholder('-')
                                            ->placeholder('-'),
                                        Infolists\Components\TextEntry::make('notes')
                                            ->label('Notes')
                                            ->columnSpanFull()
                                            ->html()
                                            ->placeholder('No notes.'),
                                    ])
                                    ->columns(2)
                                    ->grid(1)
                                    ->contained(true),
                            ]),
                        Infolists\Components\Tabs\Tab::make('Timestamps')
                            ->icon('heroicon-o-clock')
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Created On')
                                    ->dateTime(),
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->dateTime(),
                                Infolists\Components\TextEntry::make('user.name') // Jika ada relasi user
                                    ->label('Created by')
                                    ->placeholder('-')
                                    ->visible(fn (Product $record) => $record->user !== null),
                            ])->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
