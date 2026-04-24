<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tariff;
use App\Models\Vehicle;
use App\Models\Transaction;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function processPayment(Request $request)
    {
        // 1. Validación básica de entrada
        $request->validate([
            'nfc_card_uid' => 'required|string|exists:users,nfc_card_uid',
            'vehicle_id' => 'required|uuid|exists:vehicles,id',
        ]);

        // 2. Buscar al pasajero y calcular su edad
        $passenger = User::where('nfc_card_uid', $request->nfc_card_uid)->first();
        $age = Carbon::parse($passenger->date_of_birth)->age;

        // 3. Lógica dinámica de Tarifas
        // Buscamos en la tabla 'tariffs' según la edad
        $tariffName = 'General';
        if ($age < 25) $tariffName = 'Estudiante'; // Ejemplo: menores de 25
        if ($age >= 60) $tariffName = 'Tercera Edad';

        $tariff = Tariff::where('name', $tariffName)->first();

        if (!$tariff) {
            return response()->json(['error' => 'Configuración de tarifa no encontrada'], 500);
        }

        // 4. Validar Saldo Suficiente
        if ($passenger->balance < $tariff->price) {
            return response()->json([
                'error' => 'Saldo insuficiente',
                'balance' => $passenger->balance,
                'required' => $tariff->price
            ], 402);
        }

        // 5. PROCESO ATÓMICO (DB Transaction)
        try {
            $transaction = DB::transaction(function () use ($passenger, $tariff, $request) {
                // A. Descontar saldo al pasajero
                $passenger->decrement('balance', $tariff->price);

                // B. Crear el registro del pago
                return Transaction::create([
                    'user_id' => $passenger->id,
                    'vehicle_id' => $request->vehicle_id,
                    'tariff_id' => $tariff->id,
                    'type' => 'payment',
                    'amount' => $tariff->price,
                    'status' => 'completed',
                    'created_at' => now(),
                ]);
            });

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
