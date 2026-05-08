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

        // El NFC solo se expone al propio pasajero o a operadores de plataforma
        $isPassenger = $this->role->value === 'passenger';
        $isSelf      = $viewer !== null && $viewer->id === $this->id;
        $isOperator  = $viewer !== null && $viewer->isPlatformOperator();

        $showNfc = $isPassenger && ($isSelf || $isOperator);

        return [
            'id'                 => $this->id,
            'name'               => $this->name,
            'email'              => $this->email,
            'role'               => $this->role,
            'ci'                 => $this->ci,
            'date_of_birth'      => $this->date_of_birth,
            'balance'            => (float) $this->balance,
            'transport_line_id'  => $this->transport_line_id,
            'nfc_card_uid'       => $showNfc ? $this->nfc_card_uid : null,
        ];
    }
}
