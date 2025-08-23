<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountManagerTargetResource\Pages;
use App\Filament\Resources\AccountManagerTargetResource\RelationManagers;
use App\Models\AccountManagerTarget;
use App\Models\User; // Pastikan User model di-import
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Carbon\Carbon; // Untuk helper bulan dan tahun
use Filament\Support\RawJs; // Untuk masking input uang
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Filament\Forms\Get;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Support\Facades\DB;

class AccountManagerTargetResource extends Resource
{
    // Constants for magic numbers
    private const YEAR_RANGE_PAST = 2;
    private const YEAR_RANGE_FUTURE = 3;
    private const DEFAULT_TARGET_AMOUNT = 1000000000;
    
    // Role constants
    private const ROLE_SUPER_ADMIN = 'super_admin';
    private const ROLE_PANEL_USER = 'panel_user';
    private const ROLE_ACCOUNT_MANAGER = 'Account Manager';
    private const ADMIN_ROLES = [self::ROLE_SUPER_ADMIN, self::ROLE_PANEL_USER];
    private const ALL_AUTHORIZED_ROLES = [self::ROLE_SUPER_ADMIN, self::ROLE_PANEL_USER, self::ROLE_ACCOUNT_MANAGER];

    protected static ?string $model = AccountManagerTarget::class;
    protected static ?string $navigationIcon = 'heroicon-o-flag';
    protected static ?string $navigationGroup = 'Shop';
    protected static ?string $navigationLabel = 'AM Target';
    protected static ?string $modelLabel = 'Target Account Manager';
    protected static ?string $pluralModelLabel = 'Target Account Manager';
    protected static bool $shouldRegisterNavigation = true;

    // Cached user instance
    private static ?User $currentUser = null;

    /**
     * Get current authenticated user with caching
     */
    private static function getCurrentUser(): ?User
    {
        if (self::$currentUser === null) {
            self::$currentUser = Auth::user();
        }
        return self::$currentUser;
    }

    /**
     * Check if current user can access all targets
     */
    private static function canAccessAllTargets(): bool
    {
        $user = self::getCurrentUser();
        return $user && $user->hasRole(self::ADMIN_ROLES);
    }

    /**
     * Check if current user is account manager
     */
    private static function isAccountManager(): bool
    {
        $user = self::getCurrentUser();
        return $user && $user->hasRole(self::ROLE_ACCOUNT_MANAGER);
    }

    /**
     * Add achieved amount subquery to builder
     */
    private static function addAchievedAmountSubquery(Builder $query): Builder
    {
        return $query->selectSub(
            \App\Models\Order::query()
                ->selectRaw('COALESCE(SUM(total_price), 0)')
                ->whereColumn('orders.user_id', 'account_manager_targets.user_id')
                ->whereYear('orders.closing_date', DB::raw('account_manager_targets.year'))
                ->whereMonth('orders.closing_date', DB::raw('account_manager_targets.month')),
            'calculated_achieved_amount'
        );
    }

