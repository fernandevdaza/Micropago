<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DriverController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\TariffController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\TransportLineController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Auth - rutas públicas
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    // CRUD Protegido
    Route::apiResource('users', UserController::class);
    Route::apiResource('tariffs', TariffController::class);
    Route::apiResource('transport-lines', TransportLineController::class);
    Route::apiResource('vehicles', VehicleController::class);
    Route::apiResource('transactions', TransactionController::class)->only(['index', 'show']);

    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    Route::post('/pay', [PaymentController::class, 'processPayment']);
    Route::post('/admin/recharge', [AdminController::class, 'recharge']);
    Route::get('/driver/transactions', [DriverController::class, 'myTransactions']);
    Route::get('/driver/vehicle', [DriverController::class, 'myVehicle']);

    Route::get('/passenger/transactions', function (Request $request) {
        $transactions = Transaction::where('user_id', $request->user()->id)
            ->with(['tariff', 'vehicle'])
            ->latest()
            ->get();

        return TransactionResource::collection($transactions);
    });
});
