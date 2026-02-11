<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     * 
     * CRITICAL: Order matters!
     * 1. System configuration (Settings, Shifts) must run first
     * 2. User interface (Menus) 
     * 3. Base data (Users, Clients, Sites via CleaningServiceSeeder)
     * 4. User-dependent data (LeaveAllocations) must run AFTER users exist
     */
    public function run(): void
    {
        $this->call([
            // Phase 1: System Configuration (Must run first)
            SettingsSeeder::class,
            ShiftsSeeder::class,
            
            // Phase 2: User Interface
            MenuSeeder::class,
                        // Phase 3: Base Data (Creates users, clients, sites, employees)
                CleaningServiceSeeder::class,

                // Phase 4: Business Modules Data
                ContractSeeder::class,
                DailyAssignmentSeeder::class,
                ClientInvoiceSeeder::class,
                WorkerReplacementSeeder::class,

                // Phase 5: User Allocations (Must run AFTER users exist)
                LeaveAllocationSeeder::class,
            ]);
    }
}
