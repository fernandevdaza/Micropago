<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isPlatformOperator();
    }

    public function rules(): array
    {
        return [
            'user_id'    => 'required|exists:users,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'tariff_id'  => 'required|exists:tariffs,id',
            'type'       => 'required|string',
            'amount'     => 'required|numeric|min:0',
            'status'     => 'required|string',
        ];
    }
}
