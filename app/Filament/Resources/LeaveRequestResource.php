<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveRequestResource\Pages;
use App\Filament\Resources\LeaveRequestResource\RelationManagers;
use App\Filament\Resources\LeaveRequestResource\Widgets;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\BulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class LeaveRequestResource extends Resource
{
    protected static ?string $model = LeaveRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Permohonan Cuti';

    protected static ?string $pluralModelLabel = 'Permohonan Cuti';

    protected static ?string $navigationGroup = 'Human Resource';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Permohonan Cuti')
                    ->description('Informasi dasar tentang permohonan cuti')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('user_id')
                                    ->label('Karyawan')
                                    ->relationship('user', 'name')
                                    ->required()
                                    ->disabled(function () {
                                        $user = Auth::user();
                                        return $user ? !$user->roles->contains('name', 'super_admin') : true;
                                    })
                                    ->dehydrated(true)
                                    ->searchable()
                                    ->preload()
                                    ->default(fn () => Auth::id())
                                    ->columnSpan(1),

                                Select::make('leave_type_id')
                                    ->label('Jenis Cuti')
                                    ->relationship('leaveType', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(1),
                            ]),

                        Grid::make(3)
                            ->schema([
                                DatePicker::make('start_date')
                                    ->label('Tanggal Mulai')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $endDate = $get('end_date');
                                        if ($state && $endDate) {
                                            $startDate = Carbon::parse($state);
                                            $endDate = Carbon::parse($endDate);
                                            $totalDays = $startDate->diffInDays($endDate) + 1;
                                            $set('total_days', $totalDays);
                                        }
                                    }),

                                DatePicker::make('end_date')
                                    ->label('Tanggal Selesai')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $startDate = $get('start_date');
                                        if ($startDate && $state) {
                                            $startDate = Carbon::parse($startDate);
                                            $endDate = Carbon::parse($state);
                                            $totalDays = $startDate->diffInDays($endDate) + 1;
                                            $set('total_days', $totalDays);
                                        }
                                    }),

                                Forms\Components\TextInput::make('total_days')
                                    ->label('Total Hari')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(),
                            ]),

                        Textarea::make('reason')
                            ->label('Alasan Cuti')
                            ->rows(3)
                            ->placeholder('Silakan berikan alasan untuk permohonan cuti Anda...'),

                        Forms\Components\TextInput::make('emergency_contact')
                            ->label('Kontak Darurat')
                            ->placeholder('Informasi kontak darurat (opsional)')
                            ->helperText('Nama dan nomor telepon yang dapat dihubungi selama cuti'),

                        Forms\Components\FileUpload::make('documents')
                            ->label('Dokumen Pendukung')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(2048) // 2MB
                            ->directory('leave-documents')
                            ->multiple()
                            ->openable()
                            ->maxFiles(3)
                            ->helperText('Upload dokumen pendukung (PDF - maksimal 2MB per file, maksimal 3 file)'),

                        Select::make('replacement_employee_id')
                            ->label('Karyawan Pengganti')
                            ->relationship('replacementEmployee', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih karyawan pengganti (opsional)')
                            ->helperText('Pilih karyawan yang akan menangani tanggung jawab Anda selama cuti')
                            ->options(function () {
                                return User::where('status', 'active')
                                    ->where('id', '!=', Auth::id())
                                    ->pluck('name', 'id');
                            }),
                    ]),

                Section::make('Informasi Persetujuan')
                    ->description('Status dan detail persetujuan')
                    // ->visible(function () {
                    //     $user = Auth::user();
                    //     return $user ? $user->roles->contains('name', 'super_admin') : false;
                    // })
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'pending' => 'Menunggu',
                                        'approved' => 'Disetujui',
                                        'rejected' => 'Ditolak',
                                    ])
                                    ->default('pending')
                                    ->required()
                                    ->reactive(),

                                Select::make('approved_by')
                                    ->label('Disetujui Oleh')
                                    ->relationship('approver', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn (callable $get) => in_array($get('status'), ['approved', 'rejected'])),
                            ]),

                        Textarea::make('approval_notes')
                            ->label('Catatan Persetujuan')
                            ->rows(2)
                            ->placeholder('Tambahkan catatan tentang persetujuan/penolakan...')
                            ->visible(fn (callable $get) => in_array($get('status'), ['approved', 'rejected'])),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $user = Auth::user();
                // Jika bukan super_admin, hanya tampilkan data leave request milik user yang login
                if ($user && !$user->roles->contains('name', 'super_admin')) {
                    $query->where('user_id', $user->id);
                }
            })
            ->columns([
                TextColumn::make('user.name')
                    ->label('Karyawan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('leaveType.name')
                    ->label('Jenis Cuti')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('start_date')
                    ->label('Tanggal Mulai')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Tanggal Selesai')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('total_days')
                    ->label('Hari')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('documents')
                    ->label('Dokumen')
                    ->formatStateUsing(function ($state) {
                        if (empty($state)) {
                            return 'Tidak ada dokumen';
                        }
                        $count = is_array($state) ? count($state) : 0;
                        return $count . ' file' . ($count > 1 ? '' : '');
                    })
                    ->badge()
                    ->color(function ($state) {
                        return empty($state) ? 'gray' : 'success';
                    })
                    ->icon(function ($state) {
                        return empty($state) ? 'heroicon-o-document' : 'heroicon-o-document-text';
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(function (string $state): string {
                        return match ($state) {
                            'pending' => 'Menunggu',
                            'approved' => 'Disetujui',
                            'rejected' => 'Ditolak',
                            default => $state,
                        };
                    })
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-check-circle' => 'approved',
                        'heroicon-o-x-circle' => 'rejected',
                    ]),

                TextColumn::make('replacementEmployee.name')
                    ->label('Pengganti')
                    ->placeholder('Tidak ada pengganti')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('emergency_contact')
                    ->label('Kontak Darurat')
                    ->placeholder('Tidak disediakan')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(30),

                TextColumn::make('approver.name')
                    ->label('Disetujui Oleh')
                    ->placeholder('Belum disetujui')
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Tanggal Pengajuan')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),

                SelectFilter::make('leave_type_id')
                    ->label('Jenis Cuti')
                    ->relationship('leaveType', 'name'),

                SelectFilter::make('user_id')
                    ->label('Karyawan')
                    ->relationship('user', 'name')
                    ->searchable(),

                SelectFilter::make('replacement_employee_id')
                    ->label('Karyawan Pengganti')
                    ->relationship('replacementEmployee', 'name')
                    ->searchable(),

                Filter::make('date_range')
                    ->label('Rentang Tanggal')
                    ->form([
                        DatePicker::make('start_date')
                            ->label('Dari Tanggal'),
                        DatePicker::make('end_date')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('start_date', '>=', $date),
                            )
                            ->when(
                                $data['end_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('end_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat'),
                Tables\Actions\EditAction::make()
                    ->label('Edit'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->visible(function () {
                        $user = Auth::user();
                        return $user ? $user->roles->contains('name', 'super_admin') : false;
                    }),
                
                Tables\Actions\Action::make('view_documents')
                    ->label('Dokumen')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->visible(function (LeaveRequest $record) {
                        return !empty($record->documents);
                    })
                    ->modalHeading('Dokumen Pendukung')
                    ->modalContent(function (LeaveRequest $record) {
                        $documents = $record->documents ?? [];
                        $documentLinks = [];
                        
                        foreach ($documents as $document) {
                            $documentLinks[] = '<div class="mb-2">
                                <a href="' . asset('storage/' . $document) . '" 
                                   target="_blank" 
                                   class="text-blue-600 hover:text-blue-800 underline flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    ' . basename($document) . '
                                </a>
                            </div>';
                        }
                        
                        return view('filament.components.document-list', [
                            'documents' => $documents,
                            'documentLinks' => $documentLinks
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
                
                Tables\Actions\Action::make('approve')
                    ->label('Setuju')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(function (LeaveRequest $record) {
                        $user = Auth::user();
                        $isSuperAdmin = $user ? $user->roles->contains('name', 'super_admin') : false;
                        return $record->status === 'pending' && $isSuperAdmin;
                    })
                    ->action(function (LeaveRequest $record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => Auth::id(),
                        ]);
                        
                        Notification::make()
                            ->title('Permohonan cuti disetujui')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(function (LeaveRequest $record) {
                        $user = Auth::user();
                        $isSuperAdmin = $user ? $user->roles->contains('name', 'super_admin') : false;
                        return $record->status === 'pending' && $isSuperAdmin;
                    })
                    ->action(function (LeaveRequest $record) {
                        $record->update([
                            'status' => 'rejected',
                            'approved_by' => Auth::id(),
                        ]);
                        
                        Notification::make()
                            ->title('Permohonan cuti ditolak')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('view_approval')
                    ->label('Lihat Persetujuan')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('info')
                    ->visible(function (LeaveRequest $record) {
                        return $record->status === 'approved';
                    })
                    ->url(fn (LeaveRequest $record) => route('leave-request.approval-detail', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->visible(function () {
                            $user = Auth::user();
                            return $user ? $user->roles->contains('name', 'super_admin') : false;
                        }),
                    
                    BulkAction::make('approve_bulk')
                        ->label('Setujui Terpilih')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(function () {
                            $user = Auth::user();
                            return $user ? $user->roles->contains('name', 'super_admin') : false;
                        })
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                if ($record->status === 'pending') {
                                    $record->update([
                                        'status' => 'approved',
                                        'approved_by' => Auth::id(),
                                    ]);
                                }
                            });
                            
                            Notification::make()
                                ->title('Permohonan cuti terpilih telah disetujui')
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('reject_bulk')
                        ->label('Tolak Terpilih')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(function () {
                            $user = Auth::user();
                            return $user ? $user->roles->contains('name', 'super_admin') : false;
                        })
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                if ($record->status === 'pending') {
                                    $record->update([
                                        'status' => 'rejected',
                                        'approved_by' => Auth::id(),
                                    ]);
                                }
                            });
                            
                            Notification::make()
                                ->title('Permohonan cuti terpilih telah ditolak')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            Widgets\LeaveRequestOverview::class,
            Widgets\LeaveRequestChart::class,
            Widgets\LeaveTypeStats::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaveRequests::route('/'),
            'create' => Pages\CreateLeaveRequest::route('/create'),
            'edit' => Pages\EditLeaveRequest::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
