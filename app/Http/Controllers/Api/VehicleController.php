<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vehicle\StoreVehicleRequest;
use App\Http\Requests\Vehicle\UpdateVehicleRequest;
use App\Http\Resources\VehicleResource;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Vehicle::class);

        $query = Vehicle::with(['transportLine', 'driver']);

        if ($request->user()->isLineAdmin()) {
            $query->where('transport_line_id', $request->user()->transport_line_id);
        }

        return VehicleResource::collection($query->get());
    }

    public function store(StoreVehicleRequest $request)
    {
        $validated = $request->validated();

        if ($request->user()->isLineAdmin()) {
            $validated['transport_line_id'] = $request->user()->transport_line_id;
        }

        $this->validateVehicleAssignment($request->user(), $validated);

        $vehicle = Vehicle::create($validated);
        return new VehicleResource($vehicle);
    }

    public function show(Request $request, Vehicle $vehicle)
    {
        $this->authorize('view', $vehicle);

        $vehicle->load(['transportLine', 'driver']);
        return new VehicleResource($vehicle);
    }

    public function update(UpdateVehicleRequest $request, Vehicle $vehicle)
    {
        $validated = $request->validated();

        if ($request->user()->isLineAdmin()) {
            $validated['transport_line_id'] = $request->user()->transport_line_id;
        }

        if ($validated !== []) {
            $candidate = array_merge($vehicle->only(['transport_line_id', 'driver_id']), $validated);
            $this->validateVehicleAssignment($request->user(), $candidate, $vehicle);
        }

        $vehicle->update($validated);
        return new VehicleResource($vehicle);
    }

    public function destroy(Request $request, Vehicle $vehicle)
    {
        $this->authorize('delete', $vehicle);

        $vehicle->delete();
        return response()->json(['message' => 'Eliminado Correctamente'], 204);
    }

    private function validateVehicleAssignment(User $actor, array $payload, ?Vehicle $vehicle = null): void
    {
        $driver = User::findOrFail($payload['driver_id']);

        if (!$driver->isDriver()) {
            abort(422, 'El conductor asignado debe tener rol driver.');
        }

        if ($driver->transport_line_id !== (int) $payload['transport_line_id']) {
            abort(422, 'El conductor debe pertenecer a la misma línea del vehículo.');
        }

        if ($actor->isLineAdmin() && !$actor->belongsToLine((int) $payload['transport_line_id'])) {
            abort(403, 'No autorizado para asignar vehículos fuera de tu línea.');
        }

        $existingVehicle = Vehicle::query()
            ->where('driver_id', $driver->id)
            ->when($vehicle !== null, fn ($query) => $query->where('id', '!=', $vehicle->id))
            ->exists();

        if ($existingVehicle) {
            abort(422, 'El conductor ya tiene un vehículo asignado.');
        }
    }
}
