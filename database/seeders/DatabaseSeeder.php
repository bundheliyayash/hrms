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
        // 1. System Configuration & Static Data
        $this->call([
            SettingsSeeder::class,
            ShiftsSeeder::class,
        ]);

        // 2. Authorization (Roles & Permissions)
        $this->call(RbacSeeder::class);

        // 3. Administrative User (Dependent on Roles)
        $this->call(AdminUserSeeder::class);

        // 4. User Interface Structure
        $this->call(MenuSeeder::class);

        // 5. Operational Data (Dependent on Users/Employees)
        // Runs only if employees exist (safe to keep)
        $this->call(LeaveAllocationSeeder::class);
    }
}
