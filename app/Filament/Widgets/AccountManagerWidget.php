<?php

namespace App\Filament\Widgets;

use App\Models\User;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Collection;
use Filament\Tables\Actions\Action;
use Illuminate\Contracts\View\View;

class AccountManagerWidget extends BaseWidget
{
    use HasWidgetShield;
    
    // Widget configuration for appearance and behavior
    protected static ?string $heading = 'Account Manager Performance Dashboard';
    protected static ?int $sort = 6;  // Controls widget position in the dashboard
    protected static ?int $contentHeight = 400;  // Makes the widget a reasonable size
    protected int $pageSize = 10;  // Limits number of items per page for better performance

    public function table(Table $table): Table
    {   
        return $table
            // Start with the base query - only get active account managers
            ->query(
                User::query()
                    ->withCount(['orders as am_count']) // Menghitung jumlah order dan menamakannya am_count
                    ->where('status_id', '2')  // User status must be active
                    // Add a condition to ensure they have an active employee record
                    ->whereHas('employees', function (Builder $query) { // Ensure they have an employee record
                        $query->whereDate('date_of_join', '<=', now()); // And have joined on or before today
                    })
            )
            // Define what information we want to show
            ->columns([
                // Profile image - helps users quickly identify managers
                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label('Profile')
                    ->defaultImageUrl(function ($record) {
                        // Generate default avatar based on user's name initials
                        $name = $record->name ?? 'User';
                        $initials = collect(explode(' ', $name))
                            ->map(fn($word) => strtoupper(substr($word, 0, 1)))
                            ->take(2)
                            ->implode('');
                        
                        // Fallback if no initials
                        if (empty($initials)) {
                            $initials = strtoupper(substr($name, 0, 2));
                        }
                        
                        // Use UI Avatars service to generate default avatar
                        return "https://ui-avatars.com/api/?" . http_build_query([
                            'name' => $initials,
                            'size' => 128,
                            'background' => '3b82f6',
                            'color' => 'ffffff',
                            'font-size' => 0.6,
                            'rounded' => true,
                            'bold' => true,
                            'format' => 'svg'
                        ]);
                    })
                    ->circular()  // Round images look more polished
                    ->size(40),   // Good size for visibility without taking too much space

                // Manager's name - the most important identifier
                Tables\Columns\TextColumn::make('name')
                    ->label('Account Manager')
                    ->sortable()    // Enables sorting by name
                    ->weight(FontWeight::Bold),  // Makes names stand out

                // Email address - hidden by default to avoid clutter
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->toggleable(isToggledHiddenByDefault: true),

                // Client count - key performance metric
                Tables\Columns\TextColumn::make('amCount')
                    ->label('Total Clients')
                    ->numeric()
                    ->alignCenter()
                    ->weight(FontWeight::Bold)
                    ->color('primary'),  // Uses theme color for consistency
                
                Tables\Columns\TextColumn::make('closing')
                    ->numeric()
                    ->prefix('Rp. ')
                    ->weight(FontWeight::Bold),

                // Join date - useful for tracking tenure
                Tables\Columns\TextColumn::make('employees.date_of_join')
                    ->label('Join Date')
                    ->date('d M Y'),
            ])
            // Default sorting by join date
            ->defaultSort('am_count', 'desc') // Urutkan berdasarkan jumlah closing (am_count) terbanyak
            // Add filtering capabilities
            ->filters([
                // Date range filter for finding managers by join date
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')
                            ->label('From Date')
                            ->native(false),  // Use custom date picker for better UX
                        DatePicker::make('until')
                            ->label('Until Date')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => 
                                    $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => 
                                    $query->whereDate('created_at', '<=', $date),
                            );
                    }),

                // User Status filter (based on users.status_id)
                Tables\Filters\SelectFilter::make('status_id')
                    ->label('User Account Status')
                    ->options([
                        '2' => 'Active',
                        '1' => 'Inactive'
                    ]),

                // Employee Employment Status filter (based on employees.date_of_out)
                Tables\Filters\TernaryFilter::make('employment_status')
                    ->label('Employment Status')
                    ->placeholder('All Employment Statuses')
                    ->trueLabel('Currently Employed')
                    ->default(true) // Set default filter ke "Currently Employed"
                    ->falseLabel('Formerly Employed')
                    ->queries(
                        true: fn (Builder $query) => $query->whereHas('employees', function (Builder $employeeQuery) {
                            $employeeQuery->whereDate('date_of_join', '<=', now())
                                          ->where(function (Builder $subQuery) {
                                              $subQuery->whereNull('date_of_out')
                                                       ->orWhereDate('date_of_out', '>=', now());
                                          });
                        }),
                        false: fn (Builder $query) => $query->whereHas('employees', fn (Builder $employeeQuery) => $employeeQuery->whereDate('date_of_out', '<', now())),
                        blank: fn (Builder $query) => $query // No additional filter for 'blank'
                    ),
            ])
            // Define available actions
            ->actions([
                // View details action - shows modal with detailed information
                // Action::make('view')
                //     ->label('View')
                //     ->icon('heroicon-m-eye')
                //     ->color('success')
                //     ->modalHeading(fn (User $record): string => 
                //         "Account Manager Details: {$record->name}")
                //     ->modalContent(fn (User $record): View => view(
                //         'filament.resources.user.modal.view',
                //         ['user' => $record]
                //     ))
                //     ->modalWidth('md'),  // Comfortable reading width

                // Edit action - opens full edit page
                Action::make('edit')
                    ->label('Edit')
                    ->color('warning')
                    ->url(fn (User $record): string => 
                        url("/admin/users/{$record->id}/edit"))
                    ->openUrlInNewTab(),
            ])
            // Bulk actions for operating on multiple records
            ->bulkActions([
                Tables\Actions\BulkAction::make('export')
                    ->label('Export Selected')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function (Collection $records) {
                        // Export functionality can be added here
                    })
            ])
            // Configure pagination
            ->paginated([3, 6, 12])
            // Auto-refresh data every 30 seconds
            ->poll('30s');
    }
}
