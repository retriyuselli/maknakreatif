<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveTypeResource\Pages;
use App\Filament\Resources\LeaveTypeResource\RelationManagers;
use App\Models\LeaveType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LeaveTypeResource extends Resource
{
    protected static ?string $model = LeaveType::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Jenis Cuti';

    protected static ?string $modelLabel = 'Jenis Cuti';

    protected static ?string $pluralModelLabel = 'Jenis Cuti';

    protected static ?string $navigationGroup = 'Human Resource';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Jenis Cuti')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Jenis Cuti')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Cuti Tahunan, Cuti Sakit, Cuti Melahirkan')
                            ->unique(LeaveType::class, 'name', ignoreRecord: true),
                        Forms\Components\TextInput::make('max_days_per_year')
                            ->label('Maksimal Hari Per Tahun')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(365)
                            ->suffix('hari')
                            ->placeholder('12')
                            ->helperText('Jumlah maksimal hari cuti yang dapat diambil dalam satu tahun'),
                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->placeholder('Masukkan keterangan jenis cuti')
                            ->maxLength(500),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Jenis Cuti')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('max_days_per_year')
                    ->label('Maksimal Hari/Tahun')
                    ->numeric()
                    ->sortable()
                    ->suffix(' hari')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('approved_count')
                    ->label('Disetujui')
                    ->getStateUsing(function ($record) {
                        return $record->leaveRequests()->where('status', 'approved')->count();
                    })
                    ->badge()
                    ->color('success')
                    ->alignCenter()
                    ->sortable(false),
                Tables\Columns\TextColumn::make('pending_count')
                    ->label('Menunggu')
                    ->getStateUsing(function ($record) {
                        return $record->leaveRequests()->where('status', 'pending')->count();
                    })
                    ->badge()
                    ->color('warning')
                    ->alignCenter()
                    ->sortable(false),
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->sortable()
                    ->color('info'),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Dihapus')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Aktif')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->badge()
                    ->color(fn ($state) => $state ? 'danger' : 'success')
                    ->formatStateUsing(fn ($state) => $state ? 'Dihapus' : 'Aktif'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
                    ->label('Filter Status')
                    ->placeholder('Semua Data')
                    ->trueLabel('Hanya yang Dihapus')
                    ->falseLabel('Tanpa yang Dihapus'),
                Tables\Filters\Filter::make('max_days_range')
                    ->label('Range Maksimal Hari')
                    ->form([
                        Forms\Components\TextInput::make('max_days_from')
                            ->label('Dari')
                            ->numeric()
                            ->suffix('hari'),
                        Forms\Components\TextInput::make('max_days_to')
                            ->label('Sampai')
                            ->numeric()
                            ->suffix('hari'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['max_days_from'],
                                fn (Builder $query, $days): Builder => $query->where('max_days_per_year', '>=', $days),
                            )
                            ->when(
                                $data['max_days_to'],
                                fn (Builder $query, $days): Builder => $query->where('max_days_per_year', '<=', $days),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\RestoreAction::make()
                    ->successNotificationTitle('Jenis cuti berhasil dipulihkan'),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Jenis Cuti')
                    ->modalDescription('Apakah Anda yakin ingin menghapus jenis cuti ini? Data akan dipindahkan ke trash dan dapat dipulihkan.')
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->successNotificationTitle('Jenis cuti berhasil dihapus'),
                Tables\Actions\ForceDeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Permanen Jenis Cuti')
                    ->modalDescription('Apakah Anda yakin ingin menghapus permanen jenis cuti ini? Data tidak dapat dipulihkan!')
                    ->modalSubmitActionLabel('Ya, Hapus Permanen')
                    ->successNotificationTitle('Jenis cuti berhasil dihapus permanen'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\RestoreBulkAction::make()
                        ->successNotificationTitle('Jenis cuti terpilih berhasil dipulihkan'),
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Jenis Cuti Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus jenis cuti yang dipilih? Data akan dipindahkan ke trash dan dapat dipulihkan.')
                        ->modalSubmitActionLabel('Ya, Hapus Semua')
                        ->successNotificationTitle('Jenis cuti terpilih berhasil dihapus'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Permanen Jenis Cuti Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus permanen jenis cuti yang dipilih? Data tidak dapat dipulihkan!')
                        ->modalSubmitActionLabel('Ya, Hapus Permanen Semua')
                        ->successNotificationTitle('Jenis cuti terpilih berhasil dihapus permanen'),
                ]),
            ])
            ->defaultSort('name', 'asc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
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
            'index' => Pages\ListLeaveTypes::route('/'),
            'create' => Pages\CreateLeaveType::route('/create'),
            'edit' => Pages\EditLeaveType::route('/{record}/edit'),
        ];
    }
}
