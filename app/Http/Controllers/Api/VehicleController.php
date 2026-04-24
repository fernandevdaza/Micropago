<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::with(['transportLine', 'driver'])->get();
        return VehicleResource::collection($vehicles);
    }

    public function store(Request $request)
    {
        $vehicle = Vehicle::create($request->all());
        return new VehicleResource($vehicle);
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load(['transportLine', 'driver']);
        return new VehicleResource($vehicle);
    }
    public function update(Request $request, Vehicle $vehicle)
    {
        $vehicle->update($request->all());
        return new VehicleResource($vehicle);
    }
    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        return response()->json(['message'=>'Eliminado Correctamente'], 204);
    }

}
