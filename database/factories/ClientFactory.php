<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'email' => fake()->email(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'service_start_date' => now()->subMonth(),
            'is_active' => true,
        ];
    }
}
