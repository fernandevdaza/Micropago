<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VehicleResource;
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
        $validated = $request->validate([
            'transport_line_id' => 'required|exists:transport_lines,id',
            'driver_id' => 'required|exists:users,id',
            'internal_number' => 'required|integer',
            'license_plate' => 'required|string|unique:vehicles,license_plate',
        ]);

        $vehicle = Vehicle::create($validated);
        return new VehicleResource($vehicle);
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load(['transportLine', 'driver']);
        return new VehicleResource($vehicle);
    }
    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'transport_line_id' => 'sometimes|required|exists:transport_lines,id',
            'driver_id' => 'sometimes|required|exists:users,id',
            'internal_number' => 'sometimes|required|integer',
            'license_plate' => 'sometimes|required|string|unique:vehicles,license_plate,' . $vehicle->id,
        ]);

        $vehicle->update($validated);
        return new VehicleResource($vehicle);
    }
    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        return response()->json(['message'=>'Eliminado Correctamente'], 204);
    }

}
