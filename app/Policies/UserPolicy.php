<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role->value, ['super_admin', 'admin', 'line_admin']);
    }

    public function view(User $user, User $model): bool
    {
        if (in_array($user->role->value, ['super_admin', 'admin'])) return true;

        return $user->role->value === 'line_admin' && $user->transport_line_id === $model->transport_line_id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role->value, ['super_admin', 'admin', 'line_admin']);
    }

    public function update(User $user, User $model): bool
    {
        if (in_array($user->role->value, ['super_admin', 'admin'])) return true;

        return $user->role->value === 'line_admin' && $user->transport_line_id === $model->transport_line_id;
    }

    public function delete(User $user, User $model): bool
    {
        if (in_array($user->role->value, ['super_admin', 'admin'])) return true;

        return $user->role->value === 'line_admin' && $user->transport_line_id === $model->transport_line_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
