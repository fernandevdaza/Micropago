<?php

namespace App\Http\Requests\TransportLine;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransportLineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isPlatformOperator();
    }

    public function rules(): array
    {
        return [
            'name'        => 'sometimes|required|string',
            'description' => 'nullable|string',
        ];
    }
}
