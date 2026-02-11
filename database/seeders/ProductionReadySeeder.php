<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProductionReadySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting Production Clean-up...');

        // 1. Disable Foreign Key Checks
        Schema::disableForeignKeyConstraints();

        // 2. Define Tables to Truncate
        $transactionalTables = [
            'attendances',
            'attendance_breaks',
            'attendance_corrections',
            'manual_attendance_requests',
            'daily_assignments',
            'leaves',
            'leave_allocations',
            'payrolls',
            'client_invoices',
            'invoice_line_items',
            'worker_replacements',
            'notifications',
            'jobs',
            'failed_jobs',
            'activity_log', // If utilizing spatie/laravel-activitylog
            'files', // If exists
            'documents',
        ];

        $operationalTables = [
            'employee_details',
            'clients',
            'client_sites',
            'contract_sites',
            'contracts',
            'shifts',
            'holidays',
            'holiday_site',
            'holiday_department',
        ];

        // 3. Truncate Tables
        foreach (array_merge($transactionalTables, $operationalTables) as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
                $this->command->info("Truncated table: {$table}");
            }
        }

        // 4. Handle Users Table (Keep only Admin)
        $adminRole = 'admin'; // Assuming 'role' column or role name
        
        // Strategy: Delete all non-admin users. 
        // Note: usage of 'role' column based on model definition seen earlier.
        DB::table('users')->where('role', '!=', 'admin')->delete();
        $this->command->info("Deleted non-admin users.");

        // 5. Ensure Admin User Exists
        $admin = User::where('role', 'admin')->first();

        if (!$admin) {
            $this->command->info("No Admin found. Creating default Admin.");
            $admin = User::forceCreate([
                'name' => 'System Admin',
                'email' => 'admin@hrms.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $this->command->info("Admin user preserved: {$admin->email}");
            // Optional: Reset password if requested, but better to keep existing if known.
            // Ensuring active status
            $admin->update(['status' => 'active']);
        }

        // 6. Reset Settings (Keep keys, reset values if needed, or just keep as is)
        // For now, we keep settings as is, assuming they are configured for production.
        // If we wanted to reset:
        // DB::table('settings')->update(['value' => DB::raw('default_value')]); 

        // 7. Clean Storage (Optional - Be careful)
        // $this->cleanStorage();

        // 8. Re-enable Foreign Key Checks
        Schema::enableForeignKeyConstraints();

        $this->command->info('System is now Production Ready! Login with Admin.');
    }

    protected function cleanStorage()
    {
        // Example: Delete all files in public/uploads except .gitignore
        // Storage::disk('public')->deleteDirectory('uploads');
        // Storage::disk('public')->makeDirectory('uploads');
    }
}
