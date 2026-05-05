<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'vehicle', 'tariff'])->latest();

        if ($request->user()->role->value === 'line_admin') {
            $lineId = $request->user()->transport_line_id;
            $vehicleIds = Vehicle::where('transport_line_id', $lineId)->pluck('id');
            $query->whereIn('vehicle_id', $vehicleIds);
        }

        return TransactionResource::collection($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'    => 'required|exists:users,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'tariff_id'  => 'required|exists:tariffs,id',
            'type'       => 'required|string',
            'amount'     => 'required|numeric|min:0',
            'status'     => 'required|string',
        ]);

        $transaction = Transaction::create($validated);
        return new TransactionResource($transaction);
    }

    public function show(Request $request, Transaction $transaction)
    {
        if ($request->user()->role->value === 'line_admin') {
            $lineId = $request->user()->transport_line_id;
            $vehicle = Vehicle::find($transaction->vehicle_id);
            if (!$vehicle || $vehicle->transport_line_id !== $lineId) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
        }

        $transaction->load(['user', 'vehicle', 'tariff']);
        return new TransactionResource($transaction);
    }

    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'user_id'    => 'sometimes|required|exists:users,id',
            'vehicle_id' => 'sometimes|required|exists:vehicles,id',
            'tariff_id'  => 'sometimes|required|exists:tariffs,id',
            'type'       => 'sometimes|required|string',
            'amount'     => 'sometimes|required|numeric|min:0',
            'status'     => 'sometimes|required|string',
        ]);

        $transaction->update($validated);
        return new TransactionResource($transaction);
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        return response()->json(['message' => 'Eliminado Correctamente'], 204);
    }
}
