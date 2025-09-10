<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayrollResource\Pages;
use App\Models\Payroll;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PayrollResource extends Resource
{
    protected static ?string $model = Payroll::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Payroll';

    protected static ?string $modelLabel = 'Payroll';

    protected static ?string $pluralModelLabel = 'Payroll';

    /**
     * Parse currency string to float value
     * Handles various formats like: 6,000,000.00, 6.000.000, 6000000
     */
    private static function parseCurrencyToFloat(?string $value): float
    {
        if (!$value) return 0;
        
        // Remove all non-digit characters except dots
        $clean = preg_replace('/[^\d.]/', '', $value);
        
        if (empty($clean)) return 0;
        
        // Count dots to determine format
        $dotCount = substr_count($clean, '.');
        
        if ($dotCount === 0) {
            // No dots - simple number
            return (float) $clean;
        } else if ($dotCount === 1) {
            $parts = explode('.', $clean);
            $decimalPart = $parts[1];
            
            // If decimal part is exactly 2 digits (likely cents) and ends with 00, treat as integer
            if (strlen($decimalPart) === 2 && $decimalPart === '00') {
                return (float) $parts[0];
            }
            // If decimal part is more than 2 digits, treat dot as thousands separator
            else if (strlen($decimalPart) > 2) {
                return (float) implode('', $parts);
            }
            // Otherwise treat as decimal
            else {
                return (float) $clean;
            }
        } else {
            // Multiple dots - treat all but last as thousands separators
            $parts = explode('.', $clean);
            $lastPart = array_pop($parts);
            
            // If last part is "00" or similar, ignore it (it's just .00)
            if (strlen($lastPart) === 2 && $lastPart === '00') {
                return (float) implode('', $parts);
            }
            // If last part is more than 2 digits, it's part of the number
            else if (strlen($lastPart) > 2) {
                return (float) implode('', array_merge($parts, [$lastPart]));
            }
            // Otherwise treat as decimal
            else {
                return (float) (implode('', $parts) . '.' . $lastPart);
            }
        }
    }

    protected static ?string $navigationGroup = 'Human Resource';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Employee Information Section
                Forms\Components\Section::make('Informasi Karyawan')
                    ->description('Pilih karyawan dan periode payroll')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->label('Karyawan')
                                    ->relationship('user', 'name', function (Builder $query) {
                                        return $query->with('status');
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->getOptionLabelFromRecordUsing(function (User $record): string {
                                        $statusName = $record->status?->status_name ?? $record->department ?? 'No Status';
                                        $email = $record->email ? " - {$record->email}" : '';
                                        return "{$record->name} ({$statusName}){$email}";
                                    })
                                    ->helperText('Pilih karyawan yang akan dibuatkan payroll')
                                    ->columnSpan(2),
                                
                                Forms\Components\Group::make([
                                    Forms\Components\Select::make('period_month')
                                        ->label('Bulan Periode')
                                        ->options([
                                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                        ])
                                        ->default(now()->month)
                                        ->required()
                                        ->live()
                                        ->helperText('Pilih bulan periode payroll'),
                                    
                                    Forms\Components\Select::make('period_year')
                                        ->label('Tahun Periode')
                                        ->options(function () {
                                            $currentYear = now()->year;
                                            $years = [];
                                            for ($year = $currentYear - 1; $year <= $currentYear + 1; $year++) {
                                                $years[$year] = $year;
                                            }
                                            return $years;
                                        })
                                        ->default(now()->year)
                                        ->required()
                                        ->live()
                                        ->helperText('Pilih tahun periode payroll'),
                                ])
                                    ->columnSpan(1),
                            ]),
                        
                        Forms\Components\Placeholder::make('employee_info')
                            ->label('Info Karyawan')
                            ->content(function (Forms\Get $get): string {
                                $userId = $get('user_id');
                                if (!$userId) {
                                    return 'Pilih karyawan untuk melihat informasi';
                                }
                                
                                $user = User::with('status')->find($userId);
                                if (!$user) {
                                    return 'Karyawan tidak ditemukan';
                                }
                                
                                $hireDate = $user->hire_date?->format('d/m/Y') ?? 'No Date';
                                
                                // Check if payroll already exists for this period
                                $month = $get('period_month');
                                $year = $get('period_year');
                                
                                $existingPayroll = null;
                                if ($month && $year) {
                                    $existingPayroll = \App\Models\Payroll::where('user_id', $userId)
                                        ->where('period_month', $month)
                                        ->where('period_year', $year)
                                        ->first();
                                }
                                
                                $info = "📅 Mulai kerja: {$hireDate}";
                                
                                if ($existingPayroll) {
                                    $months = [
                                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                    ];
                                    $monthName = $months[$month];
                                    $info .= "\n⚠️ Payroll untuk {$monthName} {$year} sudah ada!";
                                }
                                
                                return $info;
                            })
                            ->visible(fn (Forms\Get $get): bool => (bool) $get('user_id')),
                    ])->columns(1),

                // Salary Information Section
                Forms\Components\Section::make('Informasi Gaji')
                    ->description('Pengaturan gaji bulanan dan tahunan')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\TextInput::make('gaji_pokok')
                                    ->label('Gaji Pokok')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->suffixIcon('heroicon-m-currency-dollar')
                                    ->placeholder('4000000')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->live(onBlur: true)
                                    ->dehydrateStateUsing(fn ($state): ?float => static::parseCurrencyToFloat($state))
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state, $record) {
                                        // Hitung monthly_salary otomatis: (gaji_pokok + tunjangan + bonus) - pengurangan
                                        $gajiPokok = static::parseCurrencyToFloat($state) ?? 0;
                                        $tunjangan = static::parseCurrencyToFloat($get('tunjangan')) ?? 0;
                                        $bonus = static::parseCurrencyToFloat($get('bonus')) ?? 0;
                                        $pengurangan = static::parseCurrencyToFloat($get('pengurangan')) ?? 0;
                                        $monthlySalary = $gajiPokok + $tunjangan + $bonus - $pengurangan;
                                        
                                        $set('monthly_salary', number_format($monthlySalary, 0, ',', '.'));
                                        
                                        // Buat instance sementara untuk menggunakan accessor
                                        $tempPayroll = new \App\Models\Payroll();
                                        $tempPayroll->monthly_salary = $monthlySalary;
                                        
                                        // Gunakan accessor untuk perhitungan (bonus sudah termasuk dalam monthly_salary)
                                        $set('annual_salary', $tempPayroll->formatted_annual_salary);
                                        $set('total_compensation', $tempPayroll->formatted_total_compensation);
                                    })
                                    ->helperText('Gaji pokok tanpa tunjangan'),

                                Forms\Components\TextInput::make('tunjangan')
                                    ->label('Tunjangan')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->suffixIcon('heroicon-m-plus')
                                    ->placeholder('1000000')
                                    ->default(0)
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->live(onBlur: true)
                                    ->dehydrateStateUsing(fn ($state): ?float => static::parseCurrencyToFloat($state))
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state, $record) {
                                        // Hitung monthly_salary otomatis: (gaji_pokok + tunjangan + bonus) - pengurangan
                                        $gajiPokok = static::parseCurrencyToFloat($get('gaji_pokok')) ?? 0;
                                        $tunjangan = static::parseCurrencyToFloat($state) ?? 0;
                                        $bonus = static::parseCurrencyToFloat($get('bonus')) ?? 0;
                                        $pengurangan = static::parseCurrencyToFloat($get('pengurangan')) ?? 0;
                                        $monthlySalary = $gajiPokok + $tunjangan + $bonus - $pengurangan;
                                        
                                        $set('monthly_salary', number_format($monthlySalary, 0, ',', '.'));
                                        
                                        // Buat instance sementara untuk menggunakan accessor
                                        $tempPayroll = new \App\Models\Payroll();
                                        $tempPayroll->monthly_salary = $monthlySalary;
                                        
                                        // Gunakan accessor untuk perhitungan (bonus sudah termasuk dalam monthly_salary)
                                        $set('annual_salary', $tempPayroll->formatted_annual_salary);
                                        $set('total_compensation', $tempPayroll->formatted_total_compensation);
                                    })
                                    ->helperText('Tunjangan dan benefit lainnya'),

                                Forms\Components\TextInput::make('pengurangan')
                                    ->label('Pengurangan')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->suffixIcon('heroicon-m-minus')
                                    ->placeholder('BPJS, keterlambatan dan lainnya')
                                    ->default(0)
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->live(onBlur: true)
                                    ->dehydrateStateUsing(fn ($state): ?float => static::parseCurrencyToFloat($state))
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state, $record) {
                                        // Hitung monthly_salary otomatis: (gaji_pokok + tunjangan + bonus) - pengurangan
                                        $gajiPokok = static::parseCurrencyToFloat($get('gaji_pokok')) ?? 0;
                                        $tunjangan = static::parseCurrencyToFloat($get('tunjangan')) ?? 0;
                                        $bonus = static::parseCurrencyToFloat($get('bonus')) ?? 0;
                                        $pengurangan = static::parseCurrencyToFloat($state) ?? 0;
                                        $monthlySalary = $gajiPokok + $tunjangan + $bonus - $pengurangan;
                                        
                                        $set('monthly_salary', number_format($monthlySalary, 0, ',', '.'));
                                        
                                        // Buat instance sementara untuk menggunakan accessor
                                        $tempPayroll = new \App\Models\Payroll();
                                        $tempPayroll->monthly_salary = $monthlySalary;
                                        
                                        // Gunakan accessor untuk perhitungan (bonus sudah termasuk dalam monthly_salary)
                                        $set('annual_salary', $tempPayroll->formatted_annual_salary);
                                        $set('total_compensation', $tempPayroll->formatted_total_compensation);
                                    })
                                    ->helperText('BPJS, keterlambatan dan lainnya'),
                                    
                                Forms\Components\TextInput::make('monthly_salary')
                                    ->label('Total Gaji Bulanan')
                                    ->prefix('Rp')
                                    ->suffixIcon('heroicon-m-calculator')
                                    ->readOnly()
                                    ->dehydrated(false)
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->afterStateHydrated(function (Forms\Components\TextInput $component, $state, $record) {
                                        if ($record) {
                                            // Edit mode: gunakan nilai dari database
                                            $component->state(number_format($record->monthly_salary, 0, ',', '.'));
                                        }
                                    })
                                    ->helperText('Otomatis: (Gaji Pokok + Tunjangan + Bonus) - Pengurangan')
                                    ->extraAttributes(['class' => 'bg-blue-50'])
                                    ->disabled(),
                            ]),
                        
                        Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\TextInput::make('annual_salary')
                                    ->label('Gaji Tahunan')
                                    ->prefix('Rp')
                                    ->suffixIcon('heroicon-m-calendar')
                                    ->readOnly()
                                    ->dehydrated(false)
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->afterStateHydrated(function (Forms\Components\TextInput $component, $state, $record) {
                                        if ($record) {
                                            // Edit mode: gunakan accessor dari model
                                            $component->state($record->formatted_annual_salary);
                                        }
                                    })
                                    ->helperText('Otomatis dihitung oleh sistem: Gaji Bulanan × 12 bulan')
                                    ->extraAttributes(['class' => 'bg-gray-50'])
                                    ->disabled(),
                            ]),
                        
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('bonus')
                                    ->label('Bonus')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->suffixIcon('heroicon-m-gift')
                                    ->placeholder('1000000')
                                    ->default(0)
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->dehydrateStateUsing(fn ($state): ?float => static::parseCurrencyToFloat($state))
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        // Hitung monthly_salary otomatis: (gaji_pokok + tunjangan + bonus) - pengurangan
                                        $gajiPokok = static::parseCurrencyToFloat($get('gaji_pokok')) ?? 0;
                                        $tunjangan = static::parseCurrencyToFloat($get('tunjangan')) ?? 0;
                                        $bonus = static::parseCurrencyToFloat($state) ?? 0;
                                        $pengurangan = static::parseCurrencyToFloat($get('pengurangan')) ?? 0;
                                        $monthlySalary = $gajiPokok + $tunjangan + $bonus - $pengurangan;
                                        
                                        $set('monthly_salary', number_format($monthlySalary, 0, ',', '.'));
                                        
                                        // Buat instance sementara untuk menggunakan accessor
                                        $tempPayroll = new \App\Models\Payroll();
                                        $tempPayroll->monthly_salary = $monthlySalary;
                                        
                                        // Gunakan accessor untuk perhitungan (bonus sudah termasuk dalam monthly_salary)
                                        $set('annual_salary', $tempPayroll->formatted_annual_salary);
                                        $set('total_compensation', $tempPayroll->formatted_total_compensation);
                                    })
                                    ->helperText('Bonus bulanan (termasuk dalam gaji bulanan)'),
                                
                                Forms\Components\TextInput::make('total_compensation')
                                    ->label('Total Kompensasi')
                                    ->prefix('Rp')
                                    ->suffixIcon('heroicon-m-calculator')
                                    ->readOnly()
                                    ->dehydrated(false)
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->live()
                                    ->afterStateHydrated(function (Forms\Components\TextInput $component, $state, $record) {
                                        if ($record) {
                                            // Edit mode: gunakan accessor dari model
                                            $component->state($record->formatted_total_compensation);
                                        }
                                    })
                                    ->helperText('Total: Gaji Tahunan + Bonus (dihitung otomatis)')
                                    ->extraAttributes(['class' => 'bg-gray-50'])
                                    ->disabled(),
                            ]),
                    ]),

                // Review Information Section
                Forms\Components\Section::make('Informasi Review')
                    ->description('Jadwal review gaji dan performa')
                    ->icon('heroicon-o-calendar-days')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('last_review_date')
                                    ->label('Tanggal Review Terakhir')
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->helperText('Kapan terakhir kali direview'),
                                
                                Forms\Components\DatePicker::make('next_review_date')
                                    ->label('Tanggal Review Berikutnya')
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->helperText('Jadwal review berikutnya')
                                    ->afterOrEqual('today'),
                            ]),
                        
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->placeholder('Catatan tambahan mengenai payroll ini...')
                            ->rows(3)
                            ->maxLength(1000)
                            ->helperText('Catatan internal (maksimal 1000 karakter)'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $user = Auth::user();
                // Jika bukan super_admin atau finance, hanya tampilkan data payroll milik user yang login
                if ($user && !$user->roles->contains('name', 'super_admin') && !$user->roles->contains('name', 'finance')) {
                    $query->where('user_id', $user->id);
                }
            })
            ->heading('Data Payroll')
            ->description('Kelola data payroll karyawan. Default menampilkan data bulan berjalan, gunakan filter atau tombol aksi cepat untuk melihat periode lain.')
            ->columns([
                Tables\Columns\ImageColumn::make('user.avatar_url')
                    ->label('Avatar')
                    ->circular()
                    ->size(40)
                    ->defaultImageUrl(function ($record) {
                        $name = $record->user?->name ?? 'User';
                        $initials = collect(explode(' ', $name))
                            ->map(fn($word) => strtoupper(substr($word, 0, 1)))
                            ->take(2)
                            ->implode('');
                        return "https://ui-avatars.com/api/?name={$initials}&background=3b82f6&color=ffffff&size=128";
                    }),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Karyawan')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->description(fn ($record): string => $record->user?->email ?? ''),
                
                Tables\Columns\TextColumn::make('periode')
                    ->label('Periode')
                    ->getStateUsing(fn ($record): string => $record->period_name)
                    ->badge()
                    ->color('primary')
                    ->sortable(['period_year', 'period_month']),
                
                Tables\Columns\TextColumn::make('user.status.status_name')
                    ->label('Status Jabatan')
                    ->badge()
                    ->color(function ($state): string {
                        return match ($state) {
                            'Admin' => 'danger',
                            'Finance' => 'warning',
                            'HRD' => 'info',
                            'Account Manager' => 'primary',
                            'Staff' => 'success',
                            default => 'gray',
                        };
                    })
                    ->placeholder('No Status'),
                
                Tables\Columns\TextColumn::make('user.department')
                    ->label('Departemen')
                    ->badge()
                    ->color(function ($state): string {
                        return match ($state) {
                            'bisnis' => 'success',
                            'operasional' => 'primary',
                            default => 'gray',
                        };
                    })
                    ->formatStateUsing(function ($state): string {
                        return match ($state) {
                            'bisnis' => 'Bisnis',
                            'operasional' => 'Operasional',
                            default => $state,
                        };
                    }),
                
                Tables\Columns\TextColumn::make('gaji_pokok')
                    ->label('Gaji Pokok')
                    ->money('IDR')
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->color('info')
                    ->placeholder('Rp 0'),
                
                Tables\Columns\TextColumn::make('tunjangan')
                    ->label('Tunjangan')
                    ->money('IDR')
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->color('warning')
                    ->placeholder('Rp 0'),
                
                Tables\Columns\TextColumn::make('pengurangan')
                    ->label('Pengurangan')
                    ->money('IDR')
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->color('danger')
                    ->placeholder('Rp 0')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('monthly_salary')
                    ->label('Total Gaji')
                    ->money('IDR')
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->color('success')
                    ->description(function ($record): string {
                        $gajiPokok = number_format($record->gaji_pokok ?? 0, 0, ',', '.');
                        $tunjangan = number_format($record->tunjangan ?? 0, 0, ',', '.');
                        $bonus = number_format($record->bonus ?? 0, 0, ',', '.');
                        $pengurangan = number_format($record->pengurangan ?? 0, 0, ',', '.');
                        return "({$gajiPokok} + {$tunjangan} + {$bonus}) - {$pengurangan}";
                    }),
                
                Tables\Columns\TextColumn::make('annual_salary')
                    ->label('Gaji Tahunan')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('bonus')
                    ->label('Bonus')
                    ->money('IDR')
                    ->sortable()
                    ->placeholder('Rp 0')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('total_compensation')
                    ->label('Total Kompensasi')
                    ->money('IDR')
                    ->sortable()
                    ->getStateUsing(function ($record): float {
                        return $record->total_compensation; // Menggunakan accessor dari model
                    })
                    ->weight(FontWeight::Bold)
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('last_review_date')
                    ->label('Review Terakhir')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('Belum pernah')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('next_review_date')
                    ->label('Review Berikutnya')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('Belum dijadwalkan')
                    ->color(function ($record): string {
                        if (!$record->next_review_date) return 'gray';
                        $nextReview = $record->next_review_date;
                        $daysUntil = now()->diffInDays($nextReview, false);
                        
                        if ($daysUntil < 0) return 'danger'; // Overdue
                        if ($daysUntil <= 7) return 'warning'; // Soon
                        return 'success'; // Good
                    })
                    ->badge(function ($record): bool {
                        if (!$record->next_review_date) return false;
                        $daysUntil = now()->diffInDays($record->next_review_date, false);
                        return $daysUntil <= 7; // Show badge if within 7 days
                    }),
                
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
                Tables\Filters\SelectFilter::make('user')
                    ->label('Karyawan')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\SelectFilter::make('department')
                    ->label('Departemen')
                    ->options([
                        'bisnis' => 'Bisnis',
                        'operasional' => 'Operasional',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['value']) && $data['value']) {
                            return $query->whereHas('user', function (Builder $query) use ($data) {
                                $query->where('department', $data['value']);
                            });
                        }
                        return $query;
                    }),
                
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status Jabatan')
                    ->relationship('user.status', 'status_name')
                    ->preload(),
                
                Tables\Filters\Filter::make('monthly_salary_range')
                    ->label('Range Gaji Bulanan')
                    ->form([
                        Forms\Components\TextInput::make('monthly_salary_from')
                            ->label('Dari')
                            ->numeric()
                            ->prefix('Rp'),
                        Forms\Components\TextInput::make('monthly_salary_to')
                            ->label('Sampai')
                            ->numeric()
                            ->prefix('Rp'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['monthly_salary_from'],
                                fn (Builder $query, $salary): Builder => $query->where('monthly_salary', '>=', $salary),
                            )
                            ->when(
                                $data['monthly_salary_to'],
                                fn (Builder $query, $salary): Builder => $query->where('monthly_salary', '<=', $salary),
                            );
                    }),
                
                Tables\Filters\Filter::make('review_due')
                    ->label('Review Mendekati')
                    ->query(fn (Builder $query): Builder => $query->whereDate('next_review_date', '<=', now()->addDays(30)))
                    ->toggle(),
                
                Tables\Filters\SelectFilter::make('period_month')
                    ->label('Bulan Periode')
                    ->placeholder('Semua bulan')
                    ->options([
                        '1' => 'Januari',
                        '2' => 'Februari', 
                        '3' => 'Maret',
                        '4' => 'April',
                        '5' => 'Mei',
                        '6' => 'Juni',
                        '7' => 'Juli',
                        '8' => 'Agustus',
                        '9' => 'September',
                        '10' => 'Oktober',
                        '11' => 'November',
                        '12' => 'Desember',
                    ])
                    ->default(now()->month)
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['value']) && $data['value']) {
                            return $query->where('period_month', $data['value']);
                        }
                        return $query;
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['value']) {
                            return null;
                        }
                        
                        $months = [
                            '1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April',
                            '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus',
                            '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                        ];
                        
                        return 'Bulan: ' . $months[$data['value']];
                    }),
                
                Tables\Filters\SelectFilter::make('period_year')
                    ->label('Tahun Periode')
                    ->placeholder('Semua tahun')
                    ->options(function () {
                        $currentYear = now()->year;
                        $years = [];
                        for ($year = $currentYear - 2; $year <= $currentYear + 1; $year++) {
                            $years[$year] = $year;
                        }
                        return $years;
                    })
                    ->default(now()->year)
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['value']) && $data['value']) {
                            return $query->where('period_year', $data['value']);
                        }
                        return $query;
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['value']) {
                            return null;
                        }
                        
                        return 'Tahun: ' . $data['value'];
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('current_month')
                    ->label('Bulan Ini')
                    ->icon('heroicon-o-calendar')
                    ->color('primary')
                    ->action(function () {
                        return redirect()->route('filament.admin.resources.payrolls.index', [
                            'tableFilters' => [
                                'period_month' => ['value' => now()->month],
                                'period_year' => ['value' => now()->year],
                            ]
                        ]);
                    }),
                
                Tables\Actions\Action::make('last_month')
                    ->label('Bulan Lalu')
                    ->icon('heroicon-o-arrow-left')
                    ->color('gray')
                    ->action(function () {
                        $lastMonth = now()->subMonth();
                        return redirect()->route('filament.admin.resources.payrolls.index', [
                            'tableFilters' => [
                                'period_month' => ['value' => $lastMonth->month],
                                'period_year' => ['value' => $lastMonth->year],
                            ]
                        ]);
                    }),
                
                Tables\Actions\Action::make('two_months_ago')
                    ->label('2 Bulan Lalu')
                    ->icon('heroicon-o-arrow-left')
                    ->color('gray')
                    ->action(function () {
                        $twoMonthsAgo = now()->subMonths(2);
                        return redirect()->route('filament.admin.resources.payrolls.index', [
                            'tableFilters' => [
                                'period_month' => ['value' => $twoMonthsAgo->month],
                                'period_year' => ['value' => $twoMonthsAgo->year],
                            ]
                        ]);
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat')
                        ->color('info'),
                    
                    Tables\Actions\EditAction::make()
                        ->label('Edit')
                        ->color('warning'),
                    
                    Tables\Actions\Action::make('salary_raise')
                        ->label('Kenaikan Gaji')
                        ->icon('heroicon-o-arrow-trending-up')
                        ->color('success')
                        ->visible(function () {
                            $user = Auth::user();
                            return $user && $user->roles->contains('name', 'super_admin');
                        })
                        ->form([
                            Forms\Components\Select::make('raise_type')
                                ->label('Jenis Kenaikan')
                                ->options([
                                    'percentage' => 'Persentase (%)',
                                    'amount' => 'Nominal (Rp)',
                                ])
                                ->required()
                                ->live(),
                            
                            Forms\Components\TextInput::make('raise_value')
                                ->label('Nilai Kenaikan')
                                ->required()
                                ->numeric()
                                ->suffix(fn (Forms\Get $get): string => $get('raise_type') === 'percentage' ? '%' : '')
                                ->prefix(fn (Forms\Get $get): string => $get('raise_type') === 'amount' ? 'Rp ' : ''),
                            
                            Forms\Components\Textarea::make('raise_reason')
                                ->label('Alasan Kenaikan')
                                ->placeholder('Contoh: Promosi, review tahunan, kinerja excellent')
                                ->required(),
                        ])
                        ->action(function (array $data, $record): void {
                            $currentSalary = $record->monthly_salary;
                            
                            if ($data['raise_type'] === 'percentage') {
                                $newSalary = $currentSalary * (1 + ($data['raise_value'] / 100));
                            } else {
                                $newSalary = $currentSalary + $data['raise_value'];
                            }
                            
                            $record->update([
                                'monthly_salary' => $newSalary,
                                'last_review_date' => now(),
                                'next_review_date' => now()->addYear(),
                                'notes' => ($record->notes ? $record->notes . "\n\n" : '') . 
                                          "[" . now()->format('d/m/Y') . "] Kenaikan gaji: " . 
                                          "Rp " . number_format($currentSalary, 0, ',', '.') . " → " . 
                                          "Rp " . number_format($newSalary, 0, ',', '.') . 
                                          " (" . $data['raise_reason'] . ")"
                            ]);
                            
                            Notification::make()
                                ->title('Kenaikan Gaji Berhasil')
                                ->body("Gaji {$record->user->name} berhasil dinaikkan menjadi Rp " . number_format($newSalary, 0, ',', '.'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Kenaikan Gaji Karyawan')
                        ->modalDescription('Pastikan data kenaikan gaji sudah benar sebelum menyimpan.')
                        ->modalSubmitActionLabel('Terapkan Kenaikan'),
                    
                    Tables\Actions\Action::make('slip_gaji')
                        ->label('Slip Gaji')
                        ->icon('heroicon-o-document-text')
                        ->color('primary')
                        ->url(fn ($record) => route('payroll.slip-gaji.download', $record))
                        ->openUrlInNewTab(),
                    
                    Tables\Actions\Action::make('duplicate')
                        ->label('Duplikasi')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('info')
                        ->visible(function () {
                            $user = Auth::user();
                            return $user && ($user->roles->contains('name', 'super_admin') || $user->roles->contains('name', 'finance'));
                        })
                        ->form([
                            Forms\Components\Select::make('target_month')
                                ->label('Bulan Tujuan')
                                ->options([
                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                ])
                                ->default(function ($record) {
                                    $nextMonth = $record->period_month + 1;
                                    return $nextMonth > 12 ? 1 : $nextMonth;
                                })
                                ->required()
                                ->live()
                                ->helperText('Pilih bulan untuk payroll baru'),
                            
                            Forms\Components\Select::make('target_year')
                                ->label('Tahun Tujuan')
                                ->options(function () {
                                    $currentYear = now()->year;
                                    $years = [];
                                    for ($year = $currentYear - 1; $year <= $currentYear + 2; $year++) {
                                        $years[$year] = $year;
                                    }
                                    return $years;
                                })
                                ->default(function ($record) {
                                    return $record->period_month == 12 ? $record->period_year + 1 : $record->period_year;
                                })
                                ->required()
                                ->live()
                                ->helperText('Pilih tahun untuk payroll baru'),
                            
                            Forms\Components\Toggle::make('copy_bonus')
                                ->label('Salin Bonus')
                                ->default(false)
                                ->helperText('Apakah bonus ikut disalin? (biasanya bonus tidak rutin setiap bulan)'),
                            
                            Forms\Components\Toggle::make('reset_review_dates')
                                ->label('Reset Tanggal Review')
                                ->default(true)
                                ->helperText('Reset tanggal review terakhir dan berikutnya'),
                            
                            Forms\Components\Textarea::make('duplicate_notes')
                                ->label('Catatan Duplikasi')
                                ->placeholder('Catatan tambahan untuk payroll yang diduplikasi...')
                                ->rows(3)
                                ->helperText('Catatan opsional untuk payroll baru'),
                        ])
                        ->action(function (array $data, $record): void {
                            // Check if payroll already exists for target period
                            $existingPayroll = Payroll::where('user_id', $record->user_id)
                                ->where('period_month', $data['target_month'])
                                ->where('period_year', $data['target_year'])
                                ->first();
                            
                            if ($existingPayroll) {
                                $months = [
                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                ];
                                $monthName = $months[$data['target_month']];
                                
                                Notification::make()
                                    ->title('Duplikasi Gagal')
                                    ->body("Payroll untuk {$record->user->name} pada {$monthName} {$data['target_year']} sudah ada!")
                                    ->danger()
                                    ->send();
                                return;
                            }
                            
                            // Create duplicate payroll
                            $newPayroll = $record->replicate();
                            $newPayroll->period_month = $data['target_month'];
                            $newPayroll->period_year = $data['target_year'];
                            
                            // Handle bonus
                            if (!$data['copy_bonus']) {
                                $newPayroll->bonus = 0;
                            }
                            
                            // Handle review dates
                            if ($data['reset_review_dates']) {
                                $newPayroll->last_review_date = null;
                                $newPayroll->next_review_date = null;
                            }
                            
                            // Add duplicate notes
                            $originalPeriod = $record->period_name;
                            $duplicateNote = "[" . now()->format('d/m/Y H:i') . "] Diduplikasi dari payroll {$originalPeriod}";
                            
                            if ($data['duplicate_notes']) {
                                $newPayroll->notes = $duplicateNote . "\n\n" . $data['duplicate_notes'];
                            } else {
                                $newPayroll->notes = $duplicateNote;
                            }
                            
                            // Save new payroll
                            $newPayroll->save();
                            
                            $months = [
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                            ];
                            $targetPeriod = $months[$data['target_month']] . ' ' . $data['target_year'];
                            
                            Notification::make()
                                ->title('Duplikasi Berhasil')
                                ->body("Payroll {$record->user->name} berhasil diduplikasi ke periode {$targetPeriod}")
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Duplikasi Payroll')
                        ->modalDescription('Duplikasi akan membuat payroll baru dengan data yang sama untuk periode berbeda.')
                        ->modalSubmitActionLabel('Duplikasi Payroll')
                        ->modalIcon('heroicon-o-document-duplicate'),
                    
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus'),
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
                        ->label('Hapus Terpilih'),
                    
                    Tables\Actions\BulkAction::make('bulk_review_update')
                        ->label('Update Review Massal')
                        ->icon('heroicon-o-calendar')
                        ->color('info')
                        ->form([
                            Forms\Components\DatePicker::make('next_review_date')
                                ->label('Tanggal Review Berikutnya')
                                ->required()
                                ->native(false)
                                ->displayFormat('d/m/Y'),
                        ])
                        ->action(function (array $data, $records): void {
                            $count = 0;
                            foreach ($records as $record) {
                                $record->update([
                                    'next_review_date' => $data['next_review_date'],
                                ]);
                                $count++;
                            }
                            
                            Notification::make()
                                ->title('Review Update Berhasil')
                                ->body("Tanggal review untuk {$count} karyawan berhasil diupdate.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    
                    Tables\Actions\BulkAction::make('bulk_duplicate')
                        ->label('Duplikasi Massal')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('success')
                        ->visible(function () {
                            $user = Auth::user();
                            return $user && ($user->roles->contains('name', 'super_admin') || $user->roles->contains('name', 'finance'));
                        })
                        ->form([
                            Forms\Components\Select::make('target_month')
                                ->label('Bulan Tujuan')
                                ->options([
                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                ])
                                ->default(now()->addMonth()->month)
                                ->required()
                                ->helperText('Pilih bulan untuk payroll baru'),
                            
                            Forms\Components\Select::make('target_year')
                                ->label('Tahun Tujuan')
                                ->options(function () {
                                    $currentYear = now()->year;
                                    $years = [];
                                    for ($year = $currentYear; $year <= $currentYear + 2; $year++) {
                                        $years[$year] = $year;
                                    }
                                    return $years;
                                })
                                ->default(now()->addMonth()->year)
                                ->required()
                                ->helperText('Pilih tahun untuk payroll baru'),
                            
                            Forms\Components\Toggle::make('copy_bonus')
                                ->label('Salin Bonus')
                                ->default(false)
                                ->helperText('Apakah bonus ikut disalin?'),
                            
                            Forms\Components\Toggle::make('skip_existing')
                                ->label('Lewati yang Sudah Ada')
                                ->default(true)
                                ->helperText('Lewati karyawan yang sudah memiliki payroll di periode target'),
                        ])
                        ->action(function (array $data, $records): void {
                            $successCount = 0;
                            $skippedCount = 0;
                            $skippedNames = [];
                            
                            foreach ($records as $record) {
                                // Check if payroll already exists
                                $existingPayroll = Payroll::where('user_id', $record->user_id)
                                    ->where('period_month', $data['target_month'])
                                    ->where('period_year', $data['target_year'])
                                    ->first();
                                
                                if ($existingPayroll && $data['skip_existing']) {
                                    $skippedCount++;
                                    $skippedNames[] = $record->user->name;
                                    continue;
                                }
                                
                                if ($existingPayroll && !$data['skip_existing']) {
                                    $skippedCount++;
                                    $skippedNames[] = $record->user->name . ' (sudah ada)';
                                    continue;
                                }
                                
                                // Create duplicate
                                $newPayroll = $record->replicate();
                                $newPayroll->period_month = $data['target_month'];
                                $newPayroll->period_year = $data['target_year'];
                                
                                if (!$data['copy_bonus']) {
                                    $newPayroll->bonus = 0;
                                }
                                
                                // Reset review dates for bulk duplicate
                                $newPayroll->last_review_date = null;
                                $newPayroll->next_review_date = null;
                                
                                // Add bulk duplicate note
                                $originalPeriod = $record->period_name;
                                $newPayroll->notes = "[" . now()->format('d/m/Y H:i') . "] Diduplikasi secara massal dari payroll {$originalPeriod}";
                                
                                $newPayroll->save();
                                $successCount++;
                            }
                            
                            $months = [
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                            ];
                            $targetPeriod = $months[$data['target_month']] . ' ' . $data['target_year'];
                            
                            $message = "Berhasil menduplikasi {$successCount} payroll ke periode {$targetPeriod}";
                            
                            if ($skippedCount > 0) {
                                $message .= ". Dilewati: {$skippedCount} record";
                                if (count($skippedNames) <= 3) {
                                    $message .= " (" . implode(', ', $skippedNames) . ")";
                                }
                            }
                            
                            Notification::make()
                                ->title('Duplikasi Massal Selesai')
                                ->body($message)
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Duplikasi Payroll Massal')
                        ->modalDescription('Duplikasi akan membuat payroll baru untuk semua karyawan terpilih dengan periode yang ditentukan.')
                        ->modalSubmitActionLabel('Duplikasi Semua')
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
            ->recordTitleAttribute('user.name')
            ->searchOnBlur()
            ->deferLoading()
            ->emptyStateHeading('Tidak ada data payroll')
            ->emptyStateDescription('Belum ada data payroll untuk periode yang dipilih. Coba ubah filter bulan/tahun atau buat data payroll baru.')
            ->emptyStateIcon('heroicon-o-banknotes');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'user.status'])
            ->latest('created_at');
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
            'index' => Pages\ListPayrolls::route('/'),
            'create' => Pages\CreatePayroll::route('/create'),
            'edit' => Pages\EditPayroll::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Total payroll records';
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
