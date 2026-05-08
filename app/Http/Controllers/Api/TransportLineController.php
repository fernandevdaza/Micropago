<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransportLine\StoreTransportLineRequest;
use App\Http\Requests\TransportLine\UpdateTransportLineRequest;
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

    public function store(StoreTransportLineRequest $request)
    {
        $validated = $request->validated();

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

    public function update(UpdateTransportLineRequest $request, TransportLine $transportLine)
    {
        $validated = $request->validated();

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
