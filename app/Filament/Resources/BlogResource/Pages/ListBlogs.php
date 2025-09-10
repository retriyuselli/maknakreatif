<?php

namespace App\Filament\Resources\BlogResource\Pages;

use App\Filament\Resources\BlogResource;
use App\Models\Blog;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListBlogs extends ListRecords
{
    protected static string $resource = BlogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            BlogResource\Widgets\BlogStatsWidget::class,
        ];
    }
    
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Articles')
                ->badge(Blog::count()),
            'published' => Tab::make('Published')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_published', true))
                ->badge(Blog::where('is_published', true)->count()),
            'featured' => Tab::make('Featured')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_featured', true))
                ->badge(Blog::where('is_featured', true)->count()),
            'drafts' => Tab::make('Drafts')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_published', false))
                ->badge(Blog::where('is_published', false)->count()),
        ];
    }
}
