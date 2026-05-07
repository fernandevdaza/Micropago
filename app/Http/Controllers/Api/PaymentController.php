<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tariff;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function processPayment(Request $request)
    {
        $driver = $request->user();

        if (!$driver->isDriver()) {
            return response()->json(['error' => 'Solo los conductores pueden procesar pagos'], 403);
        }

        $validated = $request->validate([
            'nfc_card_uid' => 'required|string|exists:users,nfc_card_uid',
            'vehicle_id' => 'required|integer|exists:vehicles,id',
        ]);

        $vehicle = Vehicle::where('driver_id', $driver->id)->first();

        if (!$vehicle) {
            return response()->json(['error' => 'El conductor no tiene un vehículo asignado'], 403);
        }

        if ($vehicle->id !== (int) $validated['vehicle_id']) {
            return response()->json(['error' => 'Solo puedes cobrar con tu vehículo asignado'], 403);
        }

        $passenger = User::where('nfc_card_uid', $validated['nfc_card_uid'])->first();

        if (!$passenger) {
            return response()->json(['message' => 'NFC no registrado'], 404);
        }

        if (!$passenger->isPassenger()) {
            return response()->json(['error' => 'El UID NFC debe pertenecer a un pasajero'], 422);
        }

        $age = Carbon::parse($passenger->date_of_birth)->age;

        $tariffName = 'General';

        if ($age < 18) {
            $tariffName = 'Estudiante';
        } elseif ($age >= 60) {
            $tariffName = 'Tercera Edad';
        }

        $tariff = Tariff::where('name', $tariffName)->first();

        if (!$tariff) {
            return response()->json(['error' => 'Configuración de tarifa no encontrada'], 500);
        }

        if ($passenger->balance < $tariff->price) {
            return response()->json([
                'error' => 'Saldo insuficiente',
                'balance' => $passenger->balance,
                'required' => $tariff->price
            ], 402);
        }

        try {
            $transaction = DB::transaction(function () use ($passenger, $tariff, $vehicle) {
                $passenger->decrement('balance', $tariff->price);

                return Transaction::create([
                    'user_id' => $passenger->id,
                    'vehicle_id' => $vehicle->id,
                    'tariff_id' => $tariff->id,
                    'type' => 'payment',
                    'amount' => $tariff->price,
                    'status' => 'completed',
                ]);
            });

            $passenger->refresh();

            return response()->json([
                'message' => 'Pago exitoso',
                'passenger' => $passenger->name,
                'tariff_applied' => $tariff->name,
                'amount_paid' => $tariff->price,
                'new_balance' => $passenger->balance,
                'transaction_id' => $transaction->id
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al procesar el pago',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
