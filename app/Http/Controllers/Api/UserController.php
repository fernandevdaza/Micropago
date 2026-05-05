<?php

namespace App\Http\Controllers\Api;

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

        if ($request->user()->role->value === 'line_admin') {
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
            'balance' => 'nullable|numeric|min:0',
            'transport_line_id' => 'required_if:role,driver,line_admin|exists:transport_lines,id',
        ]);

        if ($validated['role'] === 'passenger') {
            $validated['nfc_card_uid'] = strtoupper(bin2hex(random_bytes(4)));
        }

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
            'balance' => 'sometimes|numeric|min:0',
            'transport_line_id' => 'sometimes|required_if:role,driver,line_admin|exists:transport_lines,id',
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);
        return new UserResource($user);
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $user->delete();
        return response()->json(['message' => 'Eliminado correctamente'], 204);
    }

}
