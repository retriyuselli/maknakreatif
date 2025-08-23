<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengeluaranResource\Pages;
use App\Filament\Resources\PengeluaranResource\RelationManagers;
use App\Models\Expo;
use App\Models\Pengeluaran;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PengeluaranResource extends Resource
{
    protected static ?string $model = Pengeluaran::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Pengeluaran';
    protected static ?string $navigationLabel = 'Pengeluaran Expo';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('expo_id')
                    ->label('Expo')
                    ->options(function () {
                        return Expo::query()
                            ->where('status', true)
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
                Forms\Components\TextInput::make('nama_pengeluaran')
                    ->label('Nama Pengeluaran')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Contoh: Sewa Booth'),
                Forms\Components\TextInput::make('nominal')
                    ->label('Nominal')
                    ->required()
                    ->stripCharacters(',')
                    ->prefix('Rp ')
                    ->numeric()
                    ->mask(RawJs::make('$money($input)'))
                    ->placeholder('Contoh: 1.000.000'),
                Forms\Components\DatePicker::make('tanggal')
                    ->label('Tanggal Pengeluaran')
                    ->required()
                    ->displayFormat('d M Y'),
                
                Forms\Components\FileUpload::make('bukti_transfer')
                    ->label('Bukti Transfer')
                
                    ->image()
                    ->directory('pengeluaran_lain/bukti_transfer')
                    ->visibility('public')
                    ->maxSize(1024) // 1 MB
                    ->acceptedFileTypes(['image/*', 'application/pdf']),
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
                    ->label('Keterangan')
                    ->columnSpanFull(),
                Forms\Components\Hidden::make('user_id')
                    ->default(fn () => auth()->id()),
                ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('expo.nama_expo')
                    ->label('Expo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expo.periode')
                    ->label('Periode')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_pengeluaran')
                    ->label('Nama Pengeluaran')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nominal')
                    ->label('Nominal')
                    ->money('IDR', true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User Input')
                    ->searchable()
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
                // Tambahkan filter jika diperlukan
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
            'index' => Pages\ListPengeluarans::route('/'),
            'create' => Pages\CreatePengeluaran::route('/create'),
            'edit' => Pages\EditPengeluaran::route('/{record}/edit'),
        ];
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }      
}
