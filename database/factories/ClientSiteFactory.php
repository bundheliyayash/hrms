<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientSiteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'site_name' => fake()->streetName(),
            'address' => fake()->address(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'radius_meters' => 100,
            'is_active' => true,
        ];
    }
}
