<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    /**
     * @unauthenticated
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'ci' => 'required|string|unique:users,ci',
            'date_of_birth' => 'required|date|before:today',
        ]);

        $user = User::create([
            ...$validated,
            'role' => 'passenger',
            'nfc_card_uid' => strtoupper(bin2hex(random_bytes(4))),
        ]);

        $token = $user->createToken('micropago-token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    /**
     * Iniciar sesión y obtener un token Bearer.
     *
     * @response 200 { "user": {}, "token": "string" }
     * @response 401 { "message": "Credenciales incorrectas" }
     */

    /**
     * @unauthenticated
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('micropago-token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    /**
     * Cerrar sesión y revocar el token actual.
     *
     * @response 200 { "message": "Sesión cerrada correctamente" }
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }

    /**
     * Renovar el token de acceso sin necesidad de credenciales.
     *
     * @response 200 { "message": "Token renovado", "token": "string" }
     */
    public function refresh(Request $request)
    {
        $currentToken = $request->user()->currentAccessToken();
        $tokenName = $currentToken->name;
        $currentToken->delete();

        $newToken = $request->user()->createToken($tokenName)->plainTextToken;

        return response()->json([
            'message' => 'Token renovado',
            'token' => $newToken,
        ]);
    }

    /**
     * Obtener el perfil del usuario autenticado.
     *
     * @response 200 scenario="Pasajero autenticado" { "id": 1, "name": "...", "balance": 50.00 }
     */
    public function me(Request $request)
    {
        return new UserResource($request->user());
    }
}
