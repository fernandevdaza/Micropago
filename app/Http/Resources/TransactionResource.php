<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'user_id'    => $this->user_id,
            'vehicle_id' => $this->vehicle_id,
            'type'       => $this->type,
            'amount'     => (float) $this->amount,
            'status'     => $this->status,
            'created_at' => $this->created_at->toDateTimeString(),

            'user'    => new UserResource($this->whenLoaded('user')),
            'vehicle' => new VehicleResource($this->whenLoaded('vehicle')),
            'tariff'  => new TariffResource($this->whenLoaded('tariff')),
        ];
    }
}
