<?php

namespace App\Http\Requests\TransportLine;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransportLineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isPlatformOperator();
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string',
            'description' => 'nullable|string',
        ];
    }
}
