<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('vehicle'));
    }

    public function rules(): array
    {
        $vehicleId = $this->route('vehicle')?->id;

        return [
            'transport_line_id' => 'sometimes|required|exists:transport_lines,id',
            'driver_id'         => 'sometimes|required|exists:users,id',
            'internal_number'   => 'sometimes|required|integer',
            'license_plate'     => 'sometimes|required|string|unique:vehicles,license_plate,' . $vehicleId,
        ];
    }
}
