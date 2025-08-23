<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Filament\Resources\DataPembayaranResource\Pages; // Pastikan ini ada
use App\Filament\Resources\DataPembayaranResource\Widgets\DataPembayaranOverview;
use App\Filament\Resources\DataPembayaranResource\Widgets\DataPembayaranStatsOverview;
use App\Models\DataPembayaran;
use App\Models\Order;
use Dflydev\DotAccessData\Data;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Clusters\Pendapatan;

// use App\Filament\Widgets\DataPembayaranStatsOverview;

class DataPembayaranResource extends Resource
{
    protected static ?string $model = DataPembayaran::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $recordTitleAttribute = 'keterangan';
    protected static ?string $navigationLabel = 'Pendapatan Wedding';
    protected static ?string $cluster = Pendapatan::class;


    public static function form(Form $form): Form
    {
    return $form
        ->schema([
            Forms\Components\Select::make('order_id')
                ->relationship('order', 'name')
                ->searchable()
                ->disabled()
                ->preload()
                ->required()
                ->afterStateUpdated(function ($state, callable $set) {
                    if ($state) {
                        $order = Order::find($state);
                        $set('nominal', $order?->sisa ?? 0);
                    }
                }),
                
            Forms\Components\TextInput::make('keterangan')
                ->label('Keterangan')
                ->disabled()
                ->prefix('Pembayaran')
                ->placeholder('1, 2, 3 dst'),

            Forms\Components\TextInput::make('nominal')
                ->label('Amount')
                ->disabled()
                ->readOnly()
                ->numeric()
                ->mask(RawJs::make('$money($input)'))
                ->stripCharacters(',')
                ->prefix('Rp. ')
                ->required()
                ->minValue(0)
                ->rules(['max:999999999'])
                ->columnSpan(['md' => 1]),
            
            Forms\Components\Select::make('kategori_transaksi')
                ->options([
                    'uang_masuk' => 'Uang Masuk',
                    'uang_keluar' => 'Uang Keluar',
                ])
                ->default('uang_masuk')
                ->disabled()
                ->label('Tipe Transaksi')
                ->required(),

            Forms\Components\Select::make('payment_method_id')
                ->relationship('paymentMethod', 'name')
                ->required()
                ->searchable()
                ->disabled()
                ->preload(),

            Forms\Components\DatePicker::make('tgl_bayar')
                ->label('Payment Date')
                ->required()
                ->disabled(),

            Forms\Components\FileUpload::make('image')
                ->label('Payment Proof')
                ->disabled()
                ->image()
                ->maxSize(1280)
                ->directory('payment-proofs/' . date('Y/m'))
                ->visibility('private')
                ->downloadable()
                ->openable() // Keep openable for both image and PDF
                ->acceptedFileTypes(['image/jpeg', 'image/png'])
                ->helperText('Max 1MB. JPG or PNG only.')
        ])->columns(3);
    }


    public static function table(Table $table): Table
    {
    return $table
        ->modifyQueryUsing(fn (Builder $query) => $query->with(['paymentMethod', 'order']))
        ->columns([
            Tables\Columns\TextColumn::make('order.name')
                ->label('Order Number')
                ->searchable()
                ->label('Project')
                ->sortable()
                ->copyable(),
                
            Tables\Columns\TextColumn::make('tgl_bayar')
                ->label('Payment Date')
                ->date('d M Y')
                ->sortable(),

            Tables\Columns\TextColumn::make('paymentMethod.name')
                ->label('Payment Method')
                ->searchable()
                ->sortable(),
            
            Tables\Columns\TextColumn::make('nominal')
                ->label('Amount')
                ->formatStateUsing(fn (string $state): string => 'Rp. ' . number_format($state, 0, ',', '.'))
                ->summarize([
                    Tables\Columns\Summarizers\Sum::make()
                        ->formatStateUsing(fn (string $state): string => 'Rp. ' . number_format($state, 0, ',', '.')),
                ])
                ->sortable(),

            Tables\Columns\ImageColumn::make('image')
                ->label('Payment Proof')
                ->circular(false)
                ->sortable()
                ->square(),

            Tables\Columns\TextColumn::make('keterangan')
                ->label('Description')
                ->prefix('Pembayaran ')
                ->searchable()
                ->toggleable()
                ->wrap(),
        ])
        ->defaultSort('tgl_bayar', 'desc')
        ->filters([
            Tables\Filters\TrashedFilter::make(),
            Tables\Filters\SelectFilter::make('order_status')
                ->label('Order Status')
                ->options(OrderStatus::class) // Menggunakan Enum OrderStatus
                ->query(function (Builder $query, array $data): Builder {
                    if (blank($data['value'])) {
                        return $query;
                    }
                    return $query->whereHas('order', function (Builder $orderQuery) use ($data) {
                        $orderQuery->where('status', $data['value']);
                    });
                }),
            Tables\Filters\SelectFilter::make('payment_method')
                ->relationship('paymentMethod', 'name')
                ->preload()
                ->multiple()
                ->label('Payment Method'),

            Tables\Filters\Filter::make('date_range')
                ->form([
                    Forms\Components\DatePicker::make('from')
                        ->label('From Date'),
                    Forms\Components\DatePicker::make('until')
                        ->label('Until Date'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['from'],
                            fn (Builder $query, $date): Builder => 
                                $query->whereDate('tgl_bayar', '>=', $date),
                        )
                        ->when(
                            $data['until'],
                            fn (Builder $query, $date): Builder => 
                                $query->whereDate('tgl_bayar', '<=', $date),
                        );
                })
        ])
        ->filtersFormColumns(3)

        ->actions([
            Tables\Actions\ActionGroup::make([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
        ])
        
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
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

    public static function getGlobalSearchResultDetails(Model $record): array
    {
    return [
        'Order' => $record->order?->name,
        'Amount' => 'Rp. ' . number_format($record->nominal, 0, ',', '.'),
        'Date' => $record->tgl_bayar ? \Carbon\Carbon::parse($record->tgl_bayar)->format('d M Y') : '-',
    ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDataPembayarans::route('/'),
            'edit' => Pages\EditDataPembayaran::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            DataPembayaranStatsOverview::class,
            // Anda juga bisa menambahkan LatestDataPembayaranTableWidget::class di sini jika diinginkan
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Finance';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['keterangan', 'order.number', 'paymentMethod.name'];
    }

    public static function getNavigationBadge(): ?string
    {
    /** @var class-string<\App\Models\DataPembayaran> $model */
    $model = static::getModel();

        return cache()->remember('data_pembayaran_count', now()->addMinutes(5), function () use ($model) {
            return $model::query()
                ->whereNull('deleted_at')
                ->count();
        });
    }

    public static function getNavigationBadgeColor(): ?string
    {
    /** @var class-string<\App\Models\DataPembayaran> $model */
    $model = static::getModel();
    
    $count = cache()->remember('data_pembayaran_count', now()->addMinutes(5), function () use ($model) {
        return $model::query()
            ->whereNull('deleted_at')
            ->count();
    });

        return match (true) {
            $count > 10 => 'warning',
            $count > 0 => 'primary',
            default => 'secondary',
        };
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Pembayaran dari konsumen ke perusahaan sebagai DP dan pembayaran lanjutan';
    }
}
