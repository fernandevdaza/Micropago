<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'internal_number' => $this->internal_number,
            'license_plate' => $this->license_plate,

            'transport_line' => new TransportLineResource($this->whenLoaded('transportLine')),
            'driver' => new UserResource($this->whenLoaded('driver')),
        ];
    }
}
