<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'transport_line_id' => null,
            'driver_id'         => null,
            'internal_number'   => fake()->unique()->numberBetween(1, 9999),
            'license_plate'     => strtoupper(fake()->unique()->bothify('???-###')),
        ];
    }
}
