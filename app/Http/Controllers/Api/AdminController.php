<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RechargeRequest;
use App\Http\Resources\UserResource;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function recharge(RechargeRequest $request)
    {
        $validated = $request->validated();

        $user = User::findOrFail($validated['user_id']);

        if (!$user->isPassenger()) {
            return response()->json(['error' => 'Solo se puede recargar saldo a pasajeros'], 403);
        }

        $transaction = DB::transaction(function () use ($user, $validated) {
            $user->increment('balance', $validated['amount']);
            return Transaction::create([
                'user_id' => $user->id,
                'vehicle_id' => null,
                'tariff_id' => null,
                'type' => 'recharge',
                'amount' => $validated['amount'],
                'status' => 'completed',
            ]);
        });

        $user->refresh();

        return response()->json([
            'message' => 'Recarga exitosa',
            'user' => new UserResource($user),
            'transaction_id' => $transaction->id,
        ]);
    }
}
