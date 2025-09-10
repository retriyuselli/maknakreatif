<?php

namespace App\Filament\Resources;
use App\Filament\Resources\UserResource\Widgets\AccountManagerStats;
use App\Filament\Resources\UserResource\Widgets\UserExpirationWidget;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\Widgets\UserStatsOverview;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationGroup = 'Human Resource';

    /**
     * Check if current user is super admin
     */
    public static function isSuperAdmin(): bool
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user) return false;
        
        return $user->hasRole('super_admin');
    }

    /**
     * Check if target user is super admin
     */
    public static function isTargetUserSuperAdmin($record): bool
    {
        if (!$record) return false;
        
        return $record->hasRole('super_admin');
    }

    /**
     * Apply query restrictions based on user role
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['payrolls' => function ($query) {
                $query->latest(); // Load payrolls ordered by latest
            }])
            ->with('roles') // Load roles for display and counting
            ->withCount('roles'); // Add roles count for sorting and display
        
        // If current user is not super_admin, hide super_admin users from the list
        if (!static::isSuperAdmin()) {
            $query->whereDoesntHave('roles', function (Builder $query) {
                $query->where('name', 'super_admin');
            });
        }
        
        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Basic Information Section
                Forms\Components\Section::make('Informasi Dasar')
                    ->description('Informasi dasar akun pengguna')
                    ->schema([
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nama Lengkap')
                                            ->required()
                                            ->maxLength(255)
                                            ->autocomplete('name')
                                            ->placeholder('Masukkan nama lengkap'),
                                        
                                        Forms\Components\TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->autocomplete('email')
                                            ->placeholder('user@example.com'),
                                    ]),
                            ]),
                        
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Select::make('roles')
                                            ->label('Role')
                                            ->relationship('roles', 'name')
                                            ->multiple()
                                            ->preload()
                                            ->searchable()
                                            ->placeholder('Pilih Role')
                                            ->maxItems(5)
                                            ->helperText('Pilih satu atau lebih role untuk pengguna (maksimal 5 role)')
                                            ->createOptionForm([
                                                Forms\Components\TextInput::make('name')
                                                    ->label('Nama Role')
                                                    ->required()
                                                    ->unique('roles', 'name'),
                                            ])
                                            ->createOptionUsing(function (array $data) {
                                                return \Spatie\Permission\Models\Role::create($data)->getKey();
                                            }),
                                        
                                        Forms\Components\Select::make('status_id')
                                            ->label('Status Jabatan')
                                            ->relationship('status', 'status_name')
                                            ->required()
                                            ->preload()
                                            ->searchable()
                                            ->native(false)
                                            // ->id('status_jabatan_select')
                                            ->selectablePlaceholder(false)
                                            ->placeholder('Pilih Status Jabatan')
                                            ->helperText('Status jabatan pengguna (Admin, Finance, HRD, dll)'),
                                    ]),
                            ]),
                        
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('password')
                                            ->label('Password')
                                            ->password()
                                            ->required(fn (string $operation): bool => $operation === 'create')
                                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                                            ->dehydrated(fn ($state) => filled($state))
                                            ->minLength(8)
                                            ->maxLength(255)
                                            ->helperText('Minimal 8 karakter. Kosongkan jika tidak ingin mengubah password.')
                                            ->columnSpan(2),
                                    ]),
                            ]),
                    ]),

                // Personal Information Section
                Forms\Components\Section::make('Informasi Personal')
                    ->description('Informasi personal dan kontak')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('phone_number')
                                    ->label('Nomor Telepon')
                                    ->tel()
                                    ->maxLength(255)
                                    ->placeholder('08xx-xxxx-xxxx'),
                                
                                Forms\Components\DatePicker::make('date_of_birth')
                                    ->label('Tanggal Lahir')
                                    ->displayFormat('d/m/Y')
                                    ->maxDate(now()->subYears(17)), // Minimal 17 tahun
                            ]),
                        
                        Forms\Components\Textarea::make('address')
                            ->label('Alamat')
                            ->maxLength(500)
                            ->rows(3)
                            ->placeholder('Alamat lengkap'),
                        
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('gender')
                                    ->label('Jenis Kelamin')
                                    ->options([
                                        'male' => 'Laki-laki',
                                        'female' => 'Perempuan',
                                    ])
                                    ->placeholder('Pilih jenis kelamin'),
                                
                                Forms\Components\Select::make('department')
                                    ->label('Departemen')
                                    ->options([
                                        'bisnis' => 'Bisnis',
                                        'operasional' => 'Operasional',
                                    ])
                                    ->default('operasional')
                                    ->required(),
                            ]),
                    ]),

                // Employment Information Section
                Forms\Components\Section::make('Informasi Pekerjaan')
                    ->description('Informasi terkait pekerjaan dan masa kerja')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('hire_date')
                                    ->label('Tanggal Mulai Kerja')
                                    ->displayFormat('d/m/Y')
                                    ->maxDate(now()),
                                
                                Forms\Components\DatePicker::make('last_working_date')
                                    ->label('Tanggal Berakhir Kerja')
                                    ->displayFormat('d/m/Y')
                                    ->helperText('Kosongkan jika masih aktif bekerja'),
                            ]),
                    ]),

                // Account Settings Section
                Forms\Components\Section::make('Pengaturan Akun')
                    ->description('Pengaturan foto profil, status akun, dan kedaluwarsa akun')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\FileUpload::make('avatar_url')
                                    ->label('Foto Profil')
                                    ->image()
                                    ->directory('avatars')
                                    ->imageEditor()
                                    ->circleCropper()
                                    ->maxSize(2048) // 2MB
                                    ->helperText('Upload foto profil (maksimal 2MB)'),
                                
                                Forms\Components\Select::make('status')
                                    ->label('Status Akun')
                                    ->options([
                                        'active' => '🟢 Aktif - Dapat mengakses sistem',
                                        'inactive' => '🟠 Nonaktif - Akses sementara diblokir', 
                                        'terminated' => '🔴 Terminated - Akses permanent diblokir',
                                    ])
                                    ->default('active')
                                    ->required()
                                    ->helperText('Mengatur tingkat akses pengguna ke sistem')
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set) {
                                        // Auto set expire_date if terminated
                                        if ($state === 'terminated') {
                                            $set('expire_date', now());
                                        }
                                    }),
                            ]),
                        
                        Forms\Components\DateTimePicker::make('expire_date')
                            ->label('Tanggal Kedaluwarsa Akun')
                            ->helperText('Kosongkan jika akun tidak memiliki batas waktu. Otomatis diisi jika status Terminated.')
                            ->displayFormat('d/m/Y H:i')
                            ->minDate(now())
                            ->hidden(fn ($get) => $get('status') === 'terminated'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $user = Auth::user();
                // Jika bukan super_admin, hanya tampilkan data milik user yang login
                if ($user && !$user->roles->contains('name', 'super_admin')) {
                    $query->where('id', $user->id);
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label('Avatar')
                    ->defaultImageUrl(function ($record) {
                        // Generate default avatar based on user's name initials
                        $name = $record->name ?? 'User';
                        $initials = collect(explode(' ', $name))
                            ->map(fn($word) => strtoupper(substr($word, 0, 1)))
                            ->take(2)
                            ->implode('');
                        
                        // Use UI Avatars service to generate default avatar
                        return "https://ui-avatars.com/api/?name={$initials}&background=3b82f6&color=ffffff&size=128";
                    })
                    ->circular()
                    ->size(40),
                
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Email copied!')
                    ->icon('heroicon-o-envelope'),
                
                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Telepon')
                    ->searchable()
                    ->placeholder('Tidak ada')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-phone'),
                
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->separator(',')
                    ->color(function (string $state): string {
                        return match ($state) {
                            'super_admin' => 'danger',
                            'admin' => 'warning',
                            'Account Manager' => 'info',
                            'employee' => 'success',
                            default => 'gray',
                        };
                    }),
                
                Tables\Columns\TextColumn::make('roles_count')
                    ->label('Jumlah Role')
                    ->getStateUsing(function (User $record): string {
                        $count = $record->roles_count ?? $record->roles()->count();
                        return $count . ' Role' . ($count > 1 ? 's' : '');
                    })
                    ->badge()
                    ->color(function (User $record): string {
                        $count = $record->roles_count ?? $record->roles()->count();
                        return match (true) {
                            $count === 0 => 'gray',
                            $count === 1 => 'success',
                            $count === 2 => 'warning',
                            $count >= 3 => 'danger',
                            default => 'primary',
                        };
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('roles_count', $direction);
                    })
                    ->icon('heroicon-o-user-group')
                    ->tooltip(function (User $record): string {
                        $roles = $record->roles->pluck('name')->toArray();
                        return empty($roles) ? 'Tidak ada role' : 'Roles: ' . implode(', ', $roles);
                    }),
                
                Tables\Columns\TextColumn::make('status.status_name')
                    ->label('Status Jabatan')
                    ->badge()
                    ->searchable()
                    ->color(function (string $state): string {
                        return match ($state) {
                            'Admin' => 'danger',
                            'Finance' => 'warning',
                            'HRD' => 'info',
                            'Account Manager' => 'primary',
                            'Staff' => 'success',
                            default => 'gray',
                        };
                    }),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Status Akun')
                    ->badge()
                    ->color(function (string $state): string {
                        return match ($state) {
                            'active' => 'success',
                            'inactive' => 'warning',
                            'terminated' => 'danger',
                            default => 'gray',
                        };
                    })
                    ->formatStateUsing(function (string $state): string {
                        return match ($state) {
                            'active' => 'Aktif',
                            'inactive' => 'Nonaktif',
                            'terminated' => 'Terminated',
                            default => $state,
                        };
                    }),
                
                Tables\Columns\TextColumn::make('department')
                    ->label('Departemen')
                    ->badge()
                    ->color(function (string $state): string {
                        return match ($state) {
                            'bisnis' => 'success',
                            'operasional' => 'primary',
                            default => 'gray',
                        };
                    })
                    ->formatStateUsing(function (string $state): string {
                        return match ($state) {
                            'bisnis' => 'Bisnis',
                            'operasional' => 'Operasional',
                            default => $state,
                        };
                    }),

                Tables\Columns\TextColumn::make('payrolls.monthly_salary')
                    ->label('Gaji Bulanan')
                    ->money('IDR')
                    ->sortable()
                    ->placeholder('Belum diatur')
                    ->getStateUsing(function ($record) {
                        // Ambil payroll terbaru berdasarkan created_at
                        $latestPayroll = $record->payrolls()->latest()->first();
                        return $latestPayroll ? $latestPayroll->monthly_salary : null;
                    })
                    ->color(function ($state) {
                        if (!$state) return 'gray';
                        if ($state >= 8000000) return 'success';
                        if ($state >= 5000000) return 'warning';
                        return 'danger';
                    })
                    ->icon('heroicon-o-banknotes')
                    ->tooltip(function ($record) {
                        $latestPayroll = $record->payrolls()->latest()->first();
                        if (!$latestPayroll) return 'Belum ada data payroll';
                        
                        return sprintf(
                            "Gaji Tahunan: %s\nBonus: %s\nTotal: %s\nPeriode: %s",
                            $latestPayroll->formatted_annual_salary_with_prefix,
                            $latestPayroll->formatted_bonus_with_prefix,
                            $latestPayroll->formatted_total_compensation_with_prefix,
                            $latestPayroll->pay_period ?? 'N/A'
                        );
                    }),

                Tables\Columns\TextColumn::make('total_leave_taken')
                    ->label('Cuti Diambil')
                    ->getStateUsing(function ($record) {
                        return $record->leaveRequests()
                            ->where('status', 'approved')
                            ->whereYear('start_date', date('Y'))
                            ->sum('total_days');
                    })
                    ->formatStateUsing(function ($state) {
                        return $state . ' hari';
                    })
                    ->badge()
                    ->color(function ($state) {
                        if ($state == 0) return 'gray';
                        if ($state <= 6) return 'success';
                        if ($state <= 12) return 'warning';
                        return 'danger';
                    })
                    ->icon('heroicon-o-calendar-days')
                    ->tooltip(function ($record) {
                        $currentYear = date('Y');
                        $totalApproved = $record->leaveRequests()
                            ->where('status', 'approved')
                            ->whereYear('start_date', $currentYear)
                            ->sum('total_days');
                        
                        $totalPending = $record->leaveRequests()
                            ->where('status', 'pending')
                            ->whereYear('start_date', $currentYear)
                            ->sum('total_days');
                        
                        $totalRejected = $record->leaveRequests()
                            ->where('status', 'rejected')
                            ->whereYear('start_date', $currentYear)
                            ->sum('total_days');
                        
                        return sprintf(
                            "Tahun %s:\nDisetujui: %d hari\nMenunggu: %d hari\nDitolak: %d hari",
                            $currentYear,
                            $totalApproved,
                            $totalPending,
                            $totalRejected
                        );
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('remaining_leave')
                    ->label('Sisa Cuti')
                    ->getStateUsing(function ($record) {
                        $annualLeaveAllowance = 12; // Default 12 hari per tahun
                        $usedLeave = $record->leaveRequests()
                            ->where('status', 'approved')
                            ->whereYear('start_date', date('Y'))
                            ->sum('total_days');
                        
                        return max(0, $annualLeaveAllowance - $usedLeave);
                    })
                    ->formatStateUsing(function ($state) {
                        return $state . ' hari';
                    })
                    ->badge()
                    ->color(function ($state) {
                        if ($state >= 8) return 'success';
                        if ($state >= 4) return 'warning';
                        if ($state > 0) return 'danger';
                        return 'gray';
                    })
                    ->icon('heroicon-o-clock')
                    ->tooltip(function ($record) {
                        $annualLeaveAllowance = 12;
                        $currentYear = date('Y');
                        $usedLeave = $record->leaveRequests()
                            ->where('status', 'approved')
                            ->whereYear('start_date', $currentYear)
                            ->sum('total_days');
                        
                        $remainingLeave = max(0, $annualLeaveAllowance - $usedLeave);
                        $percentage = $annualLeaveAllowance > 0 ? round(($usedLeave / $annualLeaveAllowance) * 100, 1) : 0;
                        
                        return sprintf(
                            "Jatah Tahunan: %d hari\nTerpakai: %d hari (%.1f%%)\nSisa: %d hari",
                            $annualLeaveAllowance,
                            $usedLeave,
                            $percentage,
                            $remainingLeave
                        );
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('hire_date')
                    ->label('Tanggal Mulai')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('Tidak ada')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-calendar'),
                
                Tables\Columns\TextColumn::make('expire_date')
                    ->label('Kedaluwarsa')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Tidak ada batas')
                    ->sortable()
                    ->color(function ($record) {
                        if (!$record->expire_date) return 'gray';
                        if (method_exists($record, 'isExpired') && $record->isExpired()) return 'danger';
                        if (method_exists($record, 'isExpiringSoon') && $record->isExpiringSoon()) return 'warning';
                        return 'success';
                    })
                    ->badge(function ($record) {
                        if (!$record->expire_date) return false;
                        return (method_exists($record, 'isExpired') && $record->isExpired()) || 
                               (method_exists($record, 'isExpiringSoon') && $record->isExpiringSoon());
                    })
                    ->formatStateUsing(function ($state, $record) {
                        if (!$state) return 'Tidak ada batas';
                        if (method_exists($record, 'isExpired') && $record->isExpired()) return $state . ' (Kedaluwarsa)';
                        if (method_exists($record, 'isExpiringSoon') && $record->isExpiringSoon()) {
                            $days = method_exists($record, 'getDaysUntilExpiration') ? $record->getDaysUntilExpiration() : 0;
                            return $state . " ($days hari lagi)";
                        }
                        return $state;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('gender')
                    ->label('Jenis Kelamin')
                    ->formatStateUsing(function (string $state): string {
                        return match ($state) {
                            'male' => 'Laki-laki',
                            'female' => 'Perempuan',
                            default => 'Tidak diketahui',
                        };
                    })
                    ->badge()
                    ->color(function (string $state): string {
                        return match ($state) {
                            'male' => 'blue',
                            'female' => 'pink',
                            default => 'gray',
                        };
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                
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
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->preload()
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status Jabatan')
                    ->relationship('status', 'status_name')
                    ->preload(),
                
                Tables\Filters\SelectFilter::make('account_status')
                    ->label('Status Akun')
                    ->options([
                        'active' => 'Aktif',
                        'inactive' => 'Nonaktif',
                        'terminated' => 'Terminated',
                    ])
                    ->attribute('status'),
                
                Tables\Filters\SelectFilter::make('department')
                    ->label('Departemen')
                    ->options([
                        'bisnis' => 'Bisnis',
                        'operasional' => 'Operasional',
                    ]),

                Tables\Filters\SelectFilter::make('salary_range')
                    ->label('Range Gaji')
                    ->options([
                        'below_5m' => 'Di bawah 5 Juta',
                        '5m_8m' => '5 - 8 Juta',
                        'above_8m' => 'Di atas 8 Juta',
                        'no_salary' => 'Belum Ada Gaji',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!isset($data['value']) || !$data['value']) {
                            return $query;
                        }

                        switch ($data['value']) {
                            case 'below_5m':
                                return $query->whereHas('payrolls', function (Builder $q) {
                                    $q->where('monthly_salary', '<', 5000000);
                                });
                            case '5m_8m':
                                return $query->whereHas('payrolls', function (Builder $q) {
                                    $q->whereBetween('monthly_salary', [5000000, 8000000]);
                                });
                            case 'above_8m':
                                return $query->whereHas('payrolls', function (Builder $q) {
                                    $q->where('monthly_salary', '>', 8000000);
                                });
                            case 'no_salary':
                                return $query->whereDoesntHave('payrolls');
                            default:
                                return $query;
                        }
                    }),
                
                Tables\Filters\SelectFilter::make('gender')
                    ->label('Jenis Kelamin')
                    ->options([
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                    ]),
                
                Tables\Filters\Filter::make('expired')
                    ->label('Kedaluwarsa')
                    ->query(fn (Builder $query): Builder => $query->where('expire_date', '<', now()))
                    ->toggle(),
                
                Tables\Filters\Filter::make('active')
                    ->label('Aktif (Tanpa Batas)')
                    ->query(fn (Builder $query): Builder => $query->whereNull('expire_date'))
                    ->toggle(),

                Tables\Filters\SelectFilter::make('leave_usage')
                    ->label('Penggunaan Cuti')
                    ->options([
                        'no_leave' => 'Belum Pernah Cuti',
                        'low_usage' => 'Penggunaan Rendah (≤ 3 hari)',
                        'medium_usage' => 'Penggunaan Sedang (4-8 hari)',
                        'high_usage' => 'Penggunaan Tinggi (> 8 hari)',
                        'over_limit' => 'Melebihi Jatah (> 12 hari)',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!isset($data['value']) || !$data['value']) {
                            return $query;
                        }

                        $currentYear = date('Y');
                        
                        switch ($data['value']) {
                            case 'no_leave':
                                return $query->whereDoesntHave('leaveRequests', function (Builder $q) use ($currentYear) {
                                    $q->where('status', 'approved')
                                      ->whereYear('start_date', $currentYear);
                                });
                            
                            case 'low_usage':
                                return $query->whereHas('leaveRequests', function (Builder $q) use ($currentYear) {
                                    $q->where('status', 'approved')
                                      ->whereYear('start_date', $currentYear);
                                })->whereRaw("
                                    (SELECT COALESCE(SUM(total_days), 0) 
                                     FROM leave_requests 
                                     WHERE user_id = users.id 
                                     AND status = 'approved' 
                                     AND YEAR(start_date) = ?) <= 3
                                ", [$currentYear]);
                            
                            case 'medium_usage':
                                return $query->whereRaw("
                                    (SELECT COALESCE(SUM(total_days), 0) 
                                     FROM leave_requests 
                                     WHERE user_id = users.id 
                                     AND status = 'approved' 
                                     AND YEAR(start_date) = ?) BETWEEN 4 AND 8
                                ", [$currentYear]);
                            
                            case 'high_usage':
                                return $query->whereRaw("
                                    (SELECT COALESCE(SUM(total_days), 0) 
                                     FROM leave_requests 
                                     WHERE user_id = users.id 
                                     AND status = 'approved' 
                                     AND YEAR(start_date) = ?) BETWEEN 9 AND 12
                                ", [$currentYear]);
                            
                            case 'over_limit':
                                return $query->whereRaw("
                                    (SELECT COALESCE(SUM(total_days), 0) 
                                     FROM leave_requests 
                                     WHERE user_id = users.id 
                                     AND status = 'approved' 
                                     AND YEAR(start_date) = ?) > 12
                                ", [$currentYear]);
                            
                            default:
                                return $query;
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat')
                        ->color('info'),
                    
                    Tables\Actions\EditAction::make()
                        ->label('Edit')
                        ->color('warning')
                        ->visible(function ($record) {
                            // Super admin can edit anyone
                            if (static::isSuperAdmin()) {
                                return true;
                            }
                            // Non-super admin cannot edit super admin users
                            return !static::isTargetUserSuperAdmin($record);
                        }),
                    
                    Tables\Actions\Action::make('reset_password')
                        ->label('Reset Password')
                        ->icon('heroicon-o-key')
                        ->color('secondary')
                        ->form([
                            Forms\Components\TextInput::make('new_password')
                                ->label('Password Baru')
                                ->password()
                                ->required()
                                ->minLength(8)
                                ->maxLength(255),
                            Forms\Components\TextInput::make('confirm_password')
                                ->label('Konfirmasi Password')
                                ->password()
                                ->required()
                                ->same('new_password'),
                        ])
                        ->action(function (array $data, $record): void {
                            $record->update([
                                'password' => Hash::make($data['new_password']),
                            ]);
                            
                            Notification::make()
                                ->title('Password berhasil direset')
                                ->success()
                                ->send();
                        })
                        ->modalHeading('Reset Password User')
                        ->modalDescription('Masukkan password baru untuk user ini')
                        ->modalSubmitActionLabel('Reset Password')
                        ->modalCancelActionLabel('Cancel')
                        ->modalContent(view('filament.modal.reset-password-content'))
                        ->visible(function ($record) {
                            // Super admin can reset anyone's password
                            if (static::isSuperAdmin()) {
                                return true;
                            }
                            // Non-super admin cannot reset super admin's password
                            return !static::isTargetUserSuperAdmin($record);
                        }),
                    
                    Tables\Actions\Action::make('toggle_status')
                        ->label(function ($record) {
                            return $record->status === 'active' ? 'Nonaktifkan' : 'Aktifkan';
                        })
                        ->icon(function ($record) {
                            return $record->status === 'active' ? 'heroicon-o-pause' : 'heroicon-o-play';
                        })
                        ->color(function ($record) {
                            return $record->status === 'active' ? 'warning' : 'success';
                        })
                        ->action(function ($record, $livewire): void {
                            $newStatus = $record->status === 'active' ? 'inactive' : 'active';
                            $record->update(['status' => $newStatus]);
                            
                            // Refresh the record to get updated data
                            $record->refresh();
                            
                            Notification::make()
                                ->title("User berhasil " . ($newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan'))
                                ->success()
                                ->send();
                            
                            // Refresh the table to show updated status
                            $livewire->dispatch('$refresh');
                        })
                        ->requiresConfirmation()
                        ->modalHeading(function ($record) {
                            return $record->status === 'active' ? 'Nonaktifkan User' : 'Aktifkan User';
                        })
                        ->modalDescription(function ($record) {
                            $action = $record->status === 'active' ? 'menonaktifkan' : 'mengaktifkan';
                            return "Apakah Anda yakin ingin {$action} user {$record->name}?";
                        })
                        ->modalSubmitActionLabel(function ($record) {
                            return $record->status === 'active' ? 'Nonaktifkan' : 'Aktifkan';
                        })
                        ->modalCancelActionLabel('Cancel')
                        ->visible(function ($record) {
                            if (static::isSuperAdmin()) {
                                return true;
                            }
                            return !static::isTargetUserSuperAdmin($record);
                        }),

                    Tables\Actions\Action::make('manage_payroll')
                        ->label('Kelola Gaji')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->url(function ($record) {
                            $latestPayroll = $record->payrolls()->latest()->first();
                            if ($latestPayroll) {
                                // Jika sudah ada payroll, redirect ke edit
                                return route('filament.admin.resources.payrolls.edit', $latestPayroll);
                            } else {
                                // Jika belum ada payroll, redirect ke create dengan user_id
                                return route('filament.admin.resources.payrolls.create', ['user_id' => $record->id]);
                            }
                        })
                        ->openUrlInNewTab()
                        ->tooltip(function ($record) {
                            $latestPayroll = $record->payrolls()->latest()->first();
                            if ($latestPayroll) {
                                return sprintf(
                                    "Gaji saat ini: %s\nKlik untuk edit",
                                    'Rp ' . number_format($latestPayroll->monthly_salary, 0, '.', '.')
                                );
                            }
                            return 'Belum ada data gaji. Klik untuk menambah.';
                        }),

                    Tables\Actions\Action::make('view_salary_history')
                        ->label('Riwayat Gaji')
                        ->icon('heroicon-o-chart-bar')
                        ->color('info')
                        ->modalHeading(function ($record) {
                            return "Riwayat Gaji - {$record->name}";
                        })
                        ->modalContent(function ($record) {
                            $payrolls = $record->payrolls()->orderBy('created_at', 'desc')->get();
                            
                            if ($payrolls->isEmpty()) {
                                return view('filament.modals.no-payroll-history');
                            }
                            
                            return view('filament.modals.salary-history', [
                                'payrolls' => $payrolls,
                                'user' => $record
                            ]);
                        })
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Tutup')
                        ->visible(function ($record) {
                            return $record->payrolls()->exists();
                        }),
                    
                    Tables\Actions\Action::make('deactivate_user')
                        ->label('Nonaktifkan Permanen')
                        ->icon('heroicon-o-archive-box-x-mark')
                        ->color('danger')
                        ->action(function ($record): void {
                            $record->update([
                                'status' => 'terminated',
                                'expire_date' => now(),
                                'last_working_date' => now()->toDateString(),
                            ]);
                            
                            Notification::make()
                                ->title("User {$record->name} berhasil dinonaktifkan permanen")
                                ->body('User telah dinonaktifkan dan tidak dapat mengakses sistem.')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Nonaktifkan User Permanen')
                        ->modalDescription(function ($record) {
                            return "Apakah Anda yakin ingin menonaktifkan {$record->name} secara permanen? User tidak akan bisa mengakses sistem lagi, namun data historis akan tetap tersimpan.";
                        })
                        ->visible(function ($record) {
                            return $record->status !== 'terminated' && (
                                static::isSuperAdmin() || !static::isTargetUserSuperAdmin($record)
                            );
                        }),
                    
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->before(function ($record) {
                            // Check for foreign key constraints before deletion
                            $tablesToCheck = [
                                'nota_dinas' => ['approved_by', 'created_by'],
                                'leave_requests' => ['user_id', 'replacement_employee_id'],
                                'payrolls' => ['user_id'],
                                'leave_balances' => ['user_id'],
                                'annual_summaries' => ['user_id'],
                            ];
                            
                            $constraintTables = [];
                            foreach ($tablesToCheck as $table => $columns) {
                                foreach ($columns as $column) {
                                    $count = DB::table($table)->where($column, $record->id)->count();
                                    if ($count > 0) {
                                        $constraintTables[] = $table;
                                        break;
                                    }
                                }
                            }
                            
                            if (!empty($constraintTables)) {
                                $tableList = implode(', ', $constraintTables);
                                throw new \Exception("User tidak dapat dihapus karena masih memiliki data terkait di tabel: $tableList");
                            }
                        })
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('User berhasil dihapus')
                        )
                        ->visible(function ($record) {
                            // Super admin can delete anyone
                            if (static::isSuperAdmin()) {
                                return true;
                            }
                            // Non-super admin cannot delete super admin users
                            return !static::isTargetUserSuperAdmin($record);
                        }),
                ])
                    ->label('Aksi')
                    ->color('primary')
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->size('sm')
                    ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->action(function ($records, $livewire) {
                            // Filter out super admin users if current user is not super admin
                            if (!static::isSuperAdmin()) {
                                $records = $records->filter(function ($record) {
                                    return !static::isTargetUserSuperAdmin($record);
                                });
                            }
                            
                            $deletedCount = 0;
                            $failedCount = 0;
                            $failedUsers = [];
                            
                            foreach ($records as $record) {
                                try {
                                    // Check for foreign key constraints before deletion
                                    $hasConstraints = false;
                                    $constraintTables = [];
                                    
                                    // Check common tables that might reference users
                                    $tablesToCheck = [
                                        'nota_dinas' => ['approved_by', 'created_by'],
                                        'leave_requests' => ['user_id', 'replacement_employee_id'],
                                        'payrolls' => ['user_id'],
                                        'leave_balances' => ['user_id'],
                                        'annual_summaries' => ['user_id'],
                                    ];
                                    
                                    foreach ($tablesToCheck as $table => $columns) {
                                        foreach ($columns as $column) {
                                            $count = DB::table($table)->where($column, $record->id)->count();
                                            if ($count > 0) {
                                                $hasConstraints = true;
                                                $constraintTables[] = $table;
                                                break;
                                            }
                                        }
                                    }
                                    
                                    if ($hasConstraints) {
                                        $failedCount++;
                                        $failedUsers[] = [
                                            'name' => $record->name,
                                            'tables' => array_unique($constraintTables)
                                        ];
                                    } else {
                                        $record->delete();
                                        $deletedCount++;
                                    }
                                } catch (\Exception $e) {
                                    $failedCount++;
                                    $failedUsers[] = [
                                        'name' => $record->name,
                                        'error' => 'Database constraint error'
                                    ];
                                }
                            }
                            
                            // Show appropriate notifications
                            if ($deletedCount > 0) {
                                Notification::make()
                                    ->title("$deletedCount user berhasil dihapus")
                                    ->success()
                                    ->send();
                            }
                            
                            if ($failedCount > 0) {
                                $failedNames = collect($failedUsers)->pluck('name')->join(', ');
                                Notification::make()
                                    ->title("$failedCount user tidak dapat dihapus")
                                    ->body("User berikut masih memiliki data terkait: $failedNames")
                                    ->warning()
                                    ->persistent()
                                    ->send();
                            }
                            
                            // Refresh the table
                            $livewire->dispatch('$refresh');
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Hapus User Terpilih')
                        ->modalDescription('User yang memiliki data terkait (nota dinas, cuti, gaji, dll) tidak akan dihapus untuk menjaga integritas data.')
                        ->deselectRecordsAfterCompletion(),
                    
                    Tables\Actions\BulkAction::make('bulk_reset_password')
                        ->label('Reset Password Massal')
                        ->icon('heroicon-o-key')
                        ->color('warning')
                        ->form([
                            Forms\Components\TextInput::make('new_password')
                                ->label('Password Baru')
                                ->password()
                                ->required()
                                ->minLength(8)
                                ->maxLength(255)
                                ->helperText('Password ini akan diterapkan ke semua user yang dipilih'),
                            Forms\Components\TextInput::make('confirm_password')
                                ->label('Konfirmasi Password')
                                ->password()
                                ->required()
                                ->same('new_password'),
                        ])
                        ->action(function (array $data, $records, $livewire): void {
                            // Filter out super admin users if current user is not super admin
                            if (!static::isSuperAdmin()) {
                                $records = $records->filter(function ($record) {
                                    return !static::isTargetUserSuperAdmin($record);
                                });
                            }
                            
                            $count = 0;
                            foreach ($records as $record) {
                                $record->update([
                                    'password' => Hash::make($data['new_password']),
                                ]);
                                $count++;
                            }
                            
                            Notification::make()
                                ->title("Password $count user berhasil direset")
                                ->success()
                                ->send();
                            
                            // Refresh the table
                            $livewire->dispatch('$refresh');
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Reset Password Massal')
                        ->modalDescription('Apakah Anda yakin ingin mereset password untuk semua user yang dipilih?')
                        ->modalSubmitAction(fn ($action) => $action->color('purple')->label('Reset Password'))
                        ->deselectRecordsAfterCompletion(),
                    
                    Tables\Actions\BulkAction::make('bulk_toggle_status')
                        ->label('Toggle Status Massal')
                        ->icon('heroicon-o-arrow-path')
                        ->color('secondary')
                        ->action(function ($records, $livewire): void {
                            // Filter out super admin users if current user is not super admin
                            if (!static::isSuperAdmin()) {
                                $records = $records->filter(function ($record) {
                                    return !static::isTargetUserSuperAdmin($record);
                                });
                            }
                            
                            $count = 0;
                            foreach ($records as $record) {
                                $newStatus = $record->status === 'active' ? 'inactive' : 'active';
                                $record->update(['status' => $newStatus]);
                                $count++;
                            }
                            
                            Notification::make()
                                ->title("Status $count user berhasil diubah")
                                ->success()
                                ->send();
                            
                            // Refresh the table to show updated statuses
                            $livewire->dispatch('$refresh');
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Toggle Status Massal')
                        ->modalDescription('Apakah Anda yakin ingin mengubah status semua user yang dipilih?')
                        ->deselectRecordsAfterCompletion(),
                    
                    Tables\Actions\BulkAction::make('bulk_deactivate_permanent')
                        ->label('Nonaktifkan Permanen')
                        ->icon('heroicon-o-archive-box-x-mark')
                        ->color('danger')
                        ->action(function ($records, $livewire): void {
                            // Filter out super admin users if current user is not super admin
                            if (!static::isSuperAdmin()) {
                                $records = $records->filter(function ($record) {
                                    return !static::isTargetUserSuperAdmin($record);
                                });
                            }
                            
                            // Filter out already terminated users
                            $records = $records->filter(function ($record) {
                                return $record->status !== 'terminated';
                            });
                            
                            $count = 0;
                            foreach ($records as $record) {
                                $record->update([
                                    'status' => 'terminated',
                                    'expire_date' => now(),
                                    'last_working_date' => now()->toDateString(),
                                ]);
                                $count++;
                            }
                            
                            Notification::make()
                                ->title("$count user berhasil dinonaktifkan permanen")
                                ->body('User telah dinonaktifkan dan tidak dapat mengakses sistem, namun data historis tetap tersimpan.')
                                ->success()
                                ->send();
                            
                            // Refresh the table to show updated statuses
                            $livewire->dispatch('$refresh');
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Nonaktifkan User Permanen')
                        ->modalDescription('User akan dinonaktifkan permanen namun data historis tetap tersimpan. Ini lebih aman daripada menghapus user yang memiliki data terkait.')
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->striped()
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([10, 25, 50, 100])
            ->defaultSort('created_at', 'desc')
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession()
            ->extremePaginationLinks()
            ->selectCurrentPageOnly()
            ->recordTitleAttribute('name')
            ->searchOnBlur()
            ->deferLoading();
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
            // AccountManagerStats::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Total user';
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
