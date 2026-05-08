<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::query();

        if ($request->user()->isLineAdmin()) {
            $query->where('transport_line_id', $request->user()->transport_line_id);
        }

        return UserResource::collection($query->get());
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();
        $validated = $this->sanitizeUserPayload($request->user(), $validated);

        $user = User::create($validated);

        return new UserResource($user);
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        return new UserResource($user->load('transportLine'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $validated = $this->sanitizeUserPayload($request->user(), $validated, $user);

        $user->update($validated);
        return new UserResource($user);
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $user->delete();
        return response()->json(['message' => 'Eliminado correctamente'], 204);
    }

    private function sanitizeUserPayload(User $actor, array $validated, ?User $target = null): array
    {
        unset($validated['balance'], $validated['nfc_card_uid']);

        $roleValue = $validated['role'] ?? $target?->role->value;
        $role = $roleValue ? UserRole::from($roleValue) : null;

        if ($role === UserRole::Passenger) {
            abort(403, 'Los pasajeros solo pueden registrarse desde la API pública.');
        }

        if ($role === UserRole::SuperAdmin) {
            abort(403, 'El rol super_admin no puede crearse ni modificarse desde este endpoint.');
        }

        if ($actor->isAdmin()) {
            if (in_array($role, [UserRole::Admin, UserRole::SuperAdmin], true)) {
                abort(403, 'El admin no puede crear ni modificar roles admin o super_admin.');
            }

            if (in_array($role, [UserRole::Driver, UserRole::LineAdmin], true)) {
                if (empty($validated['transport_line_id']) && $target?->transport_line_id === null) {
                    abort(422, 'Los conductores y line_admin deben pertenecer a una línea.');
                }
            } else {
                $validated['transport_line_id'] = null;
            }

            return $validated;
        }

        if ($actor->isSuperAdmin()) {
            if ($role === UserRole::Admin) {
                $validated['transport_line_id'] = null;
            } elseif (in_array($role, [UserRole::Driver, UserRole::LineAdmin], true)) {
                if (empty($validated['transport_line_id']) && $target?->transport_line_id === null) {
                    abort(422, 'Los conductores y line_admin deben pertenecer a una línea.');
                }
            } else {
                $validated['transport_line_id'] = null;
            }

            return $validated;
        }

        if ($actor->isLineAdmin()) {
            if (!in_array($role, [UserRole::Driver, UserRole::LineAdmin], true)) {
                abort(403, 'El line_admin solo puede gestionar conductores y admins de su línea.');
            }

            $validated['transport_line_id'] = $actor->transport_line_id;

            if ($target !== null && !$actor->belongsToLine($target->transport_line_id)) {
                abort(403, 'No autorizado para gestionar usuarios de otra línea.');
            }

            return $validated;
        }

        abort(403, 'No autorizado para gestionar usuarios.');
    }
}
