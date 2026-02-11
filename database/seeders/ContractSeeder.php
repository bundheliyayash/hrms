<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Contract;
use App\Models\Client;
use App\Models\User;
use Carbon\Carbon;

class ContractSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $client1 = Client::where('email', 'rahul@techpark.com')->first();
        $client2 = Client::where('email', 'amit@skyline.com')->first();

        if (!$client1 || !$client2) {
            $this->command->error('Clients not found. Please run CleaningServiceSeeder first.');
            return;
        }

        // 1. Permanent Contract for Tech Park
        $c1 = Contract::firstOrCreate(
            ['client_id' => $client1->id, 'contract_type' => 'permanent'],
            [
                'contract_number' => Contract::generateContractNumber(),
                'start_date' => Carbon::now()->subMonths(3),
                'billing_type' => 'per_day',
                'rate_per_day' => 850.00,
                'minimum_workers_required' => 5,
                'payment_terms' => 'monthly',
                'status' => 'active',
                'auto_renew' => true,
                'created_by' => $admin->id
            ]
        );

        $site1 = $client1->sites()->first();
        if ($site1) {
            $c1->sites()->syncWithoutDetaching([$site1->id => ['workers_required' => 5]]);
        }

        // 2. Temporary Contract for Skyline
        $c2 = Contract::firstOrCreate(
            ['client_id' => $client2->id, 'contract_type' => 'temporary'],
            [
                'contract_number' => Contract::generateContractNumber(),
                'start_date' => Carbon::now()->subDays(15),
                'end_date' => Carbon::now()->addDays(45),
                'billing_type' => 'per_month',
                'rate_per_day' => 25000.00, // Fixed monthly
                'minimum_workers_required' => 2,
                'payment_terms' => 'weekly',
                'status' => 'active',
                'auto_renew' => false,
                'created_by' => $admin->id
            ]
        );

        $site2 = $client2->sites()->first();
        if ($site2) {
            $c2->sites()->syncWithoutDetaching([$site2->id => ['workers_required' => 2]]);
        }

        // 3. One-Day Job for Tech Park (Expiring/Ending Today)
        $c3 = Contract::firstOrCreate(
            ['contract_number' => 'CNT-2026-9999'],
            [
                'client_id' => $client1->id,
                'contract_type' => 'one_day',
                'start_date' => Carbon::today(),
                'end_date' => Carbon::today(),
                'billing_type' => 'per_service',
                'rate_per_day' => 1200.00,
                'minimum_workers_required' => 3,
                'payment_terms' => 'on_completion',
                'status' => 'active',
                'auto_renew' => false,
                'created_by' => $admin->id
            ]
        );

        if ($site1) {
            $c3->sites()->syncWithoutDetaching([$site1->id => ['workers_required' => 3]]);
        }
    }
}
