<?php

namespace App\Filament\Widgets;

use App\Models\LeaveRequest;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RecentLeaveRequestsWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = 'full';
    
    public function getHeading(): ?string
    {
        return 'Recent Leave Requests';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                LeaveRequest::query()
                    ->with(['user.roles', 'leaveType', 'approver'])
                    ->whereHas('user.roles', function ($query) {
                        $query->where('name', 'Office');
                    })
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                    
                TextColumn::make('leaveType.name')
                    ->label('Leave Type')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'annual leave' => 'success',
                        'sick leave' => 'warning',
                        'emergency leave' => 'danger',
                        default => 'gray',
                    }),
                    
                TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date('M j, Y')
                    ->sortable(),
                    
                TextColumn::make('end_date')
                    ->label('End Date')
                    ->date('M j, Y')
                    ->sortable(),
                    
                TextColumn::make('total_days')
                    ->label('Days')
                    ->numeric()
                    ->alignCenter()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state <= 2 => 'success',
                        $state <= 5 => 'warning',
                        default => 'danger',
                    }),
                    
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray' => 'pending',
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'info' => 'cancelled',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-check-circle' => 'approved',
                        'heroicon-o-x-circle' => 'rejected',
                        'heroicon-o-no-symbol' => 'cancelled',
                    ]),
                    
                TextColumn::make('approver.name')
                    ->label('Approved By')
                    ->placeholder('Not yet approved')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('created_at')
                    ->label('Requested At')
                    ->since()
                    ->sortable()
                    ->toggleable(),
                    
                TextColumn::make('reason')
                    ->label('Reason')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                ViewAction::make()
                    ->iconButton()
                    ->tooltip('View Details'),
                    
                Action::make('approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->iconButton()
                    ->tooltip('Quick Approve')
                    ->visible(fn (LeaveRequest $record): bool => 
                        $record->status === 'pending'
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Approve Leave Request')
                    ->modalDescription('Are you sure you want to approve this leave request?')
                    ->action(function (LeaveRequest $record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => Auth::id(),
                            'approved_at' => now(),
                        ]);
                        
                        $this->getTable()->getAction('refresh');
                    })
                    ->successNotificationTitle('Leave request approved successfully'),
                    
                Action::make('reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->iconButton()
                    ->tooltip('Quick Reject')
                    ->visible(fn (LeaveRequest $record): bool => 
                        $record->status === 'pending'
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Reject Leave Request')
                    ->modalDescription('Are you sure you want to reject this leave request?')
                    ->action(function (LeaveRequest $record) {
                        $record->update([
                            'status' => 'rejected',
                            'approved_by' => Auth::id(),
                            'approved_at' => now(),
                        ]);
                        
                        $this->getTable()->getAction('refresh');
                    })
                    ->successNotificationTitle('Leave request rejected'),
            ])
            ->filters([
                // Add filters if needed
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateHeading('No recent leave requests')
            ->emptyStateDescription('When employees submit leave requests, they will appear here.')
            ->emptyStateIcon('heroicon-o-calendar-days')
            ->poll('30s'); // Refresh every 30 seconds
    }

    public function getTableRecordKey($record): string
    {
        return (string) $record->getKey();
    }

    protected function getTableQuery(): Builder
    {
        return LeaveRequest::query()
            ->with(['user.roles', 'leaveType', 'approver'])
            ->whereHas('user.roles', function ($query) {
                $query->where('name', 'Office');
            })
            ->latest()
            ->limit(10);
    }

    public function getTableRecordsPerPage(): ?int
    {
        return 10;
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }

    public function getDescription(): ?string
    {
        return 'Latest 10 leave requests submitted by employees with quick approval actions.';
    }
}
