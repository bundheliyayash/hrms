<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class SystemDummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks for clean execution
        Schema::disableForeignKeyConstraints();

        $this->command->info('Starting System wide dummy data seeding...');

        // 1. Foundation
        $this->call(MasterDataSeeder::class);
        $this->command->info('Master Data seeded.');

        // 2. Organization mapping
        $this->call(ClientSiteSeeder::class);
        $this->command->info('Clients and Sites seeded.');

        // 3. People
        $this->call(EmployeeSeeder::class);
        $this->command->info('Managers and Employees seeded.');

        // 4. Operations & History
        $this->call(HistoricalDataSeeder::class);
        $this->command->info('Historical Attendance and Holidays seeded.');

        $this->call(LeaveSeeder::class);
        $this->command->info('Leave Applications seeded.');

        // 5. Finance
        $this->call(FinancialSeeder::class);
        $this->command->info('Financial/Payroll data seeded.');

        Schema::enableForeignKeyConstraints();
        
        $this->command->info('SUCCESS: HRMS completely populated with realistic data.');
    }
}
