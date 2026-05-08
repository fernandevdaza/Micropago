<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class ProcessPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isDriver();
    }

    public function rules(): array
    {
        return [
            'nfc_card_uid' => 'required|string|exists:users,nfc_card_uid',
            'vehicle_id'   => 'required|integer|exists:vehicles,id',
        ];
    }
}
