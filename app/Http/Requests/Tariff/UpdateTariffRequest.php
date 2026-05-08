<?php

namespace App\Http\Requests\Tariff;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTariffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isSuperAdmin();
    }

    public function rules(): array
    {
        $tariffId = $this->route('tariff')?->id;

        return [
            'name'  => 'sometimes|required|string|unique:tariffs,name,' . $tariffId,
            'price' => 'sometimes|required|numeric|min:0',
        ];
    }
}
