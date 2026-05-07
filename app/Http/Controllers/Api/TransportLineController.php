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
        $user = $request->user();

        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return TransportLineResource::collection(TransportLine::all());
        }

        if ($user->isLineAdmin()) {
            return TransportLineResource::collection(
                TransportLine::where('id', $user->transport_line_id)->get()
            );
        }

        return response()->json(['error' => 'No autorizado'], 403);
    }

    public function store(Request $request)
    {
        if (!$request->user()->isSuperAdmin()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $transportline = TransportLine::create($validated);
        return new TransportLineResource($transportline);
    }

    public function show(TransportLine $transportLine)
    {
        $user = request()->user();

        if ($user->isLineAdmin() && !$user->belongsToLine($transportLine->id)) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        if (!$user->isPlatformOperator() && !$user->isLineAdmin()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        return new TransportLineResource($transportLine);
    }

    public function update(Request $request, TransportLine $transportLine)
    {
        if (!$request->user()->isSuperAdmin()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string',
            'description' => 'nullable|string',
        ]);

        $transportLine->update($validated);
        return new TransportLineResource($transportLine);
    }
    public function destroy(TransportLine $transportLine)
    {
        if (!request()->user()->isSuperAdmin()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $transportLine->delete();
        return response()->json(['message'=>'Eliminado Correctamente'], 204);
    }

}
