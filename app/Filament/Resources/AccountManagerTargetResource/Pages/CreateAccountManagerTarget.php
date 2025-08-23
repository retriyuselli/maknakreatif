<?php

namespace App\Filament\Resources\AccountManagerTargetResource\Pages;

use App\Filament\Resources\AccountManagerTargetResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateAccountManagerTarget extends CreateRecord
{
    protected static string $resource = AccountManagerTargetResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $currentUser = Auth::user();
        
        // Jika user_id tidak ada dalam data atau null, set berdasarkan user saat ini
        if (empty($data['user_id']) && $currentUser) {
            // Periksa role user melalui database langsung untuk menghindari masalah caching
            $isAccountManager = DB::table('model_has_roles')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('model_has_roles.model_id', $currentUser->id)
                ->where('model_has_roles.model_type', get_class($currentUser))
                ->where('roles.name', 'Account Manager')
                ->exists();
                
            if ($isAccountManager) {
                $data['user_id'] = $currentUser->id;
            }
        }
        
        // Pastikan user_id tidak null
        if (empty($data['user_id'])) {
            throw new \Exception('User ID is required but not provided. Please ensure you are logged in and have the proper role.');
        }
        
        return $data;
    }
}
