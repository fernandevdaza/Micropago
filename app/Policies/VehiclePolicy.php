<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Auth\Access\Response;

class VehiclePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Todos pueden ver vehículos (público o interno)
    }

    public function view(User $user, Vehicle $vehicle): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role->value, ['super_admin', 'admin', 'line_admin']);
    }

    public function update(User $user, Vehicle $vehicle): bool
    {
        if (in_array($user->role->value, ['super_admin', 'admin'])) return true;

        return $user->role->value === 'line_admin' && $user->transport_line_id === $vehicle->transport_line_id;
    }

    public function delete(User $user, Vehicle $vehicle): bool
    {
        if (in_array($user->role->value, ['super_admin', 'admin'])) return true;

        return $user->role->value === 'line_admin' && $user->transport_line_id === $vehicle->transport_line_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Vehicle $vehicle): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Vehicle $vehicle): bool
    {
        return false;
    }
}
