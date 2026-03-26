<?php

namespace Database\Seeders;

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
            PayrollConfigSeeder::class,
            ShiftsSeeder::class,
        ]);

        // 2. Authorization (Roles & Permissions)
        $this->call(RbacSeeder::class);

        // 3. Administrative User (Dependent on Roles)
        $this->call(AdminUserSeeder::class);

        // 4. User Interface Structure
        $this->call(MenuSeeder::class);

        // 5. Full Demo Data (clients, employees, attendance, payroll, leaves, etc.)
        $this->call(DemoDataSeeder::class);
    }
}
