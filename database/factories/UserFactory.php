<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'             => fake()->name(),
            'email'            => fake()->unique()->safeEmail(),
            'password'         => 'password',
            'role'             => UserRole::Passenger,
            'ci'               => fake()->unique()->numerify('########'),
            'date_of_birth'    => fake()->dateTimeBetween('-50 years', '-20 years')->format('Y-m-d'),
            'balance'          => 0,
            'nfc_card_uid'     => null,
            'transport_line_id'=> null,
        ];
    }

    public function passenger(): static
    {
        return $this->state([
            'role'         => UserRole::Passenger,
            'nfc_card_uid' => strtoupper(fake()->unique()->lexify('????????')),
        ]);
    }

    public function driver(?int $lineId = null): static
    {
        return $this->state([
            'role'              => UserRole::Driver,
            'transport_line_id' => $lineId,
        ]);
    }

    public function lineAdmin(?int $lineId = null): static
    {
        return $this->state([
            'role'              => UserRole::LineAdmin,
            'transport_line_id' => $lineId,
        ]);
    }

    public function admin(): static
    {
        return $this->state(['role' => UserRole::Admin]);
    }

    public function superAdmin(): static
    {
        return $this->state(['role' => UserRole::SuperAdmin]);
    }

    public function withBalance(float $amount): static
    {
        return $this->state(['balance' => $amount]);
    }

    public function aged(int $years): static
    {
        return $this->state([
            'date_of_birth' => now()->subYears($years)->format('Y-m-d'),
        ]);
    }
}
