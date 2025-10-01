<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JournalBatchResource\Pages;
use App\Filament\Resources\JournalBatchResource\RelationManagers;
use App\Models\JournalBatch;
use App\Models\ChartOfAccount;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\FontWeight;
use Filament\Support\RawJs;

class JournalBatchResource extends Resource
{
    protected static ?string $model = JournalBatch::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Jurnal Umum';
    protected static ?string $navigationGroup = 'Akuntansi';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('manual_journal_warning')
                    ->content('⚠️ **Manual Journal Entry**: Gunakan hanya untuk jurnal penyesuaian, koreksi, transaksi aset tetap, atau entri non-operasional. Jurnal expense/payment umumnya di-generate otomatis dari data transaksi.')
                    ->columnSpanFull()
                    ->visible(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord),
                    
                Forms\Components\Section::make('Informasi Jurnal')
                    ->schema([
                        Forms\Components\Select::make('manual_journal_type')
                            ->label('Jenis Jurnal Manual')
                            ->options([
                                'adjustment' => 'Jurnal Penyesuaian (Depreciation, Accruals, etc.)',
                                'correction' => 'Jurnal Koreksi (Error correction, Reclassification)',
                                'asset' => 'Jurnal Aset Tetap (Purchase, Disposal, etc.)',
                                'financial' => 'Jurnal Keuangan (Loan, Investment, Bank charges)',
                                'tax' => 'Jurnal Pajak (Tax provision, Tax payment)',
                                'other' => 'Lainnya (Specify in description)'
                            ])
                            ->helperText('Pilih kategori jurnal manual untuk membantu tracking dan audit')
                            ->visible(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                            ->columnSpanFull(),
                            
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('batch_number')
                                    ->label('Nomor Batch')
                                    ->helperText('Nomor unik untuk identifikasi batch jurnal. Otomatis di-generate sistem.')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->default(fn () => JournalBatch::generateBatchNumber())
                                    ->maxLength(20),

                                Forms\Components\DatePicker::make('transaction_date')
                                    ->label('Tanggal Transaksi')
                                    ->helperText('Tanggal ketika transaksi terjadi. Tidak boleh tanggal masa depan.')
                                    ->required()
                                    ->default(now()),

                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->helperText('Draft: Dapat diedit. Posted: Sudah final. Reversed: Dibatalkan.')
                                    ->options([
                                        'draft' => 'Draft',
                                        'posted' => 'Posted',
                                        'reversed' => 'Reversed',
                                    ])
                                    ->default('draft')
                                    ->required(),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->label('Keterangan')
                            ->helperText('Deskripsi transaksi jurnal. Contoh: "Pembelian equipment untuk acara Wedding Sari" atau "Pembayaran vendor catering"')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('reference_type')
                                    ->label('Jenis Referensi')
                                    ->helperText('Jenis dokumen yang menjadi dasar jurnal. Contoh: "expense", "payment", "revenue", "adjustment"')
                                    ->maxLength(255)
                                    ->placeholder('Contoh: expense, payment, revenue'),

                                Forms\Components\TextInput::make('reference_id')
                                    ->label('ID Referensi')
                                    ->helperText('ID dari dokumen referensi (expense ID, payment ID, order ID, dll)')
                                    ->numeric()
                                    ->placeholder('Contoh: 144, 1052, 2031'),
                            ]),
                    ]),

                Forms\Components\Section::make('Total Transaksi')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('total_debit')
                                    ->label('Total Debit')
                                    ->helperText('Total nilai debit dalam jurnal. Dihitung otomatis dari journal entries.')
                                    ->required()
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->default(0.00)
                                    ->readOnly(),

                                Forms\Components\TextInput::make('total_credit')
                                    ->label('Total Kredit')
                                    ->helperText('Total nilai kredit dalam jurnal. Harus sama dengan total debit.')
                                    ->required()
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->default(0.00)
                                    ->readOnly(),
                            ]),
                    ]),

                Forms\Components\Section::make('Approval')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('created_by')
                                    ->label('Dibuat Oleh')
                                    ->relationship('createdBy', 'name')
                                    ->default(1) // Default to user ID 1 for now
                                    ->required()
                                    ->disabled(),

                                Forms\Components\Select::make('approved_by')
                                    ->label('Disetujui Oleh')
                                    ->relationship('approvedBy', 'name')
                                    ->nullable(),

                                Forms\Components\DateTimePicker::make('approved_at')
                                    ->label('Tanggal Persetujuan')
                                    ->nullable(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('transaction_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('batch_number')
                    ->label('Nomor Batch')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->copyable(),

                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Tanggal Transaksi')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Keterangan')
                    ->searchable()
                    ->limit(50)
                    ->wrap(),

                Tables\Columns\TextColumn::make('total_debit')
                    ->label('Total Debit')
                    ->money('IDR')
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('total_credit')
                    ->label('Total Kredit')
                    ->money('IDR')
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'draft' => 'warning',
                        'posted' => 'success',
                        'reversed' => 'danger',
                        default => 'gray'
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'draft' => 'Draft',
                        'posted' => 'Posted',
                        'reversed' => 'Reversed',
                        default => $state
                    }),

                Tables\Columns\TextColumn::make('reference_type')
                    ->label('Jenis Referensi')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Dibuat Oleh')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('approvedBy.name')
                    ->label('Disetujui Oleh')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Dihapus')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('-'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
                    ->label('Status Jurnal')
                    ->placeholder('Hanya Aktif')
                    ->trueLabel('Hanya Terhapus')
                    ->falseLabel('Dengan Terhapus'),
                    
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'posted' => 'Posted',
                        'reversed' => 'Reversed',
                    ]),
                    
                Tables\Filters\Filter::make('transaction_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('transaction_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('transaction_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    
                    Tables\Actions\Action::make('post_journal')
                        ->label('Post Jurnal')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (JournalBatch $record) {
                            if ($record->isBalanced()) {
                                $record->update(['status' => 'posted']);
                                
                                \Filament\Notifications\Notification::make()
                                    ->title('Jurnal Berhasil Di-Post')
                                    ->body("Batch {$record->batch_number} telah di-post")
                                    ->success()
                                    ->send();
                            } else {
                                \Filament\Notifications\Notification::make()
                                    ->title('Jurnal Tidak Seimbang')
                                    ->body('Debit dan Kredit harus seimbang untuk posting')
                                    ->warning()
                                    ->send();
                            }
                        })
                        ->requiresConfirmation()
                        ->visible(fn (JournalBatch $record) => $record->status === 'draft'),

                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                ])
                ->label('Aksi')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('gray')
                ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Buat Jurnal Pertama')
                    ->icon('heroicon-o-plus'),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->where('reference_type', 'NOT LIKE', '%_reversal');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\JournalEntriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJournalBatches::route('/'),
            'create' => Pages\CreateJournalBatch::route('/create'),
            'edit' => Pages\EditJournalBatch::route('/{record}/edit'),
        ];
    }
}
