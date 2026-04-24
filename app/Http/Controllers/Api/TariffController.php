<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
        $tariff = Tariff::create($request->all());
        return new TariffResource($tariff);
    }
    public function show(Tariff $tariff)
    {
        return new TariffResource($tariff);
    }
    public function update(Request $request, Tariff $tariff)
    {
        $tariff->update($request->all());
        return new TariffResource($tariff);
    }
    public function destroy(Tariff $tariff)
    {
        $tariff->delete();
        return response()->json(['message'=>'Eliminado Correctamente'], 204);
    }

}
