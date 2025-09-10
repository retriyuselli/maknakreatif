<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SopResource\Pages;
use App\Filament\Resources\SopResource\RelationManagers;
use App\Models\Sop;
use App\Models\SopCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Illuminate\Support\Facades\Auth;

class SopResource extends Resource
{
    protected static ?string $model = Sop::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationLabel = 'SOP';
    
    protected static ?string $modelLabel = 'SOP';
    
    protected static ?string $pluralModelLabel = 'SOP';
    
    protected static ?string $navigationGroup = 'SOP Management';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Umum')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('Judul SOP')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Masukkan judul SOP')
                                    ->columnSpanFull(),
                                    
                                Forms\Components\Select::make('category_id')
                                    ->label('Kategori')
                                    ->options(SopCategory::active()->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                    
                                Forms\Components\TextInput::make('version')
                                    ->label('Versi')
                                    ->default('1.0')
                                    ->required()
                                    ->maxLength(10),
                            ]),
                            
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->required()
                            ->rows(4)
                            ->placeholder('Masukkan deskripsi SOP'),
                            
                        Forms\Components\Textarea::make('keywords')
                            ->label('Kata Kunci')
                            ->placeholder('Masukkan kata kunci untuk pencarian (pisahkan dengan koma)')
                            ->helperText('Kata kunci akan membantu user menemukan SOP ini lebih mudah'),
                    ]),
                    
                Forms\Components\Section::make('Langkah-langkah')
                    ->schema([
                        Repeater::make('steps')
                            ->label('Langkah')
                            ->schema([
                                Forms\Components\TextInput::make('step_number')
                                    ->label('No. Langkah')
                                    ->numeric()
                                    ->required()
                                    ->default(function ($livewire) {
                                        $steps = $livewire->data['steps'] ?? [];
                                        return count($steps) + 1;
                                    }),
                                    
                                Forms\Components\TextInput::make('title')
                                    ->label('Judul Langkah')
                                    ->required()
                                    ->placeholder('Masukkan judul langkah'),
                                    
                                Forms\Components\RichEditor::make('description')
                                    ->label('Deskripsi Langkah')
                                    ->required()
                                    ->placeholder('Jelaskan secara detail langkah ini'),
                                    
                                Forms\Components\RichEditor::make('notes')
                                    ->label('Catatan')
                                    ->placeholder('Catatan tambahan untuk langkah ini (opsional)'),
                            ])
                            ->columns(1)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Langkah baru')
                            ->addActionLabel('Tambah Langkah')
                            ->defaultItems(1)
                            ->reorderable()
                            ->required(),
                    ]),
                    
                Forms\Components\Section::make('Dokumen Pendukung')
                    ->schema([
                        FileUpload::make('supporting_documents')
                            ->label('File Pendukung')
                            ->multiple()
                            ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->directory('sop-documents')
                            ->visibility('private')
                            ->downloadable()
                            ->openable()
                            ->previewable(false)
                            ->helperText('Upload dokumen pendukung seperti PDF, gambar, atau dokumen Word'),
                    ]),
                    
                Forms\Components\Section::make('Pengaturan')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\DatePicker::make('effective_date')
                                    ->label('Tanggal Berlaku')
                                    ->default(now())
                                    ->required(),
                                    
                                Forms\Components\DatePicker::make('review_date')
                                    ->label('Tanggal Review')
                                    ->helperText('Tanggal untuk review SOP ini'),
                                    
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->default(true)
                                    ->helperText('SOP yang tidak aktif tidak akan ditampilkan ke user'),
                            ]),
                            
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('created_by')
                                    ->label('Dibuat Oleh')
                                    ->relationship('creator', 'name')
                                    ->default(Auth::id())
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->disabled(fn (?Sop $record) => $record !== null)
                                    ->dehydrated()
                                    ->helperText('User yang membuat SOP ini'),
                                    
                                Forms\Components\Select::make('updated_by')
                                    ->label('Diperbarui Oleh')
                                    ->relationship('updater', 'name')
                                    ->default(Auth::id())
                                    ->searchable()
                                    ->preload()
                                    ->disabled()
                                    ->dehydrated()
                                    ->helperText('User yang terakhir memperbarui SOP ini')
                                    ->visible(fn (?Sop $record) => $record !== null),
                            ]),
                    ]),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul SOP')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                    
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => $record->category?->color ?? 'gray'),
                    
                Tables\Columns\TextColumn::make('version')
                    ->label('Versi')
                    ->badge()
                    ->color('success')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('steps_count')
                    ->label('Langkah')
                    ->alignCenter()
                    ->sortable(),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('effective_date')
                    ->label('Tanggal Berlaku')
                    ->date('d M Y')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('review_date')
                    ->label('Review')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn ($record) => $record->needsReview() ? 'danger' : 'success')
                    ->badge(fn ($record) => $record->needsReview()),
                    
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->toggleable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->options(SopCategory::active()->pluck('name', 'id'))
                    ->multiple(),
                    
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),
                    
                Tables\Filters\Filter::make('needs_review')
                    ->label('Perlu Review')
                    ->query(fn (Builder $query): Builder => $query->whereDate('review_date', '<', now())),
                    
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Preview')
                    ->icon('heroicon-o-eye'),
                    
                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-o-pencil'),
                    
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplikat')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function (Sop $record) {
                        $newSop = $record->replicate();
                        $newSop->title = $record->title . ' (Copy)';
                        $newSop->version = '1.0';
                        $newSop->created_by = Auth::id();
                        $newSop->updated_by = Auth::id();
                        $newSop->save();
                        
                        return redirect()->route('filament.admin.resources.sops.edit', $newSop);
                    })
                    ->requiresConfirmation(),
                    
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
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
            'index' => Pages\ListSops::route('/'),
            'create' => Pages\CreateSop::route('/create'),
            'edit' => Pages\EditSop::route('/{record}/edit'),
            'view' => Pages\ViewSop::route('/{record}'),
        ];
    }
}
