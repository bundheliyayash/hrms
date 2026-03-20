<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Client;
use App\Models\ClientSite;
use App\Models\EmployeeDetail;
use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\Leave;
use App\Models\Holiday;
use App\Models\DailyAssignment;
use Carbon\Carbon;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Cleaning up existing data...');
        Schema::disableForeignKeyConstraints();

        $tables = [
            'attendances', 'attendance_breaks', 'attendance_corrections', 'manual_attendance_requests',
            'daily_assignments', 'leaves', 'leave_allocations', 'payrolls', 'client_invoices',
            'invoice_line_items', 'worker_replacements', 'documents', 'employee_details',
            'clients', 'client_sites', 'contract_sites', 'contracts', 'holidays'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        // Keep only the primary admin
        User::where('role', '!=', 'admin')->forceDelete();
        $admin = User::firstOrCreate(
            ['email' => 'admin@hrms.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active'
            ]
        );

        $this->command->info('Creating Clients and Sites...');

        // 1. Create a Primary Client (with Portal Access)
        $client = Client::create([
            'name' => 'Reliable Services Ltd',
            'email' => 'contact@reliable.com',
            'phone' => '9876543210',
            'address' => 'Plot 45, Industrial Area, Sector 62, Noida',
            'is_active' => true,
            'service_start_date' => Carbon::now()->subYear(),
        ]);

        // Create Portal User for Client
        $clientUser = User::create([
            'name' => 'Reliable Manager',
            'email' => 'client@hrms.com',
            'password' => Hash::make('password'),
            'role' => 'client',
            'status' => 'active',
        ]);
        $client->update(['user_id' => $clientUser->id]);

        // Create Sites for this Client
        $siteA = ClientSite::create([
            'client_id' => $client->id,
            'site_name' => 'Noida HQ Site',
            'address' => 'Building A, Sector 62, Noida',
            'latitude' => 28.6273,
            'longitude' => 77.3725,
            'radius_meters' => 500,
            'is_active' => true,
        ]);

        $siteB = ClientSite::create([
            'client_id' => $client->id,
            'site_name' => 'Gurgaon Warehouse',
            'address' => 'Warehouse 7, Sector 14, Gurgaon',
            'latitude' => 28.4595,
            'longitude' => 77.0266,
            'radius_meters' => 1000,
            'is_active' => true,
        ]);

        $this->command->info('Creating Staff (Managers and Employees)...');

        // 2. Create a Manager
        $manager = User::create([
            'name' => 'Suresh Kumar',
            'email' => 'manager@hrms.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'status' => 'active',
        ]);

        // 3. Create Employees
        $employeesData = [
            ['name' => 'Amit Sharma', 'email' => 'amit@hrms.com', 'site' => $siteA],
            ['name' => 'Priya Patel', 'email' => 'priya@hrms.com', 'site' => $siteA],
            ['name' => 'Rahul Singh', 'email' => 'rahul@hrms.com', 'site' => $siteB],
            ['name' => 'Sneha Reddy', 'email' => 'sneha@hrms.com', 'site' => $siteB],
            ['name' => 'Test Employee', 'email' => 'employee@hrms.com', 'site' => $siteA],
        ];

        foreach ($employeesData as $idx => $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => 'employee',
                'status' => 'active',
            ]);

            EmployeeDetail::create([
                'user_id' => $user->id,
                'employee_id' => 'EMP' . (1000 + $idx),
                'designation' => 'Security Associate',
                'department' => 'Operations',
                'joining_date' => Carbon::now()->subMonths(6),
                'manager_id' => $manager->id,
                'site_id' => $data['site']->id,
                'basic_salary' => 15000 + ($idx * 1000),
            ]);

            // Generate some attendance for last 30 days
            for ($i = 30; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                if ($date->isSunday()) continue; // Skip Sundays

                Attendance::create([
                    'user_id' => $user->id,
                    'site_id' => $data['site']->id,
                    'client_id' => $client->id,
                    'date' => $date->format('Y-m-d'),
                    'clock_in' => '09:00',
                    'clock_out' => '18:00',
                    'status' => 'present',
                    'source' => $i < 5 ? 'client_portal' : 'admin',
                    'is_verified' => true,
                ]);
            }

            // Generate Payroll for last month
            Payroll::create([
                'user_id' => $user->id,
                'month' => Carbon::now()->subMonth()->format('F'),
                'year' => Carbon::now()->subMonth()->year,
                'total_days' => 30,
                'working_days' => 26,
                'present_days' => 25,
                'payable_days' => 25,
                'basic_salary' => 12000,
                'allowances' => 3000,
                'deductions' => 500,
                'net_salary' => 14500,
                'gross_salary' => 15000,
                'per_day_salary' => 500,
                'status' => 'paid',
            ]);
        }

        $this->command->info('Adding Holidays and Leaves...');

        // 4. Add Holidays
        Holiday::create([
            'name' => 'Republic Day',
            'start_date' => '2026-01-26',
            'end_date' => '2026-01-26',
            'type' => 'paid',
            'applicable_type' => 'all',
            'status' => 'active',
            'description' => 'National Holiday',
        ]);
        Holiday::create([
            'name' => 'Independence Day',
            'start_date' => '2026-08-15',
            'end_date' => '2026-08-15',
            'type' => 'paid',
            'applicable_type' => 'all',
            'status' => 'active',
            'description' => 'National Holiday',
        ]);

        // 5. Add a Leave Request for the dummy employee
        $testEmp = User::where('email', 'employee@hrms.com')->first();
        Leave::create([
            'user_id' => $testEmp->id,
            'leave_type' => 'sick',
            'start_date' => Carbon::today()->addDays(2)->format('Y-m-d'),
            'end_date' => Carbon::today()->addDays(3)->format('Y-m-d'),
            'reason' => 'Recovery from fever',
            'status' => 'pending',
        ]);

        Schema::enableForeignKeyConstraints();
        $this->command->info('Test data setup complete!');
        $this->command->info('Admin: admin@hrms.com / password');
        $this->command->info('Manager: manager@hrms.com / password');
        $this->command->info('Employee: employee@hrms.com / password');
        $this->command->info('Client: client@hrms.com / password');
    }
}
