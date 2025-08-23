<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DataPembayaran;
use Illuminate\Auth\Access\HandlesAuthorization;

class DataPembayaranPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_data::pembayaran');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DataPembayaran $dataPembayaran): bool
    {
        return $user->can('view_data::pembayaran');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_data::pembayaran');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DataPembayaran $dataPembayaran): bool
    {
        return $user->can('update_data::pembayaran');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DataPembayaran $dataPembayaran): bool
    {
        return $user->can('delete_data::pembayaran');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_data::pembayaran');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, DataPembayaran $dataPembayaran): bool
    {
        return $user->can('force_delete_data::pembayaran');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_data::pembayaran');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, DataPembayaran $dataPembayaran): bool
    {
        return $user->can('restore_data::pembayaran');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_data::pembayaran');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, DataPembayaran $dataPembayaran): bool
    {
        return $user->can('replicate_data::pembayaran');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_data::pembayaran');
    }
}