    /**
     * Apply role-based query filtering
     */
    private static function applyRoleBasedFiltering(Builder $query): Builder
    {
        $user = self::getCurrentUser();
        
        if (!$user) {
            return $query->where('id', 0);
        }

        if (self::canAccessAllTargets()) {
            return $query;
        }

        if (self::isAccountManager()) {
            return $query->where('user_id', $user->id);
        }

        return $query->where('id', 0);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Target')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship(
                                name: 'user',
                                titleAttribute: 'name',
                                // Filter agar hanya user dengan role 'Account Manager' yang muncul
                                modifyQueryUsing: function (Builder $query) {
                                    $user = self::getCurrentUser();
                                    
                                    // Jika user saat ini adalah Account Manager, hanya tampilkan diri mereka sendiri
                                    if ($user && $user->hasRole(self::ROLE_ACCOUNT_MANAGER)) {
                                        return $query->where('id', $user->id);
                                    }
                                    
                                    // Untuk super admin dan admin, tampilkan semua Account Manager
                                    return $query->whereHas('roles', fn ($q) => $q->where('name', self::ROLE_ACCOUNT_MANAGER));
                                }
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Account Manager')
                            ->helperText(function () {
                                $user = self::getCurrentUser();
                                if ($user && $user->hasRole(self::ROLE_ACCOUNT_MANAGER)) {
                                    return 'Anda hanya dapat membuat target untuk diri sendiri.';
                                }
                                return 'Pilih Account Manager yang akan diberi target.';
                            })
                            ->disabled(function () {
                                $user = self::getCurrentUser();
                                return $user && $user->hasRole(self::ROLE_ACCOUNT_MANAGER);
                            })
                            ->default(function () {
                                $user = self::getCurrentUser();
                                if ($user && $user->hasRole(self::ROLE_ACCOUNT_MANAGER)) {
                                    return $user->id;
                                }
                                return null;
                            })
                            ->dehydrated(true) // Pastikan field ini selalu disimpan ke database
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                // Update achieved amount when user changes
                                self::updateAchievedAmount($get, $set);
                            }),

                        Forms\Components\Select::make('year')
                            ->options(function () {
                                $currentYear = Carbon::now()->year;
                                $years = [];
                                for ($i = -self::YEAR_RANGE_PAST; $i <= self::YEAR_RANGE_FUTURE; $i++) { // Contoh: 2 tahun ke belakang dan 3 tahun ke depan
                                    $year = $currentYear + $i;
                                    $years[$year] = $year;
                                }
                                return $years;
                            })
                            ->default(Carbon::now()->year)
                            ->required()
                            ->label('Tahun')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                // Update achieved amount when year changes
                                self::updateAchievedAmount($get, $set);
                            }),

                        Forms\Components\Select::make('month')
                            ->options(function () {
                                $months = [];
                                for ($m = 1; $m <= 12; $m++) {
                                    $months[$m] = Carbon::create()->month($m)->format('F');
                                }
                                return $months;
                            })
                            ->required()
                            ->label('Bulan')
                            ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule, Get $get): Unique {
                                return $rule
                                    ->where('user_id', $get('user_id'))
                                    ->where('year', $get('year'));
                            })
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                // Update achieved amount when month changes
                                self::updateAchievedAmount($get, $set);
                            }),

                        Forms\Components\TextInput::make('target_amount')
                            ->numeric()
                            ->prefix('IDR')
                            // ->required()
                            ->default(self::DEFAULT_TARGET_AMOUNT) // Default 1 miliar
                            // ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') : '1.000.000.000')
                            // ->dehydrateStateUsing(fn ($state) => (int) str_replace(['.', ','], '', $state))
                            ->placeholder('1.000.000.000')
                            ->inputMode('numeric')
                            ->label('Jumlah Target')
                            ->mask(RawJs::make('money'))
                            ->helperText('Default: IDR 1.000.000.000 (1 Miliar)')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                // Update status when target amount changes
                                self::updateCalculatedStatus($get, $set);
                            }),

                        Forms\Components\TextInput::make('achieved_amount')
                            ->numeric()
                            ->prefix('IDR')
                            ->default(0) // Default dari migrasi
                            ->label('Jumlah Pencapaian')
                            ->helperText('Otomatis dihitung dari data pesanan yang sudah closing.')
                            ->readOnly()
                            ->dehydrated(true), // Tidak disimpan ke database karena dihitung otomatis

                        Forms\Components\TextInput::make('status')
                            ->default('pending') // Default dari migrasi
                            ->label('Status')
                            ->helperText('Otomatis dihitung berdasarkan persentase pencapaian.')
                            ->disabled()
                            ->dehydrated(false), // Tidak disimpan ke database karena dihitung otomatis
                    ])->columns(2), // Mengatur layout form menjadi 2 kolom
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                // Add calculated achieved amount from Orders table
                $query = self::addAchievedAmountSubquery($query);
                $query = self::applyRoleBasedFiltering($query);
                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Account Manager')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('year')
                    ->label('Tahun')
                    ->sortable(),
                Tables\Columns\TextColumn::make('month')
                    ->label('Bulan (Angka)')
                    ->sortable(),
                Tables\Columns\TextColumn::make('month_name')
                    ->label('Nama Bulan'),
                Tables\Columns\TextColumn::make('target_amount')
                    ->label('Target')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('calculated_achieved_amount')
                    ->label('Pencapaian')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('achievement_percentage')
                    ->label('Persentase (%)')
                    ->suffix('%'),
                Tables\Columns\TextColumn::make('calculated_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (AccountManagerTarget $record): string => $record->getStatusColor())
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filter untuk Account Manager
                Tables\Filters\SelectFilter::make('user_id')
                    ->relationship('user', 'name', fn (Builder $query) => $query->whereHas('roles', fn($q) => $q->where('name', self::ROLE_ACCOUNT_MANAGER)))
                    ->label('Account Manager')
                    ->searchable()
                    ->preload(),
                
                // Filter untuk Tahun
                Tables\Filters\SelectFilter::make('year')
                    ->options(
                        AccountManagerTarget::select('year')->distinct()->pluck('year', 'year')->toArray()
                    )
                    ->label('Tahun'),
                
                // Filter untuk Bulan
                Tables\Filters\SelectFilter::make('month')
                    ->options(function () {
                        $months = [];
                        for ($m = 1; $m <= 12; $m++) {
                            $months[$m] = Carbon::create()->month($m)->format('F');
                        }
                        return $months;
                    })
                    ->label('Bulan'),
                
                // Filter untuk Status Terhitung
                Tables\Filters\SelectFilter::make('calculated_status')
                    ->options([
                        'Overachieved' => 'Overachieved (> 100%)',
                        'Achieved' => 'Achieved (100%)',
                        'Partially Achieved' => 'Partially Achieved (50-99%)',
                        'Failed' => 'Failed (< 50%)',
                    ])
                    ->label('Status')
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'])) {
                            return $query;
                        }
                        
                        // Calculate percentage in SQL using calculated_achieved_amount
                        $statusValue = $data['value'];
                        $percentageExpression = '(COALESCE(calculated_achieved_amount, 0) * 100.0 / NULLIF(target_amount, 0))';
                        
                        return $query->where(function (Builder $query) use ($statusValue, $percentageExpression) {
                            if ($statusValue === 'Overachieved') {
                                $query->whereRaw("{$percentageExpression} > 100");
                            } elseif ($statusValue === 'Achieved') {
                                $query->whereRaw("{$percentageExpression} = 100");
                            } elseif ($statusValue === 'Partially Achieved') {
                                $query->whereRaw("{$percentageExpression} >= 50 AND {$percentageExpression} < 100");
                            } elseif ($statusValue === 'Failed') {
                                $query->whereRaw("{$percentageExpression} < 50");
                            }
                        });
                    }),
                
                // Filter untuk data yang di-trash (soft delete)
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat')
                        ->tooltip('Lihat detail target')
                        ->color('info'),
                    Tables\Actions\EditAction::make()
                        ->label('Edit')
                        ->tooltip('Edit target')
                        ->color('warning')
                        ->visible(function (AccountManagerTarget $record): bool {
                            $user = self::getCurrentUser();
                            
                            // Super admin dan admin bisa edit semua
                            if ($user && ($user->hasRole(self::ADMIN_ROLES))) {
                                return true;
                            }
                            
                            // Account Manager hanya bisa edit target mereka sendiri
                            if ($user && $user->hasRole(self::ROLE_ACCOUNT_MANAGER)) {
                                return $record->user_id === $user->id;
                            }
                            
                            return false;
                        }),
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->tooltip('Hapus target')
                        ->color('danger')
                        ->visible(function (AccountManagerTarget $record): bool {
                            $user = self::getCurrentUser();
                            
                            // Super admin dan admin bisa hapus semua
                            if ($user && ($user->hasRole(self::ADMIN_ROLES))) {
                                return true;
                            }
                            
                            // Account Manager tidak bisa hapus target mereka sendiri
                            // (opsional: jika ingin Account Manager bisa hapus, ubah menjadi seperti edit)
                            return false;
                        }),
                ])
                    ->tooltip('Aksi Target')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Target Terpilih')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Target Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus target yang dipilih? Target akan dipindahkan ke trash.')
                        ->modalSubmitActionLabel('Ya, hapus')
                        ->visible(function (): bool {
                            $user = self::getCurrentUser();
                            // Hanya super admin dan panel user yang bisa bulk delete
                            return $user && $user->hasRole(self::ADMIN_ROLES);
                        }),
                    
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Pulihkan Target Terpilih')
                        ->requiresConfirmation()
                        ->modalHeading('Pulihkan Target Terpilih')
                        ->modalDescription('Target yang dipilih akan dipulihkan dari trash.')
                        ->modalSubmitActionLabel('Ya, pulihkan')
                        ->visible(function (): bool {
                            $user = self::getCurrentUser();
                            // Hanya super admin dan panel user yang bisa bulk restore
                            return $user && $user->hasRole(self::ADMIN_ROLES);
                        }),
                    
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Hapus Permanen')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Permanen Target')
                        ->modalDescription('PERINGATAN: Target akan dihapus secara permanen dan tidak dapat dipulihkan!')
                        ->modalSubmitActionLabel('Ya, hapus permanen')
                        ->modalIcon('heroicon-o-exclamation-triangle')
                        ->visible(function (): bool {
                            $user = self::getCurrentUser();
                            // Hanya super admin yang bisa force delete
                            return $user && $user->hasRole(self::ROLE_SUPER_ADMIN);
                        }),
                    
                    Tables\Actions\BulkAction::make('export_targets')
                        ->label('Export Target Terpilih')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(function (Collection $records) {
                            // Export selected targets to CSV
                            $filename = 'account_manager_targets_' . now()->format('Y-m-d_H-i-s') . '.csv';
                            $headers = [
                                'Content-Type' => 'text/csv',
                                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                            ];
                            
                            $callback = function () use ($records) {
                                $file = fopen('php://output', 'w');
                                
                                // CSV Header
                                fputcsv($file, [
                                    'Account Manager',
                                    'Tahun',
                                    'Bulan',
                                    'Target Amount (IDR)',
                                    'Achieved Amount (IDR)',
                                    'Achievement Percentage (%)',
                                    'Status',
                                    'Created At',
                                ]);
                                
                                // CSV Data with optimized query
                                $recordIds = $records->pluck('id');
                                $achievedAmounts = \App\Models\Order::query()
                                    ->select('user_id', 
                                        DB::raw('YEAR(closing_date) as year'), 
                                        DB::raw('MONTH(closing_date) as month'),
                                        DB::raw('SUM(total_price) as total_achieved')
                                    )
                                    ->whereIn('user_id', $records->pluck('user_id'))
                                    ->groupBy('user_id', 'year', 'month')
                                    ->get()
                                    ->keyBy(function ($item) {
                                        return $item->user_id . '_' . $item->year . '_' . $item->month;
                                    });
                                
                                foreach ($records as $record) {
                                    $key = $record->user_id . '_' . $record->year . '_' . $record->month;
                                    $achievedAmount = $achievedAmounts->get($key)?->total_achieved ?? 0;
                                    
                                    $percentage = $record->target_amount > 0 
                                        ? round(($achievedAmount / $record->target_amount) * 100, 2)
                                        : 0;
                                    
                                    fputcsv($file, [
                                        $record->user->name ?? 'N/A',
                                        $record->year,
                                        $record->month_name,
                                        number_format($record->target_amount, 0, ',', '.'),
                                        number_format($achievedAmount, 0, ',', '.'),
                                        $percentage,
                                        $record->calculated_status,
                                        $record->created_at->format('d M Y H:i'),
                                    ]);
                                }
                                
                                fclose($file);
                            };
                            
                            return response()->stream($callback, 200, $headers);
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Export Target Terpilih')
                        ->modalDescription('Target yang dipilih akan diekspor ke file CSV.')
                        ->modalSubmitActionLabel('Export')
                        ->visible(function (): bool {
                            $user = self::getCurrentUser();
                            // Semua role yang bisa view bisa export
                            return $user && $user->hasRole(self::ALL_AUTHORIZED_ROLES);
                        }),
                    
                    Tables\Actions\BulkAction::make('update_status')
                        ->label('Update Status Terpilih')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->action(function (Collection $records) {
                            DB::transaction(function () use ($records) {
                                $updated = 0;
                                
                                // Optimize with single query for all records
                                $recordData = $records->map(function ($record) {
                                    return [
                                        'id' => $record->id,
                                        'user_id' => $record->user_id,
                                        'year' => $record->year,
                                        'month' => $record->month,
                                    ];
                                });
                                
                                // Get all achieved amounts in one query
                                $achievedAmounts = \App\Models\Order::query()
                                    ->select('user_id', 
                                        DB::raw('YEAR(closing_date) as year'), 
                                        DB::raw('MONTH(closing_date) as month'),
                                        DB::raw('SUM(total_price) as total_achieved')
                                    )
                                    ->whereIn('user_id', $recordData->pluck('user_id'))
                                    ->groupBy('user_id', 'year', 'month')
                                    ->get()
                                    ->keyBy(function ($item) {
                                        return $item->user_id . '_' . $item->year . '_' . $item->month;
                                    });
                                
                                foreach ($records as $record) {
                                    $key = $record->user_id . '_' . $record->year . '_' . $record->month;
                                    $achievedAmount = $achievedAmounts->get($key)?->total_achieved ?? 0;
                                    
                                    // Update achieved amount in database
                                    $record->update(['achieved_amount' => $achievedAmount]);
                                    $updated++;
                                }
                                
                                // Show notification
                                \Filament\Notifications\Notification::make()
                                    ->title('Status Updated')
                                    ->body("{$updated} target berhasil diperbarui statusnya.")
                                    ->success()
                                    ->send();
                            });
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Update Status Target')
                        ->modalDescription('Status pencapaian target yang dipilih akan diperbarui berdasarkan data pesanan terbaru.')
                        ->modalSubmitActionLabel('Update')
                        ->visible(function (): bool {
                            $user = self::getCurrentUser();
                            // Hanya admin yang bisa update status
                            return $user && $user->hasRole(self::ADMIN_ROLES);
                        }),
                ])->label('Aksi Massal'),
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
            'index' => Pages\ListAccountManagerTargets::route('/'),
            'create' => Pages\CreateAccountManagerTarget::route('/create'),
            'edit' => Pages\EditAccountManagerTarget::route('/{record}/edit'),
        ];
    }

    // Kontrol akses untuk resource
    public static function canCreate(): bool
    {
        $user = self::getCurrentUser();
        
        // Izinkan super admin, admin, dan Account Manager untuk create
        if (!$user) return false;
        
        try {
            return $user->hasRole(self::ALL_AUTHORIZED_ROLES);
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function canEdit($record): bool
    {
        $user = self::getCurrentUser();
        
        if (!$user) {
            return false;
        }
        
        try {
            // Super admin dan admin bisa edit semua
            if ($user->hasRole(self::ADMIN_ROLES)) {
                return true;
            }
            
            // Account Manager hanya bisa edit target mereka sendiri
            if ($user->hasRole(self::ROLE_ACCOUNT_MANAGER)) {
                return $record->user_id === $user->id;
            }
        } catch (\Exception $e) {
            return false;
        }
        
        return false;
    }

    public static function canDelete($record): bool
    {
        $user = self::getCurrentUser();
        
        if (!$user) {
            return false;
        }
        
        try {
            // Hanya super admin dan admin yang bisa hapus
            return $user->hasRole(self::ADMIN_ROLES);
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function canView($record): bool
    {
        $user = self::getCurrentUser();
        
        if (!$user) {
            return false;
        }
        
        try {
            // Super admin dan admin bisa lihat semua
            if ($user->hasRole(self::ADMIN_ROLES)) {
                return true;
            }
            
            // Account Manager hanya bisa lihat target mereka sendiri
            if ($user->hasRole(self::ROLE_ACCOUNT_MANAGER)) {
                return $record->user_id === $user->id;
            }
        } catch (\Exception $e) {
            return false;
        }
        
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->select('account_manager_targets.*'); // Ensure all base columns are selected
        
        $query = self::addAchievedAmountSubquery($query);
        $query = self::applyRoleBasedFiltering($query);
        
        return $query;
    }

    /**
     * Update achieved amount based on current form state
     */
    private static function updateAchievedAmount(callable $get, callable $set): void
    {
        $userId = $get('user_id');
        $year = $get('year');
        $month = $get('month');
        
        if ($userId && $year && $month) {
            // Import Order model if not already imported
            $achievedAmount = \App\Models\Order::query()
                ->where('user_id', $userId)
                ->whereYear('closing_date', $year)
                ->whereMonth('closing_date', $month)
                ->sum('total_price') ?? 0;
            
            $set('achieved_amount', $achievedAmount);
            
            // Update status as well
            self::updateCalculatedStatus($get, $set);
        }
    }
    
    /**
     * Update calculated status based on achievement percentage
     */
    private static function updateCalculatedStatus(callable $get, callable $set): void
    {
        $target = (float) str_replace(',', '', $get('target_amount') ?? '0');
        $achieved = (float) str_replace(',', '', $get('achieved_amount') ?? '0');
        
        if ($target > 0) {
            $percentage = ($achieved / $target) * 100;
            
            if ($percentage > 100) {
                $status = 'Overachieved';
            } elseif ($percentage == 100) {
                $status = 'Achieved';
            } elseif ($percentage >= 50) {
                $status = 'Partially Achieved';
            } else {
                $status = 'Failed';
            }
            
            $set('status', $status);
        } else {
            $set('status', 'Failed');
        }
    }
}
