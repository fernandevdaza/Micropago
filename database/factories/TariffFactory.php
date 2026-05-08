<?php

namespace Database\Factories;

use App\Models\Tariff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tariff>
 */
class TariffFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'  => fake()->unique()->word(),
            'price' => fake()->randomFloat(2, 1, 10),
        ];
    }

    public function general(): static
    {
        return $this->state(['name' => 'General', 'price' => 2.80]);
    }

    public function estudiante(): static
    {
        return $this->state(['name' => 'Estudiante', 'price' => 1.50]);
    }

    public function terceraEdad(): static
    {
        return $this->state(['name' => 'Tercera Edad', 'price' => 1.00]);
    }
}
