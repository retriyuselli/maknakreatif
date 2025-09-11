<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Employee;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Support\Collection;

class EventManager extends BaseWidget
{
    use HasWidgetShield;
    
    protected static ?int $sort = 5;
    protected static ?string $heading = 'Event Manager Performance Dashboard';
    protected static ?int $contentHeight = 400;
    protected int $pageSize = 5;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Employee::query()
                    ->where('position', 'Event Manager')
                    ->withCount('orders as events_count')
                    ->orderBy('events_count', 'desc')
            )
            ->columns([
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Profile')
                    ->defaultImageUrl(function ($record) {
                        // Generate default avatar based on employee's name initials
                        $name = $record->name ?? 'Employee';
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
                            'background' => '059669', // Green color for Event Managers
                            'color' => 'ffffff',
                            'font-size' => 0.6,
                            'rounded' => true,
                            'bold' => true,
                            'format' => 'svg'
                        ]);
                    })
                    ->circular()
                    ->size(40),

                Tables\Columns\TextColumn::make('name')
                    ->label('Event Manager')
                    ->sortable()
                    ->weight(FontWeight::Bold),
                
                Tables\Columns\TextColumn::make('events_count')
                    ->label('Total Events')
                    ->numeric()
                    ->alignCenter()
                    ->weight(FontWeight::Bold)
                    ->color('primary'),

                Tables\Columns\TextColumn::make('email')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('phone')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('date_of_join')
                    ->label('Join Date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('salary')
                    ->label('Salary')
                    ->money('idr')
                    ->toggleable(isToggledHiddenByDefault: true),

                // Tables\Columns\IconColumn::make('status')
                //     ->label('Status')
                //     ->boolean()
                //     ->trueIcon('heroicon-o-check-circle')
                //     ->falseIcon('heroicon-o-x-circle')
                //     ->trueColor('success')
                //     ->falseColor('danger')
                //     ->getStateUsing(fn ($record): bool => 
                //         !$record->date_of_out && $record->date_of_join),
            ])
            ->filters([
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('from')
                            ->label('From Date'),
                        DatePicker::make('until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => 
                                    $query->whereDate('date_of_join', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => 
                                    $query->whereDate('date_of_join', '<=', $date),
                            );
                    }),

                SelectFilter::make('performance')
                    ->options([
                        'high' => 'High Performers (>10 events)',
                        'medium' => 'Medium Performers (5-10 events)',
                        'low' => 'Low Performers (<5 events)',
                    ])
                    ->query(function (Builder $query, $state): Builder {
                        if (!$state) {
                            return $query;
                        }
                        
                        return match ($state) {
                            'high' => $query->having('events_count', '>', 10),
                            'medium' => $query->having('events_count', '>=', 5)
                                            ->having('events_count', '<=', 10),
                            'low' => $query->having('events_count', '<', 5),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                // Action::make('view')
                //     ->label('View')
                //     ->icon('heroicon-o-eye')
                //     ->modalContent(fn ($record) => view(
                //         'filament.resources.employee.modal.view',
                //         ['getRecord' => $record]
                //     ))
                //     ->modalWidth('md'),  // Comfortable reading width

                // Action::make('edit')
                //     ->label('Edit')
                //     ->icon('heroicon-o-pencil')
                //     ->url(fn (Employee $record): string => 
                //         "/admin/employees/{$record->id}/edit")
                //     ->openUrlInNewTab(),
            ])
            ->bulkActions([
                BulkAction::make('export')
                    ->label('Export Selected')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function (Collection $records) {
                        // Export functionality can be implemented here
                    })
            ])
            ->paginated([3, 6, 12])
            ->defaultSort('events_count', 'desc')
            ->poll('30s');
    }
}
