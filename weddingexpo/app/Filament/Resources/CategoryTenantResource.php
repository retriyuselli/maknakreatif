<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryTenantResource\Pages;
use App\Filament\Resources\CategoryTenantResource\RelationManagers;
use App\Models\CategoryTenant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoryTenantResource extends Resource
{
    protected static ?string $model = CategoryTenant::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Kategori Tenant';
    protected static ?int $navigationSort = 4; 

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('expo_id')
                    ->label('Expo')
                    ->relationship('expo', 'nama_expo')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->placeholder('Pilih Expo'),
                Forms\Components\Select::make('category')
                    ->label('Kategori Tenant')
                    ->required()
                    ->options([
                        'Platinum' => 'Platinum',
                        'Gold' => 'Gold',
                    ])
                    ->placeholder('Pilih kategori tenant'),
                Forms\Components\TextInput::make('harga_jual')
                    ->label('Harga Jual')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('Rp'),
                Forms\Components\TextInput::make('harga_modal')
                    ->label('Harga Modal')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('Rp'),
                Forms\Components\TextInput::make('jumlah_unit')
                    ->label('Jumlah Unit')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\TextInput::make('ukuran')
                    ->label('Ukuran')
                    ->maxLength(255)
                    ->placeholder('Contoh: 3x3m'),
                Forms\Components\Textarea::make('deskripsi')
                    ->label('Deskripsi')
                    ->placeholder('Keterangan tambahan')
                    ->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'Aktif' => 'Aktif',
                        'Tidak Aktif' => 'Tidak Aktif',
                    ])
                    ->required()
                    ->default('Aktif'),
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
                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori Tenant')
                    ->searchable(),
                Tables\Columns\TextColumn::make('harga_jual')
                    ->label('Harga Jual')
                    ->money('IDR', true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('harga_modal')
                    ->label('Harga Modal')
                    ->money('IDR', true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('jumlah_unit')
                    ->label('Jumlah Unit')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ukuran')
                    ->label('Ukuran')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => $state === 'Aktif' ? 'success' : 'danger'),
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
                //
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
            'index' => Pages\ListCategoryTenants::route('/'),
            'create' => Pages\CreateCategoryTenant::route('/create'),
            'edit' => Pages\EditCategoryTenant::route('/{record}/edit'),
        ];
    }
}
