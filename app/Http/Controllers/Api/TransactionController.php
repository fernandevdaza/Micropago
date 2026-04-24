<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with(['user', 'vehicle', 'tariff'])->latest()->get();
        return TransactionResource::collection($transactions);
    }

    public function store(Request $request)
    {
        $transaction = Transaction::create($request->all());
        return new TransactionResource($transaction);
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['user', 'vehicle', 'tariff']);
        return new TransactionResource($transaction);
    }
    public function update(Request $request, Transaction $transaction)
    {
        $transaction->update($request->all());
        return new TransactionResource($transaction);
    }
    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        return response()->json(['message'=>'Eliminado Correctamente'], 204);
    }

}
