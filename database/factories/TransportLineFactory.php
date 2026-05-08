<?php

namespace Database\Factories;

use App\Models\TransportLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TransportLine>
 */
class TransportLineFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'        => 'Línea ' . fake()->unique()->numberBetween(1, 999),
            'description' => fake()->sentence(),
        ];
    }
}
