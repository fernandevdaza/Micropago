<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;

class VehiclePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isPlatformOperator() || $user->isLineAdmin();
    }

    public function view(User $user, Vehicle $vehicle): bool
    {
        if ($user->isPlatformOperator()) return true;

        return $user->isLineAdmin() && $user->belongsToLine($vehicle->transport_line_id);
    }

    public function create(User $user): bool
    {
        return $user->isPlatformOperator() || $user->isLineAdmin();
    }

    public function update(User $user, Vehicle $vehicle): bool
    {
        if ($user->isPlatformOperator()) return true;

        return $user->isLineAdmin() && $user->belongsToLine($vehicle->transport_line_id);
    }

    public function delete(User $user, Vehicle $vehicle): bool
    {
        if ($user->isPlatformOperator()) return true;

        return $user->isLineAdmin() && $user->belongsToLine($vehicle->transport_line_id);
    }
}
