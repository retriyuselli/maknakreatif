<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    use BaseDashboard\Concerns\HasFiltersForm;

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('periode')
                            ->options(fn () => \App\Models\Expo::query()
                                ->whereNotNull('periode')
                                ->distinct()
                                ->orderBy('periode')
                                ->pluck('periode', 'periode')
                                ->toArray()
                            )
                            ->searchable()
                            ->placeholder('Pilih Periode'),
                        DatePicker::make('startDate')
                            ->displayFormat('Y-m-d')
                            ->maxDate(fn (Get $get) => $get('endDate') ?: now()),
                        DatePicker::make('endDate')
                            ->minDate(fn (Get $get) => $get('startDate') ?: now())
                            ->displayFormat('Y-m-d')
                            ->maxDate(now()),
                    ])
                    ->columns(3),
            ]);
    }
}
