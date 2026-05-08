<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tariff\StoreTariffRequest;
use App\Http\Requests\Tariff\UpdateTariffRequest;
use App\Http\Resources\TariffResource;
use App\Models\Tariff;
use Illuminate\Http\Request;

class TariffController extends Controller
{
    public function index()
    {
        return TariffResource::collection(Tariff::all());
    }

    public function store(StoreTariffRequest $request)
    {
        $validated = $request->validated();

        $tariff = Tariff::create($validated);
        return new TariffResource($tariff);
    }

    public function show(Tariff $tariff)
    {
        return new TariffResource($tariff);
    }

    public function update(UpdateTariffRequest $request, Tariff $tariff)
    {
        $validated = $request->validated();

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
