<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user  = $request->user();
        $query = Transaction::with(['user', 'vehicle', 'tariff'])->latest();

        if ($user->isPassenger()) {
            // Pasajero: solo sus propias transacciones
            $query->where('user_id', $user->id);

        } elseif ($user->isDriver()) {
            // Conductor: transacciones de su vehículo
            $vehicle = Vehicle::where('driver_id', $user->id)->first();

            if (!$vehicle) {
                return response()->json(['message' => 'No tienes un vehículo asignado'], 404);
            }

            $query->where('vehicle_id', $vehicle->id)->take(50);

        } elseif ($user->isLineAdmin()) {
            // Admin de línea: transacciones de vehículos de su línea
            $vehicleIds = Vehicle::where('transport_line_id', $user->transport_line_id)->pluck('id');
            $query->whereIn('vehicle_id', $vehicleIds);

        } elseif ($user->isPlatformOperator()) {
            // Admin / super_admin: todas las transacciones
        } else {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        return TransactionResource::collection($query->get());
    }

    public function store(StoreTransactionRequest $request)
    {
        $validated = $request->validated();

        $transaction = Transaction::create($validated);
        return new TransactionResource($transaction);
    }

    public function show(Request $request, Transaction $transaction)
    {
        $user = $request->user();

        if ($user->isPassenger()) {
            if ($transaction->user_id !== $user->id) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
        } elseif ($user->isDriver()) {
            $vehicle = Vehicle::where('driver_id', $user->id)->first();
            if (!$vehicle || $transaction->vehicle_id !== $vehicle->id) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
        } elseif ($user->isLineAdmin()) {
            $vehicle = Vehicle::find($transaction->vehicle_id);
            if (!$vehicle || $vehicle->transport_line_id !== $user->transport_line_id) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
        } elseif (!$user->isPlatformOperator()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $transaction->load(['user', 'vehicle', 'tariff']);
        return new TransactionResource($transaction);
    }
}
