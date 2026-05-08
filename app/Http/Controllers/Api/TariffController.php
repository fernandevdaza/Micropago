<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TariffResource;
use App\Models\Tariff;
use Illuminate\Http\Request;

class TariffController extends Controller
{
    public function index()
    {
        return TariffResource::collection(Tariff::all());
    }

    public function store(Request $request)
    {
        if (!$request->user()->isSuperAdmin()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'name'  => 'required|string|unique:tariffs,name',
            'price' => 'required|numeric|min:0',
        ]);

        $tariff = Tariff::create($validated);
        return new TariffResource($tariff);
    }

    public function show(Tariff $tariff)
    {
        return new TariffResource($tariff);
    }

    public function update(Request $request, Tariff $tariff)
    {
        if (!$request->user()->isSuperAdmin()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'name'  => 'sometimes|required|string|unique:tariffs,name,' . $tariff->id,
            'price' => 'sometimes|required|numeric|min:0',
        ]);

        $tariff->update($validated);
        return new TariffResource($tariff);
    }

    public function destroy(Request $request, Tariff $tariff)
    {
        if (!$request->user()->isSuperAdmin()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $tariff->delete();
        return response()->json(['message' => 'Eliminado Correctamente'], 204);
    }
}
