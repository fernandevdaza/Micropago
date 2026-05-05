<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransportLineResource;
use App\Models\TransportLine;
use Illuminate\Http\Request;

class TransportLineController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->role->value === 'line_admin') {
            $transportlines = TransportLine::where('id', $request->user()->transport_line_id)->get();
        } else {
            $transportlines = TransportLine::all();
        }

        return TransportLineResource::collection($transportlines);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $transportline = TransportLine::create($validated);
        return new TransportLineResource($transportline);
    }

    public function show(TransportLine $transportLine)
    {
        return new TransportLineResource($transportLine);
    }
    public function update(Request $request, TransportLine $transportLine)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string',
            'description' => 'nullable|string',
        ]);

        $transportLine->update($validated);
        return new TransportLineResource($transportLine);
    }
    public function destroy(TransportLine $transportLine)
    {
        $transportLine->delete();
        return response()->json(['message'=>'Eliminado Correctamente'], 204);
    }

}
