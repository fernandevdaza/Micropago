<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $viewer = $request->user();
        $canViewOwnPassengerUid = $this->role->value === 'passenger'
            && ($viewer === null || $viewer->id === $this->id);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'ci' => $this->ci,
            'date_of_birth' => $this->date_of_birth,
            'balance' => (float) $this->balance,
            'nfc_card_uid' => $canViewOwnPassengerUid ? $this->nfc_card_uid : null,
        ];
    }
}
