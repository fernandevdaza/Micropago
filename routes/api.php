<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Importamos todos los controladores que creaste en la carpeta Api
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TariffController;
use App\Http\Controllers\Api\TransportLineController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\TransactionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Ruta que Laravel crea por defecto al ejecutar install:api
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ------------------------------------------------------------------------
// Rutas de tu sistema de transporte
// ------------------------------------------------------------------------

// Usuarios
Route::apiResource('users', UserController::class);

// Tarifas
Route::apiResource('tariffs', TariffController::class);

// Líneas de Transporte
Route::apiResource('transport-lines', TransportLineController::class);

// Vehículos (Micros)
Route::apiResource('vehicles', VehicleController::class);

// Transacciones (Pagos y Recargas)
Route::apiResource('transactions', TransactionController::class);
