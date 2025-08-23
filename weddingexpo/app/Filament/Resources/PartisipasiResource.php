<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartisipasiResource\Pages;
use App\Filament\Resources\PartisipasiResource\RelationManagers;
use App\Models\Partisipasi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PartisipasiResource extends Resource
{
    protected static ?string $model = Partisipasi::class;
    protected static ?string $navigationIcon = 'heroicon-o-hand-raised';
    protected static ?string $navigationLabel = 'Partisipasi Expo';
    protected static ?int $navigationSort = 2; 


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('PartisipasiTabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Data Partisipasi')
                            ->schema([
                                Forms\Components\Placeholder::make('info_partisipasi')
                                    ->content('Isi data partisipasi expo dan vendor di bawah ini.'),
                                Forms\Components\Select::make('expo_id')
                                    ->label('Expo')
                                    ->options(function () {
                                        return \App\Models\Expo::query()
                                            ->select('id', 'nama_expo', 'periode')
                                            ->get()
                                            ->mapWithKeys(function ($item) {
                                                return [
                                                    $item->id => $item->nama_expo . ' (' . $item->periode . ')'
                                                ];
                                            });
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->placeholder('Pilih Expo'),
                                Forms\Components\Select::make('vendor_id')
                                    ->label('Vendor')
                                    ->relationship('vendor', 'nama_vendor')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->placeholder('Pilih Vendor'),
                                Forms\Components\TextInput::make('vendor_pendamping')
                                    ->label('Vendor Pendamping')
                                    ->maxLength(255)
                                    ->placeholder('Contoh: Vendor A, Vendor B, Vendor C')
                                    ->helperText('Opsional, jika ada vendor pendamping untuk partisipasi ini.'),
                                Forms\Components\DatePicker::make('tanggal_booking')
                                    ->label('Tanggal Booking')
                                    ->required()
                                    ->displayFormat('d M Y'),
                                Forms\Components\Select::make('category_tenant_id')
                                    ->label('Kategori Tenant')
                                    ->relationship('categoryTenant', 'category')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->placeholder('Pilih Kategori Tenant')
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $hargaJual = 0;
                                        if ($state) {
                                            $tenant = \App\Models\CategoryTenant::find($state);
                                            $hargaJual = $tenant?->harga_jual ?? 0;
                                            $set('harga_jual', $hargaJual);
                                        } else {
                                            $set('harga_jual', null);
                                        }
                                        self::updateStatusPembayaran($get, $set);
                                    }),
                                Forms\Components\TextInput::make('blok_tenant')
                                    ->label('Blok Tenant')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Contoh: A1'),
                                Forms\Components\TextInput::make('harga_jual')
                                    ->label('Harga Jual')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->readOnly()
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->prefix('Rp')
                                    ->placeholder('Contoh: 1.000.000'),
                                Forms\Components\Select::make('status_pembayaran')
                                    ->label('Status Pembayaran')
                                    ->options([
                                        'Belum Lunas' => 'Belum Lunas',
                                        'Lunas' => 'Lunas',
                                    ])
                                    ->required()
                                    ->placeholder('Pilih status pembayaran')
                                    ->disabled()
                                    ->dehydrated(true),
                                    ]),
                        Forms\Components\Tabs\Tab::make('Data Pembayaran')
                            ->schema([
                                Forms\Components\Placeholder::make('info_pembayaran')
                                    ->content('Input detail pembayaran yang terkait dengan partisipasi ini.'),
                                Forms\Components\TextInput::make('tot_nominal')
                                    ->label('Total Dibayar')
                                    ->readOnly()
                                    ->default(fn ($record) => $record?->tot_nominal ?? 0)
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->prefix('Rp')
                                    ->placeholder('Contoh: 1.000.000'),
                                Forms\Components\Repeater::make('dataPembayarans')
                                    ->label('Data Pembayaran')
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\TextInput::make('nama_pembayar')
                                            ->label('Nama Pembayar')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('nominal')
                                            ->label('Nominal')
                                            ->required()
                                            ->mask(RawJs::make('$money($input)'))
                                            ->stripCharacters(',')
                                            ->numeric()
                                            ->prefix('Rp'),
                                        Forms\Components\DatePicker::make('tanggal_bayar')
                                            ->label('Tanggal Bayar')
                                            ->required()
                                            ->displayFormat('d M Y'),
                                        Forms\Components\Select::make('metode_pembayaran')
                                            ->label('Metode Pembayaran')
                                            ->options([
                                                'Transfer Bank' => 'Transfer Bank',
                                                'Cash' => 'Cash',
                                            ])
                                            ->required(),
                                        Forms\Components\FileUpload::make('bukti_transfer')
                                            ->label('Bukti Transfer')
                                            ->required()
                                            ->directory('bukti-transfer')
                                            ->image()
                                            ->openable(),
                                        Forms\Components\Select::make('rekening_tujuan_id')
                                            ->label('Rekening Tujuan')
                                            ->options(function () {
                                                return \App\Models\RekeningTujuan::query()
                                                    ->select('id', 'nama_bank', 'nomor_rekening', 'nama_pemilik')
                                                    ->get()
                                                    ->mapWithKeys(function ($item) {
                                                        return [
                                                            $item->id => $item->nama_bank . ' - ' . $item->nomor_rekening . ' a.n. ' . $item->nama_pemilik
                                                        ];
                                                    });
                                            })
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->placeholder('Pilih rekening tujuan'),
                                        Forms\Components\Textarea::make('keterangan')
                                            ->label('Keterangan'),
                                    ])
                                    ->addActionLabel('Tambah Pembayaran')
                                    ->minItems(1)
                                    ->reorderable()
                                    ->itemLabel(fn (array $state): ?string => $state['keterangan'] ?? null)
                                    ->reorderableWithButtons()
                                    ->collapsible()
                                    ->defaultItems(1)
                                    ->live()
                                    ->grid(2)
                                    ->afterStateUpdated(fn ($get, $set) => self::updateStatusPembayaran($get, $set))
                            ])
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('expo.nama_expo')
                    ->label('Expo')
                    ->searchable()
                    ->description(fn ($record) => $record->expo?->periode)
                    ->sortable(),
                Tables\Columns\TextColumn::make('vendor.nama_vendor')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('blok_tenant')
                    ->label('Blok Tenant')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_booking')
                    ->label('Tanggal Booking')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status_pembayaran')
                    ->label('Status Pembayaran')
                    ->badge(),
                Tables\Columns\TextColumn::make('tot_nominal')
                    ->label('Total Nominal')
                    ->getStateUsing(fn ($record) => $record->tot_nominal)
                    ->prefix('Rp. ')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Dihapus')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_pembayaran')
                    ->label('Status Pembayaran')
                    ->options([
                        'Belum Lunas' => 'Belum Lunas',
                        'Lunas' => 'Lunas',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListPartisipasis::route('/'),
            'create' => Pages\CreatePartisipasi::route('/create'),
            'edit' => Pages\EditPartisipasi::route('/{record}/edit'),
        ];
    }

    public static function updateStatusPembayaran(callable $get, callable $set): void
    {
        $pembayaranState = $get('dataPembayarans');
        $totalBayar = collect($pembayaranState)->sum(fn ($item) => (int) str_replace(',', '', $item['nominal'] ?? 0));
        $set('tot_nominal', $totalBayar);

        $hargaJual = (int) str_replace(',', '', $get('harga_jual') ?? 0);
        $set('status_pembayaran', $totalBayar < $hargaJual ? 'Belum Lunas' : 'Lunas');
    }
}
