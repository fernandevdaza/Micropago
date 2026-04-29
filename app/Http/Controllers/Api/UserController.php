<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return UserResource::collection(User::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'ci' => 'required|string|unique:users,ci',
            'date_of_birth' => 'required|date|before:today',
            'role' => 'required|in:passenger,driver,admin',
            'balance' => 'nullable|numeric|min:0',
            'nfc_card_uid' => 'nullable|string',
        ]);

        $user = User::create($validated);

        return new UserResource($user);
    }

    public function show(User $user)
    {
        return new UserResource($user);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'ci' => 'sometimes|required|string|unique:users,ci,' . $user->id,
            'date_of_birth' => 'sometimes|required|date|before:today',
            'role' => 'sometimes|required|in:passenger,driver,admin',
            'balance' => 'sometimes|numeric|min:0',
            'nfc_card_uid' => 'nullable|string',
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);
        return new UserResource($user);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'Eliminado correctamente'], 204);
    }

}
