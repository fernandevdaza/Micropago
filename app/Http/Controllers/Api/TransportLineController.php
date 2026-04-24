<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransportLine;
use Illuminate\Http\Request;

class TransportLineController extends Controller
{
    public function index()
    {
        $transportlines = TransportLine::all();
        return TransportLineResource::collection($transportlines);
    }
    public function store(Request $request)
    {
        $transportline = TransportLine::create($request->all());
        return new TransportLineResource($transportline);
    }

    public function show(TransportLine $transportLine)
    {
        return new TransportLineResource($transportLine);
    }
    public function update(Request $request, TransportLine $transportLine)
    {
        $transportLine->update($request->all());
        return new TransportLineResource($transportLine);
    }
    public function destroy(TransportLine $transportLine)
    {
        $transportLine->delete();
        return response()->json(['message'=>'Eliminado Correctamente'], 204);
    }

}
