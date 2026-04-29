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
        $tariffs = Tariff::all();
        return TariffResource::collection($tariffs);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:tariffs,name',
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
        $validated = $request->validate([
            'name' => 'sometimes|required|string|unique:tariffs,name,' . $tariff->id,
            'price' => 'sometimes|required|numeric|min:0',
        ]);

        $tariff->update($validated);
        return new TariffResource($tariff);
    }
    public function destroy(Tariff $tariff)
    {
        $tariff->delete();
        return response()->json(['message'=>'Eliminado Correctamente'], 204);
    }

}
