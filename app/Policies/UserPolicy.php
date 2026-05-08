<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isPlatformOperator() || $user->isLineAdmin();
    }

    public function view(User $user, User $model): bool
    {
        if ($user->isPlatformOperator()) return true;

        return $user->isLineAdmin() && $user->belongsToLine($model->transport_line_id);
    }

    public function create(User $user): bool
    {
        return $user->isPlatformOperator() || $user->isLineAdmin();
    }

    public function update(User $user, User $model): bool
    {
        if ($model->isSuperAdmin()) return false;

        if ($user->isSuperAdmin()) return true;

        if ($user->isAdmin()) return !$model->isAdmin();

        return $user->isLineAdmin()
            && $user->belongsToLine($model->transport_line_id)
            && in_array($model->role, [UserRole::Driver, UserRole::LineAdmin], true);
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) return false;

        if ($model->isSuperAdmin()) return false;

        if ($user->isSuperAdmin()) return true;

        if ($user->isAdmin()) return !$model->isAdmin();

        return $user->isLineAdmin()
            && $user->belongsToLine($model->transport_line_id)
            && in_array($model->role, [UserRole::Driver, UserRole::LineAdmin], true);
    }
}
