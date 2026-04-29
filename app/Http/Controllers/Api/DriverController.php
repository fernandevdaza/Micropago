<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\VehicleResource;
use App\Models\Transaction;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function myTransactions(Request $request)
    {
        $user = $request->user();

        if ($user->role->value !== 'driver') {
            return response()->json(['error' => 'Solo disponible para conductores'], 403);
        }

        $vehicle = Vehicle::where('driver_id', $user->id)->first();

        if (!$vehicle) {
            return response()->json(['message' => 'No tienes un vehículo asignado'], 404);
        }

        $transactions = Transaction::where('vehicle_id', $vehicle->id)
            ->with(['user', 'tariff'])
            ->latest()
            ->take(50)
            ->get();

        return TransactionResource::collection($transactions);
    }

    public function myVehicle(Request $request)
    {
        $user = $request->user();

        if ($user->role->value !== 'driver') {
            return response()->json(['error' => 'Solo disponible para conductores'], 403);
        }

        $vehicle = Vehicle::where('driver_id', $user->id)
            ->with('transportLine')
            ->first();

        if (!$vehicle) {
            return response()->json(['message' => 'No tienes un vehículo asignado'], 404);
        }

        return new VehicleResource($vehicle);
    }
}
