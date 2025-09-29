<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(function ($record) {
                    // Super admin can edit any user
                    if (UserResource::isSuperAdmin()) {
                        return true;
                    }
                    // Non-super admin users can only edit their own data
                    $user = Auth::user();
                    return $user && $user->id === $record->id;
                }),
        ];
    }

    /**
     * Apply the same query restrictions as in the main resource
     */
    protected function getEloquentQuery(): Builder
    {
        return UserResource::getEloquentQuery();
    }
}
