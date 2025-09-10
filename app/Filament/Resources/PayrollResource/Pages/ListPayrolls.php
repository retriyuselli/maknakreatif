<?php

namespace App\Filament\Resources\PayrollResource\Pages;

use App\Filament\Resources\PayrollResource;
use App\Models\Payroll;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPayrolls extends ListRecords
{
    protected static string $resource = PayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Payroll')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->icon('heroicon-o-user-group')
                ->badge(Payroll::count()),
            
            'current_month' => Tab::make('Bulan Ini')
                ->icon('heroicon-o-calendar')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('period_month', now()->month)->where('period_year', now()->year))
                ->badge(Payroll::where('period_month', now()->month)->where('period_year', now()->year)->count())
                ->badgeColor('primary'),
            
            'last_month' => Tab::make('Bulan Lalu')
                ->icon('heroicon-o-arrow-left')
                ->modifyQueryUsing(function (Builder $query) {
                    $lastMonth = now()->subMonth();
                    return $query->where('period_month', $lastMonth->month)->where('period_year', $lastMonth->year);
                })
                ->badge(function () {
                    $lastMonth = now()->subMonth();
                    return Payroll::where('period_month', $lastMonth->month)->where('period_year', $lastMonth->year)->count();
                })
                ->badgeColor('gray'),
            
            'high_salary' => Tab::make('Gaji Tinggi')
                ->icon('heroicon-o-arrow-trending-up')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('monthly_salary', '>=', 3000000))
                ->badge(Payroll::where('monthly_salary', '>=', 3000000)->count()),

            'review_due' => Tab::make('Review Mendekati')
                ->icon('heroicon-o-exclamation-triangle')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('next_review_date', '<=', now()->addDays(30)))
                ->badge(Payroll::whereDate('next_review_date', '<=', now()->addDays(30))->count())
                ->badgeColor('warning'),
            
            'overdue_review' => Tab::make('Review Terlambat')
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('next_review_date', '<', now()))
                ->badge(Payroll::whereDate('next_review_date', '<', now())->count())
                ->badgeColor('danger'),
        ];
    }
}
