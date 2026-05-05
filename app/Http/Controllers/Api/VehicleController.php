<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::with(['transportLine', 'driver']);

        if ($request->user()->role->value === 'line_admin') {
            $query->where('transport_line_id', $request->user()->transport_line_id);
        }

        return VehicleResource::collection($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'transport_line_id' => 'required|exists:transport_lines,id',
            'driver_id'         => 'required|exists:users,id',
            'internal_number'   => 'required|integer',
            'license_plate'     => 'required|string|unique:vehicles,license_plate',
        ]);

        if ($request->user()->role->value === 'line_admin') {
            $validated['transport_line_id'] = $request->user()->transport_line_id;
        }

        $vehicle = Vehicle::create($validated);
        return new VehicleResource($vehicle);
    }

    public function show(Request $request, Vehicle $vehicle)
    {
        if ($request->user()->role->value === 'line_admin' &&
            $vehicle->transport_line_id !== $request->user()->transport_line_id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $vehicle->load(['transportLine', 'driver']);
        return new VehicleResource($vehicle);
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        if ($request->user()->role->value === 'line_admin' &&
            $vehicle->transport_line_id !== $request->user()->transport_line_id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'transport_line_id' => 'sometimes|required|exists:transport_lines,id',
            'driver_id'         => 'sometimes|required|exists:users,id',
            'internal_number'   => 'sometimes|required|integer',
            'license_plate'     => 'sometimes|required|string|unique:vehicles,license_plate,' . $vehicle->id,
        ]);

        if ($request->user()->role->value === 'line_admin') {
            unset($validated['transport_line_id']);
        }

        $vehicle->update($validated);
        return new VehicleResource($vehicle);
    }

    public function destroy(Request $request, Vehicle $vehicle)
    {
        if ($request->user()->role->value === 'line_admin' &&
            $vehicle->transport_line_id !== $request->user()->transport_line_id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $vehicle->delete();
        return response()->json(['message' => 'Eliminado Correctamente'], 204);
    }
}
