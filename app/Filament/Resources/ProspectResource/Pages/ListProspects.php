<?php

namespace App\Filament\Resources\ProspectResource\Pages;

use App\Filament\Resources\ProspectResource;
use App\Filament\Widgets\ProspectStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProspects extends ListRecords
{
    protected static string $resource = ProspectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_prospects_without_orders')
                ->label('Prospect Tanpa Order')
                ->icon('heroicon-m-exclamation-triangle')
                ->color('warning')
                ->badge(function() {
                    try {
                        return \App\Models\Prospect::doesntHave('orders')->count();
                    } catch (\Exception $e) {
                        return 0;
                    }
                })
                ->url(fn() => static::getUrl(['tableFilters' => ['order_status' => ['value' => 'no_order']]])),
                
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ProspectStatsWidget::class,
        ];
    }
}
