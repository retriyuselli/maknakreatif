<?php

namespace App\Filament\Resources\ExpenseResource\Widgets;

use App\Models\Expense;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class ExpenseOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Get expenses for the current month
        $currentMonthExpenses = Expense::whereBetween('date_expense', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth(),
        ])->sum('amount');

        // Get expenses for the previous month
        $previousMonthExpenses = Expense::whereBetween('date_expense', [
            Carbon::now()->subMonth()->startOfMonth(),
            Carbon::now()->subMonth()->endOfMonth(),
        ])->sum('amount');

        // Calculate the percentage change
        $changePercentage = 0;
        if ($previousMonthExpenses > 0) {
            $changePercentage = (($currentMonthExpenses - $previousMonthExpenses) / $previousMonthExpenses) * 100;
        } elseif ($currentMonthExpenses > 0) {
            $changePercentage = 100; // Infinite growth from zero
        }

        // Determine the trend icon and color
        $trendIcon = null;
        $trendColor = 'gray';
        if ($changePercentage > 0) {
            $trendIcon = 'heroicon-m-arrow-trending-up';
            $trendColor = 'danger'; // More expenses is usually a negative trend for profit
        } elseif ($changePercentage < 0) {
            $trendIcon = 'heroicon-m-arrow-trending-down';
            $trendColor = 'success'; // Fewer expenses is usually a positive trend for profit
        }

        // Format the change description
        $changeDescription = number_format(abs($changePercentage), 1) . '% ' . ($changePercentage >= 0 ? 'increase' : 'decrease');

        // Get total number of expenses without an image
        $expensesWithoutImageCount = Expense::whereNull('image')->orWhere('image', '')->count();

        // Get total expenses for the current year
        $currentYearExpensesAmount = Expense::whereYear('date_expense', Carbon::now()->year)->sum('amount');

        // Get total number of expenses for the current year
        $currentYearExpensesCount = Expense::whereYear('date_expense', Carbon::now()->year)->count();

        return [
            Stat::make('Total Expenses (This Month)', '' . number_format($currentMonthExpenses, 0, ',', '.'))
                ->description($changeDescription . ' from last month')
                ->descriptionIcon($trendIcon, IconPosition::Before)
                ->color($trendColor),
            // Stat::make('Total Expenses (This Year)', '' . number_format($currentYearExpensesAmount, 0, ',', '.'))
            //     ->description('Total expenses recorded this year')
            //     ->descriptionIcon('heroicon-m-banknotes', IconPosition::Before)
            //     ->color('primary'),

            Stat::make('Number of Expenses (This Year)', $currentYearExpensesCount)
                ->description('Total expense records this year')
                ->descriptionIcon('heroicon-m-document-duplicate', IconPosition::Before)
                ->color('info'),

            Stat::make('Expenses Without Image', $expensesWithoutImageCount)
                ->description('Records needing payment proof')
                ->descriptionIcon('heroicon-m-exclamation-triangle', IconPosition::Before)
                ->color('warning'),
        ];
    }
}
