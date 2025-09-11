<?php

namespace App\Filament\Resources\AccountManagerTargetResource\Widgets;

use App\Models\AccountManagerTarget;
use App\Models\User;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TopPerformersWidget extends BaseWidget
{
    protected static ?string $heading = 'Top Performers This Month';
    
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $currentYear = now()->year;
        $currentMonth = now()->month;

        return $table
            ->query(
                AccountManagerTarget::query()
                    ->with(['user'])
                    ->where('year', $currentYear)
                    ->where('month', $currentMonth)
                    ->whereHas('user.roles', function ($query) {
                        $query->where('name', 'Account Manager');
                    })
                    ->orderByDesc('achieved_amount')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('rank')
                    ->label('#')
                    ->getStateUsing(function ($rowLoop) {
                        return $rowLoop->iteration;
                    })
                    ->alignCenter()
                    ->badge()
                    ->color(fn (int $state): string => match ($state) {
                        1 => 'warning',  // Gold
                        2 => 'gray',     // Silver
                        3 => 'success',  // Bronze
                        default => 'primary',
                    }),

                TextColumn::make('user.name')
                    ->label('Account Manager')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('target_amount')
                    ->label('Target')
                    ->money('IDR')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('achieved_amount')
                    ->label('Achievement')
                    ->money('IDR')
                    ->sortable()
                    ->alignEnd()
                    ->color('success'),

                TextColumn::make('achievement_percentage')
                    ->label('Progress')
                    ->suffix('%')
                    ->alignCenter()
                    ->color(fn (float $state): string => match (true) {
                        $state >= 100 => 'success',
                        $state >= 80 => 'warning',
                        $state >= 60 => 'info',
                        default => 'danger',
                    })
                    ->weight('bold'),

                TextColumn::make('calculated_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'overachieved' => 'info',
                        'achieved' => 'success',
                        'partially achieved' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->alignCenter(),

                TextColumn::make('remaining_target')
                    ->label('Remaining')
                    ->money('IDR')
                    ->alignEnd()
                    ->color(fn (float $state): string => $state <= 0 ? 'success' : 'warning'),
            ])
            ->defaultSort('achieved_amount', 'desc')
            ->paginated(false);
    }
}
