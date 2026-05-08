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
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
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

    Route::get('/driver/vehicle', [DriverController::class, 'myVehicle']);
});
