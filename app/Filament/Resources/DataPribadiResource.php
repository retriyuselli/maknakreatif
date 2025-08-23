<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DataPribadiResource\Pages;
use App\Filament\Resources\DataPribadiResource\RelationManagers;
use App\Models\DataPribadi;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Support\RawJs;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DataPribadiResource extends Resource
{
    protected static ?string $model = DataPribadi::class;
    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationGroup = 'Master'; // Grup navigasi
    protected static ?string $navigationLabel = 'Data Team'; // Label navigasi
    protected static ?string $recordTitleAttribute = 'nama_lengkap'; // Atribut untuk judul record

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Personal')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('nama_lengkap')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Masukkan nama lengkap'),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->placeholder('contoh@domain.com')
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('nomor_telepon')
                            ->tel()
                            ->prefix('+62')
                            ->placeholder('81234567890')
                            ->telRegex('/^[0-9]{9,15}$/')
                            ->maxLength(20),
                        Forms\Components\DatePicker::make('tanggal_lahir')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        Forms\Components\Select::make('jenis_kelamin')
                            ->options([
                                'Laki-laki' => 'Laki-laki',
                                'Perempuan' => 'Perempuan',
                            ])
                            ->placeholder('Pilih jenis kelamin'),
                        Forms\Components\FileUpload::make('foto')
                            ->image()
                            ->imageEditor()
                            ->maxSize(1024) // 1MB
                            ->columnSpanFull()
                            ->helperText('Unggah foto profil (maks. 1MB).'),
                        Forms\Components\Textarea::make('alamat')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Masukkan alamat lengkap'),
                    ]),
                Section::make('Informasi Pekerjaan')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('pekerjaan')
                            ->maxLength(255)
                            ->placeholder('Masukkan pekerjaan saat ini'),
                        Forms\Components\TextInput::make('gaji')
                            ->numeric()
                            ->prefix('Rp')
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->placeholder('0'),
                        Forms\Components\Textarea::make('motivasi_kerja')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Jelaskan motivasi kerja Anda'),
                        Forms\Components\RichEditor::make('pelatihan')
                            ->columnSpanFull()
                            ->placeholder('Pelatihan yang pernah diikuti di Makna'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('foto')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => $record->nama_lengkap ? "https://ui-avatars.com/api/?name=" . urlencode($record->nama_lengkap) . "&color=FFFFFF&background=0D83DD" : null),
                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->icon('heroicon-s-envelope'),
                Tables\Columns\TextColumn::make('nomor_telepon')
                    ->searchable()
                    ->prefix('+62')
                    ->icon('heroicon-s-phone'),
                Tables\Columns\TextColumn::make('tanggal_lahir')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jenis_kelamin')
                    ->badge()
                    ->colors([
                        'success' => 'Laki-laki',
                        'warning' => 'Perempuan',
                    ])
                    ->searchable(),
                Tables\Columns\TextColumn::make('pekerjaan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('gaji')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
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
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10, 25, 50])
            ->emptyStateIcon('heroicon-o-user-group')
            ->emptyStateHeading('Tidak ada data pribadi ditemukan')
            ->emptyStateDescription('Silakan buat data pribadi baru untuk memulai.')
            ->emptyStateActions([
                Tables\Actions\Action::make('create')
                    ->label('Buat Data Pribadi Baru')
                    ->url(static::getUrl('create'))
                    ->icon('heroicon-o-plus')
                    ->button(),
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
            'index' => Pages\ListDataPribadis::route('/'),
            'create' => Pages\CreateDataPribadi::route('/create'),
            'edit' => Pages\EditDataPribadi::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'primary';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Data crew freelance';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['nama_lengkap', 'email', 'nomor_telepon', 'pekerjaan'];
    }
}
