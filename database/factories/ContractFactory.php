<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContractFactory extends Factory
{
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'contract_number' => 'CNT-' . fake()->bothify('####-????'),
            'contract_type' => 'permanent',
            'start_date' => now()->startOfMonth(),
            'billing_type' => 'per_day',
            'rate_per_day' => 1000,
            'status' => 'active',
            'payment_terms' => 'Net 30',
        ];
    }
}
