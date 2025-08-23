<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SimulasiProdukResource\Pages;
use App\Models\Product;
use App\Models\Prospect;
use App\Models\SimulasiProduk;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Wizard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

class SimulasiProdukResource extends Resource
{
    protected static ?string $model = SimulasiProduk::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Shop';
    protected static ?string $navigationLabel = 'Simulasi';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Wizard::make([
                Wizard\Step::make('Simulation Details')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Forms\Components\Select::make('prospect_id')
                            ->relationship(
                                name: 'prospect',
                                titleAttribute: 'name_event',
                                modifyQueryUsing: fn(Builder $query) => $query->whereDoesntHave('orders', function (Builder $orderQuery) {
                                    $orderQuery->whereNotNull('status'); // Hanya prospek yang TIDAK memiliki order dengan status apapun
                                }),
                            )
                            ->label('Select Prospect (for Simulation Name & Slug)')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if ($state) {
                                    $prospect = Prospect::find($state);
                                    if ($prospect && isset($prospect->name_event)) {
                                        $set('name', $prospect->name_event); // Set the hidden 'name' field
                                        $set('slug', Str::slug($prospect->name_event));
                                    } else {
                                        $set('name', null);
                                        $set('slug', null);
                                    }
                                } else {
                                    $set('name', null);
                                    $set('slug', null);
                                }
                            })
                            ->columnSpanFull(),
                        Forms\Components\Hidden::make('name')->dehydrated(), // To store the name derived from prospect
                        Forms\Components\TextInput::make('slug')->required()->maxLength(255)->disabled()->dehydrated()->unique(SimulasiProduk::class, 'slug', ignoreRecord: true),
                        Forms\Components\Select::make('user_id')->relationship('user', 'name')->label('Created By')->required()->searchable()->disabled()->preload()->default(fn() => Auth::id())->dehydrated(),
                        Forms\Components\RichEditor::make('notes')->columnSpanFull(),
                    ])
                    ->columns(2),
                Wizard\Step::make('Product & Pricing')
                    ->icon('heroicon-o-shopping-bag')
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->relationship('product', 'name')
                            ->label('Select Base Product')
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                $new_total_price = 0;
                                if ($state) {
                                    $product = Product::find($state);
                                    if ($product) {
                                        $new_total_price = $product->price ?? 0;
                                    }
                                }
                                $set('total_price', $new_total_price);
                                self::recalculateGrandTotal($get, $set, '../'); // Path to Summary step from Product step
                            })
                            ->columnSpanFull()
                            ->suffixAction(
                                Action::make('openSelectedProduct')
                                    ->icon('heroicon-m-arrow-top-right-on-square')
                                    ->tooltip('Open selected product in new tab')
                                    ->url(function (Get $get): ?string {
                                        $productId = $get('product_id');
                                        if (!$productId) {
                                            return null;
                                        }
                                        $product = Product::find($productId);
                                        return $product ? ProductResource::getUrl('edit', ['record' => $product]) : null;
                                    }, shouldOpenInNewTab: true)
                                    ->hidden(fn (Get $get): bool => blank($get('product_id'))))
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('total_price')
                            ->label('Base Total Price')
                            ->numeric()
                            ->prefix('Rp')
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->readOnly()
                            ->dehydrated()
                            ->default(0)
                            ->reactive()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::recalculateGrandTotal($get, $set, '../'); // Path to Summary step
                            })
                            ->helperText('Price from selected base product. Adjustments below.')
                    ])
                    , // End of Product & Pricing Step's schema
                Wizard\Step::make('Modification History')
                        ->icon('heroicon-o-clock')
                        ->description('Record modification details')
                        ->schema([
                            Forms\Components\Placeholder::make('created_at')
                                ->label('Created at')
                                ->content(fn (SimulasiProduk $record): ?string => $record->created_at?->diffForHumans()),

                            Forms\Components\Placeholder::make('updated_at')
                                ->label('Last modified at')
                                ->content(fn (SimulasiProduk $record): ?string => $record->updated_at?->diffForHumans()),
                            Forms\Components\Placeholder::make('last_edited_by')
                                ->label('Last Edited By')
                                // This currently shows the creator. For actual last editor, an 'updated_by_user_id' field and relationship would be needed.
                                ->content(fn (SimulasiProduk $record): ?string => $record->user?->name ?? '-'),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn (?SimulasiProduk $record) => $record === null),
            ])
            ->columnSpan('full')
            ->columns(3)
            ->skippable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc') // Tambahkan baris ini
            ->poll('5s') // refresh data setiap 3 detik
            ->columns([
                Tables\Columns\TextColumn::make('prospect.name_event')
                    ->label('Prospect Name')
                    ->searchable()->sortable()
                    ->formatStateUsing(fn (string $state): string => \Illuminate\Support\Str::title($state))
                    ->description(fn(SimulasiProduk $record): string => $record->product ? 'Based on: ' . $record
                    ->product->name : Str::limit($record->notes ?? '', 30)),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Base Price')
                    ->money('IDR')
                    ->sortable()
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('promo')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('penambahan')
                    ->label('Addition')
                    ->money('IDR')->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('pengurangan')
                    ->label('Reduction')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)->alignEnd(),
                Tables\Columns\TextColumn::make('grand_total')
                    ->money('IDR')
                    ->sortable()
                    ->alignEnd()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Created By')
                    ->searchable()
                    ->sortable(),
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
            ->actions([Tables\Actions\ActionGroup::make([
                Tables\Actions\EditAction::make(), 
                Tables\Actions\Action::make('view_simulasi')
                    ->label('View Simulasi')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->url(fn(SimulasiProduk $record) => route('simulasi.show', $record))
                    ->openUrlInNewTab(), 
                Tables\Actions\DeleteAction::make()])
                ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()])
                ])
                ->emptyStateHeading('No Simulations Found')
                ->emptyStateDescription('Create your first simulation to get started.')
                ->emptyStateIcon('heroicon-o-calculator')
                ->emptyStateActions([
                    Tables\Actions\Action::make('create')
                        ->label('Create Simulation')
                        ->icon('heroicon-m-plus')
                        ->url(route('filament.admin.resources.simulasi-produks.create'))
                        ->button(),
                ])
                ->defaultPaginationPageOption(10)
                ->paginationPageOptions([10, 25, 50])
                ->poll('60s'); // Refresh data every 60 seconds
    }

    protected static function recalculateGrandTotal(Get $get, Set $set, string $basePath = ''): void
    {
        $total_price = floatval(str_replace(',', '', $get($basePath . 'total_price') ?? '0'));
        $promo = floatval(str_replace(',', '', $get($basePath . 'promo') ?? '0'));
        $penambahan = floatval(str_replace(',', '', $get($basePath . 'penambahan') ?? '0'));
        $pengurangan = floatval(str_replace(',', '', $get($basePath . 'pengurangan') ?? '0'));

        $grand_total = $total_price + $penambahan - $promo - $pengurangan;
        $set($basePath . 'grand_total', $grand_total);
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
            'index' => Pages\ListSimulasiProduks::route('/'),
            'create' => Pages\CreateSimulasiProduk::route('/create'),
            'edit' => Pages\EditSimulasiProduk::route('/{record}/edit'),
            'invoice' => Pages\ViewSimulasiInvoice::route('/{record}/invoice'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // Example: If you want to scope simulations to the logged-in user (and admins see all)
        // if (!auth()->user()->hasRole('super_admin')) {
        //     return parent::getEloquentQuery()->where('user_id', auth()->id());
        // }
        return parent::getEloquentQuery();
    }
}
