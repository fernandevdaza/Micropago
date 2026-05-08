<?php

namespace App\Http\Requests\Vehicle;

use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Vehicle::class);
    }

    public function rules(): array
    {
        return [
            'transport_line_id' => 'required|exists:transport_lines,id',
            'driver_id'         => 'required|exists:users,id',
            'internal_number'   => 'required|integer',
            'license_plate'     => 'required|string|unique:vehicles,license_plate',
        ];
    }
}
