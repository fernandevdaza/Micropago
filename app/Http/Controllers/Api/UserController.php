<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
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

    public function store(Request $request)
    {
        $this->authorize('create', User::class);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'ci' => 'required|string|unique:users,ci',
            'date_of_birth' => 'required|date|before:today',
            'role' => 'required|in:passenger,driver,admin,super_admin,line_admin',
            'transport_line_id' => 'nullable|exists:transport_lines,id',
        ]);

        $validated = $this->sanitizeUserPayload($request->user(), $validated);

        $user = User::create($validated);

        return new UserResource($user);
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        return new UserResource($user->load('transportLine'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'ci' => 'sometimes|required|string|unique:users,ci,' . $user->id,
            'date_of_birth' => 'sometimes|required|date|before:today',
            'role' => 'sometimes|required|in:passenger,driver,admin,super_admin,line_admin',
            'transport_line_id' => 'sometimes|nullable|exists:transport_lines,id',
        ]);

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
