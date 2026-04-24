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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role, // Laravel serializará el Enum automáticamente
            'ci' => $this->ci,
            'date_of_birth' => $this->date_of_birth,
            'balance' => (float) $this->balance,
            'nfc_card_uid' => $this->nfc_card_uid,
            // 'password' no se incluye por seguridad
        ];
    }
}
