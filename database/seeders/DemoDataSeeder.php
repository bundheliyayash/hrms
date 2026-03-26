<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Role;
use App\Models\Client;
use App\Models\ClientSite;
use App\Models\Contract;
use App\Models\EmployeeDetail;
use App\Models\Shift;
use App\Models\Leave;
use App\Models\LeaveAllocation;
use App\Models\Payroll;
use App\Models\Holiday;
use App\Models\WorkerReplacement;
use App\Models\Notification;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Starting Demo Data Seeder...');

        // ── Wipe transactional tables safely ──────────────────────────────
        Schema::disableForeignKeyConstraints();
        foreach ([
            'worker_replacements', 'attendance_breaks', 'attendance_corrections',
            'attendances', 'daily_assignments', 'payrolls', 'leaves', 'leave_allocations',
            'client_invoices', 'invoice_line_items', 'notifications', 'documents',
            'employee_details', 'contract_sites', 'contracts',
            'client_sites', 'clients',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
        User::where('role', '!=', 'admin')->forceDelete();
        Schema::enableForeignKeyConstraints();
        $this->command->info('  ✓ Tables cleared');

        // ── Resolve role IDs ───────────────────────────────────────────────
        $adminRole    = Role::where('name', 'admin')->first();
        $managerRole  = Role::where('name', 'manager')->first();
        $employeeRole = Role::where('name', 'employee')->first();
        $clientRole   = Role::where('name', 'client')->first();

        // ── Shifts (already seeded, just fetch) ───────────────────────────
        $generalShift = Shift::where('name', 'General Shift')->first();
        $morningShift = Shift::where('name', 'Morning Shift')->first();
        $eveningShift = Shift::where('name', 'Evening Shift')->first();

        // ── Admin ─────────────────────────────────────────────────────────
        $admin = User::updateOrCreate(
            ['email' => 'admin@hrms.com'],
            [
                'name'     => 'System Admin',
                'password' => Hash::make('password'),
                'role'     => 'admin',
                'role_id'  => $adminRole?->id,
                'status'   => 'active',
            ]
        );
        $this->command->info('  ✓ Admin user ready');

        // ── Managers ──────────────────────────────────────────────────────
        $manager1 = User::create([
            'name'    => 'Rajesh Kumar',
            'email'   => 'manager@hrms.com',
            'password' => Hash::make('password'),
            'role'    => 'manager',
            'role_id' => $managerRole?->id,
            'status'  => 'active',
        ]);
        $manager2 = User::create([
            'name'    => 'Meena Sharma',
            'email'   => 'manager2@hrms.com',
            'password' => Hash::make('password'),
            'role'    => 'manager',
            'role_id' => $managerRole?->id,
            'status'  => 'active',
        ]);
        $this->command->info('  ✓ Managers created');

        // ══════════════════════════════════════════════════════════════════
        // CLIENTS + SITES + CONTRACTS
        // ══════════════════════════════════════════════════════════════════
        $clientsData = [
            [
                'name'           => 'Global Logistics Ltd',
                'contact_person' => 'Arjun Mehta',
                'email'          => 'arjun@globallogistics.com',
                'phone'          => '9876543210',
                'address'        => 'Plot 12, Sector 18, Noida, UP',
                'portal_email'   => 'client@hrms.com',
                'portal_name'    => 'Global Logistics',
                'sites' => [
                    ['name' => 'Global Logistics - Site 1', 'address' => 'Building A, Sector 18, Noida', 'lat' => 28.5706, 'lng' => 77.3261, 'workers' => 4],
                    ['name' => 'Global Logistics - Site 2', 'address' => 'Warehouse Complex, Sector 62, Noida', 'lat' => 28.6273, 'lng' => 77.3725, 'workers' => 6],
                ],
                'rate_per_day'    => 650.00,
                'contract_type'   => 'permanent',
            ],
            [
                'name'           => 'Star Industries Pvt Ltd',
                'contact_person' => 'Sunita Verma',
                'email'          => 'sunita@starindustries.com',
                'phone'          => '9123456780',
                'address'        => 'Industrial Zone, Gurgaon, Haryana',
                'portal_email'   => 'client2@hrms.com',
                'portal_name'    => 'Star Industries',
                'sites' => [
                    ['name' => 'Star Industries - Factory',   'address' => 'Phase 1, Industrial Zone, Gurgaon', 'lat' => 28.4595, 'lng' => 77.0266, 'workers' => 5],
                    ['name' => 'Star Industries - Admin Hub', 'address' => 'DLF Cyber City, Gurgaon',           'lat' => 28.4944, 'lng' => 77.0889, 'workers' => 3],
                ],
                'rate_per_day'    => 700.00,
                'contract_type'   => 'permanent',
            ],
            [
                'name'           => 'Metro Corp Healthcare',
                'contact_person' => 'Dr. Prakash Iyer',
                'email'          => 'prakash@metrocorp.in',
                'phone'          => '9988776655',
                'address'        => 'Saket District Centre, New Delhi',
                'portal_email'   => 'client3@hrms.com',
                'portal_name'    => 'Metro Corp',
                'sites' => [
                    ['name' => 'Metro Corp - Main Hospital', 'address' => 'Block B, Saket, New Delhi',  'lat' => 28.5274, 'lng' => 77.2167, 'workers' => 8],
                    ['name' => 'Metro Corp - Clinic Annex',  'address' => 'Lajpat Nagar, New Delhi',    'lat' => 28.5654, 'lng' => 77.2358, 'workers' => 4],
                ],
                'rate_per_day'    => 750.00,
                'contract_type'   => 'permanent',
            ],
        ];

        $allSites    = [];
        $allClients  = [];
        $allContracts = [];

        foreach ($clientsData as $cd) {
            // Create portal user
            $portalUser = User::create([
                'name'     => $cd['portal_name'],
                'email'    => $cd['portal_email'],
                'password' => Hash::make('password'),
                'role'     => 'client',
                'role_id'  => $clientRole?->id,
                'status'   => 'active',
            ]);

            // Create client
            $client = Client::create([
                'name'               => $cd['name'],
                'contact_person'     => $cd['contact_person'],
                'email'              => $cd['email'],
                'phone'              => $cd['phone'],
                'address'            => $cd['address'],
                'is_active'          => true,
                'user_id'            => $portalUser->id,
                'service_start_date' => Carbon::now()->subYear(),
            ]);
            $allClients[] = $client;

            // Create sites
            $clientSites = [];
            foreach ($cd['sites'] as $sd) {
                $site = ClientSite::create([
                    'client_id'     => $client->id,
                    'site_name'     => $sd['name'],
                    'address'       => $sd['address'],
                    'latitude'      => $sd['lat'],
                    'longitude'     => $sd['lng'],
                    'radius_meters' => 300,
                    'is_active'     => true,
                ]);
                $clientSites[] = ['site' => $site, 'workers' => $sd['workers']];
                $allSites[]    = $site;
            }

            // Create contract
            $contractNum = 'CNT-' . strtoupper(substr($cd['name'], 0, 3)) . '-' . date('Y') . '-001';
            $contract = Contract::create([
                'client_id'                => $client->id,
                'contract_number'          => $contractNum,
                'contract_type'            => $cd['contract_type'],
                'start_date'               => Carbon::now()->subYear(),
                'end_date'                 => Carbon::now()->addYear(),
                'renewal_date'             => Carbon::now()->addYear()->subMonth(),
                'billing_type'             => 'per_day',
                'rate_per_day'             => $cd['rate_per_day'],
                'minimum_workers_required' => collect($clientSites)->sum('workers'),
                'actual_workers_assigned'  => collect($clientSites)->sum('workers'),
                'payment_terms'            => 'monthly',
                'status'                   => 'active',
                'auto_renew'               => true,
                'ot_enabled'               => true,
                'ot_multiplier'            => 1.5,
                'calculate_ot_after_minutes' => 480,
                'created_by'               => $admin->id,
            ]);
            $allContracts[] = $contract;

            // Link contract ↔ sites (pivot)
            foreach ($clientSites as $cs) {
                DB::table('contract_sites')->insert([
                    'contract_id'      => $contract->id,
                    'site_id'          => $cs['site']->id,
                    'workers_required' => $cs['workers'],
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        }
        $this->command->info('  ✓ Clients, Sites & Contracts created (' . count($allClients) . ' clients, ' . count($allSites) . ' sites)');

        // ══════════════════════════════════════════════════════════════════
        // EMPLOYEES — spread across sites
        // ══════════════════════════════════════════════════════════════════
        $employeesConfig = [
            // Global Logistics Site 1 (4 workers)
            ['name' => 'Amit Sharma',    'email' => 'amit@hrms.com',    'phone' => '9811001001', 'dept' => 'Housekeeping', 'desig' => 'Cleaner',          'salary' => 14000, 'site_idx' => 0, 'shift' => $morningShift, 'mgr' => $manager1],
            ['name' => 'Priya Patel',    'email' => 'priya@hrms.com',   'phone' => '9811001002', 'dept' => 'Housekeeping', 'desig' => 'Supervisor',       'salary' => 18000, 'site_idx' => 0, 'shift' => $morningShift, 'mgr' => $manager1],
            ['name' => 'Ravi Gupta',     'email' => 'ravi@hrms.com',    'phone' => '9811001003', 'dept' => 'Housekeeping', 'desig' => 'Cleaner',          'salary' => 13500, 'site_idx' => 0, 'shift' => $eveningShift, 'mgr' => $manager1],
            ['name' => 'Kavya Reddy',    'email' => 'kavya@hrms.com',   'phone' => '9811001004', 'dept' => 'Housekeeping', 'desig' => 'Cleaner',          'salary' => 13000, 'site_idx' => 0, 'shift' => $eveningShift, 'mgr' => $manager1],
            // Global Logistics Site 2 (6 workers)
            ['name' => 'Suresh Nair',    'email' => 'suresh@hrms.com',  'phone' => '9811001005', 'dept' => 'Sanitation',   'desig' => 'Sanitation Worker', 'salary' => 13000, 'site_idx' => 1, 'shift' => $morningShift, 'mgr' => $manager1],
            ['name' => 'Deepa Thomas',   'email' => 'deepa@hrms.com',   'phone' => '9811001006', 'dept' => 'Sanitation',   'desig' => 'Sanitation Worker', 'salary' => 13000, 'site_idx' => 1, 'shift' => $morningShift, 'mgr' => $manager1],
            ['name' => 'Ajay Singh',     'email' => 'ajay@hrms.com',    'phone' => '9811001007', 'dept' => 'Sanitation',   'desig' => 'Supervisor',        'salary' => 17500, 'site_idx' => 1, 'shift' => $generalShift, 'mgr' => $manager1],
            ['name' => 'Neha Joshi',     'email' => 'neha@hrms.com',    'phone' => '9811001008', 'dept' => 'Sanitation',   'desig' => 'Sanitation Worker', 'salary' => 13500, 'site_idx' => 1, 'shift' => $eveningShift, 'mgr' => $manager1],
            ['name' => 'Vikram Yadav',   'email' => 'vikram@hrms.com',  'phone' => '9811001009', 'dept' => 'Sanitation',   'desig' => 'Sanitation Worker', 'salary' => 13000, 'site_idx' => 1, 'shift' => $eveningShift, 'mgr' => $manager1],
            ['name' => 'Pooja Mishra',   'email' => 'pooja@hrms.com',   'phone' => '9811001010', 'dept' => 'Sanitation',   'desig' => 'Sanitation Worker', 'salary' => 13000, 'site_idx' => 1, 'shift' => $eveningShift, 'mgr' => $manager1],
            // Star Industries Factory (5 workers)
            ['name' => 'Rahul Tiwari',   'email' => 'rahul@hrms.com',   'phone' => '9811002001', 'dept' => 'Housekeeping', 'desig' => 'Cleaner',           'salary' => 14000, 'site_idx' => 2, 'shift' => $morningShift, 'mgr' => $manager2],
            ['name' => 'Anita Rao',      'email' => 'anita@hrms.com',   'phone' => '9811002002', 'dept' => 'Housekeeping', 'desig' => 'Supervisor',        'salary' => 18500, 'site_idx' => 2, 'shift' => $morningShift, 'mgr' => $manager2],
            ['name' => 'Manoj Pandey',   'email' => 'manoj@hrms.com',   'phone' => '9811002003', 'dept' => 'Housekeeping', 'desig' => 'Cleaner',           'salary' => 13500, 'site_idx' => 2, 'shift' => $generalShift, 'mgr' => $manager2],
            ['name' => 'Sneha Kulkarni', 'email' => 'sneha@hrms.com',   'phone' => '9811002004', 'dept' => 'Housekeeping', 'desig' => 'Cleaner',           'salary' => 13000, 'site_idx' => 2, 'shift' => $eveningShift, 'mgr' => $manager2],
            ['name' => 'Dinesh Verma',   'email' => 'dinesh@hrms.com',  'phone' => '9811002005', 'dept' => 'Housekeeping', 'desig' => 'Cleaner',           'salary' => 13000, 'site_idx' => 2, 'shift' => $eveningShift, 'mgr' => $manager2],
            // Star Industries Admin Hub (3 workers)
            ['name' => 'Geeta Pillai',   'email' => 'geeta@hrms.com',   'phone' => '9811002006', 'dept' => 'Facility Mgmt', 'desig' => 'Facility Executive', 'salary' => 16000, 'site_idx' => 3, 'shift' => $generalShift, 'mgr' => $manager2],
            ['name' => 'Rohit Das',      'email' => 'rohit@hrms.com',   'phone' => '9811002007', 'dept' => 'Facility Mgmt', 'desig' => 'Facility Executive', 'salary' => 15500, 'site_idx' => 3, 'shift' => $generalShift, 'mgr' => $manager2],
            ['name' => 'Lakshmi Sinha',  'email' => 'lakshmi@hrms.com', 'phone' => '9811002008', 'dept' => 'Facility Mgmt', 'desig' => 'Supervisor',         'salary' => 19000, 'site_idx' => 3, 'shift' => $morningShift, 'mgr' => $manager2],
            // Metro Corp Main Hospital (8 workers)
            ['name' => 'Arun Pillai',    'email' => 'arun@hrms.com',    'phone' => '9811003001', 'dept' => 'Sanitation',   'desig' => 'Sanitation Worker',  'salary' => 14500, 'site_idx' => 4, 'shift' => $morningShift, 'mgr' => $manager1],
            ['name' => 'Meera Nair',     'email' => 'meera@hrms.com',   'phone' => '9811003002', 'dept' => 'Sanitation',   'desig' => 'Supervisor',         'salary' => 19500, 'site_idx' => 4, 'shift' => $morningShift, 'mgr' => $manager1],
            ['name' => 'Kiran Yadav',    'email' => 'kiran@hrms.com',   'phone' => '9811003003', 'dept' => 'Sanitation',   'desig' => 'Sanitation Worker',  'salary' => 14000, 'site_idx' => 4, 'shift' => $eveningShift, 'mgr' => $manager1],
            ['name' => 'Sanjay Bose',    'email' => 'sanjay@hrms.com',  'phone' => '9811003004', 'dept' => 'Sanitation',   'desig' => 'Sanitation Worker',  'salary' => 13500, 'site_idx' => 4, 'shift' => $eveningShift, 'mgr' => $manager1],
            ['name' => 'Trisha Ghosh',   'email' => 'trisha@hrms.com',  'phone' => '9811003005', 'dept' => 'Housekeeping', 'desig' => 'Cleaner',            'salary' => 13000, 'site_idx' => 4, 'shift' => $morningShift, 'mgr' => $manager1],
            ['name' => 'Ganesh Kumar',   'email' => 'ganesh@hrms.com',  'phone' => '9811003006', 'dept' => 'Housekeeping', 'desig' => 'Cleaner',            'salary' => 13000, 'site_idx' => 4, 'shift' => $eveningShift, 'mgr' => $manager1],
            // Metro Corp Clinic Annex (4 workers)
            ['name' => 'Pradeep Jain',   'email' => 'pradeep@hrms.com', 'phone' => '9811003007', 'dept' => 'Housekeeping', 'desig' => 'Cleaner',            'salary' => 13000, 'site_idx' => 5, 'shift' => $morningShift, 'mgr' => $manager2],
            ['name' => 'Sushma Tiwari',  'email' => 'sushma@hrms.com',  'phone' => '9811003008', 'dept' => 'Housekeeping', 'desig' => 'Supervisor',         'salary' => 17000, 'site_idx' => 5, 'shift' => $morningShift, 'mgr' => $manager2],
            ['name' => 'Devraj Menon',   'email' => 'devraj@hrms.com',  'phone' => '9811003009', 'dept' => 'Housekeeping', 'desig' => 'Cleaner',            'salary' => 13000, 'site_idx' => 5, 'shift' => $eveningShift, 'mgr' => $manager2],
            // Demo / Login employee
            ['name' => 'Test Employee',  'email' => 'employee@hrms.com','phone' => '9800000001', 'dept' => 'Housekeeping', 'desig' => 'Cleaner',            'salary' => 14000, 'site_idx' => 0, 'shift' => $generalShift, 'mgr' => $manager1],
        ];

        $employees = [];
        foreach ($employeesConfig as $idx => $ec) {
            $empNum = str_pad($idx + 1, 3, '0', STR_PAD_LEFT);
            $user = User::create([
                'name'     => $ec['name'],
                'email'    => $ec['email'],
                'password' => Hash::make('password'),
                'role'     => 'employee',
                'role_id'  => $employeeRole?->id,
                'status'   => 'active',
            ]);
            EmployeeDetail::create([
                'user_id'           => $user->id,
                'employee_id'       => 'EMP' . $empNum,
                'site_id'           => $allSites[$ec['site_idx']]->id,
                'shift_id'          => $ec['shift']?->id,
                'manager_id'        => $ec['mgr']->id,
                'phone'             => $ec['phone'],
                'address'           => 'Delhi NCR, India',
                'department'        => $ec['dept'],
                'designation'       => $ec['desig'],
                'employment_type'   => 'On-roll',
                'joining_date'      => Carbon::now()->subMonths(rand(3, 18)),
                'basic_salary'      => $ec['salary'],
                'bank_name'         => 'State Bank of India',
                'account_number'    => '3' . rand(100000000000, 999999999999),
                'ifsc_code'         => 'SBIN000' . rand(1000, 9999),
                'emergency_contact_name'  => 'Family Member',
                'emergency_contact_phone' => '98' . rand(10000000, 99999999),
            ]);
            $employees[] = ['user' => $user, 'site' => $allSites[$ec['site_idx']], 'salary' => $ec['salary']];
        }
        $this->command->info('  ✓ Employees created (' . count($employees) . ' employees)');

        // ── Leave Allocations ─────────────────────────────────────────────
        foreach ($employees as $e) {
            foreach (['casual', 'sick', 'earned'] as $type) {
                LeaveAllocation::create([
                    'user_id'    => $e['user']->id,
                    'leave_type' => $type,
                    'total_days' => $type === 'earned' ? 15 : 6,
                    'used_days'  => 0,
                    'year'       => date('Y'),
                ]);
            }
        }
        $this->command->info('  ✓ Leave allocations created');

        // ══════════════════════════════════════════════════════════════════
        // HOLIDAYS (2026)
        // ══════════════════════════════════════════════════════════════════
        $holidays = [
            ['name' => 'Republic Day',       'date' => '2026-01-26', 'type' => 'paid'],
            ['name' => 'Holi',               'date' => '2026-03-20', 'type' => 'paid'],
            ['name' => 'Good Friday',        'date' => '2026-04-03', 'type' => 'paid'],
            ['name' => 'Ram Navami',         'date' => '2026-04-06', 'type' => 'paid'],
            ['name' => 'Labour Day',         'date' => '2026-05-01', 'type' => 'paid'],
            ['name' => 'Independence Day',   'date' => '2026-08-15', 'type' => 'paid'],
            ['name' => 'Gandhi Jayanti',     'date' => '2026-10-02', 'type' => 'paid'],
            ['name' => 'Dussehra',           'date' => '2026-10-22', 'type' => 'paid'],
            ['name' => 'Diwali',             'date' => '2026-11-08', 'type' => 'paid'],
            ['name' => 'Christmas',          'date' => '2026-12-25', 'type' => 'paid'],
        ];
        foreach ($holidays as $h) {
            Holiday::create([
                'name'            => $h['name'],
                'start_date'      => $h['date'],
                'end_date'        => $h['date'],
                'type'            => $h['type'],
                'applicable_type' => 'all',
                'status'          => 'active',
                'description'     => 'National / Company Holiday',
            ]);
        }
        $this->command->info('  ✓ Holidays created');

        // ══════════════════════════════════════════════════════════════════
        // DAILY ASSIGNMENTS + ATTENDANCE (past 45 days)
        // ══════════════════════════════════════════════════════════════════
        // Map each employee to its contract
        $siteContractMap = [];
        foreach ($allContracts as $contract) {
            $siteIds = DB::table('contract_sites')->where('contract_id', $contract->id)->pluck('site_id');
            foreach ($siteIds as $sid) {
                $siteContractMap[$sid] = $contract;
            }
        }

        $holidayDates = collect($holidays)->pluck('date')->toArray();

        // Attendance pattern weights: present=70%, late=15%, absent=10%, half_day=5%
        $patterns = array_merge(
            array_fill(0, 70, 'present'),
            array_fill(0, 15, 'late'),
            array_fill(0, 10, 'absent'),
            array_fill(0, 5,  'half_day')
        );

        $shiftTimes = [
            'Morning Shift' => ['in' => '06:00', 'out' => '14:00'],
            'Evening Shift' => ['in' => '14:00', 'out' => '22:00'],
            'General Shift' => ['in' => '09:00', 'out' => '18:00'],
            'Night Shift'   => ['in' => '22:00', 'out' => '06:00'],
        ];

        $attendanceBulk    = [];
        $assignmentBulk    = [];
        $now               = now();

        foreach ($employees as $empData) {
            $user    = $empData['user'];
            $site    = $empData['site'];
            $contract = $siteContractMap[$site->id] ?? null;
            $detail  = $user->employeeDetail;
            $shift   = $detail?->shift;
            $shiftName = $shift?->name ?? 'General Shift';
            $times   = $shiftTimes[$shiftName] ?? $shiftTimes['General Shift'];

            for ($daysAgo = 45; $daysAgo >= 0; $daysAgo--) {
                $date = Carbon::today()->subDays($daysAgo);

                // Skip Sundays and public holidays
                if ($date->isSunday()) continue;
                if (in_array($date->format('Y-m-d'), $holidayDates)) continue;
                // Skip future dates beyond today
                if ($date->gt(Carbon::today())) continue;

                $pattern = $patterns[array_rand($patterns)];

                // Daily Assignment
                if ($contract) {
                    $assignmentBulk[] = [
                        'user_id'         => $user->id,
                        'site_id'         => $site->id,
                        'contract_id'     => $contract->id,
                        'shift_id'        => $shift?->id,
                        'assigned_date'   => $date->format('Y-m-d'),
                        'assignment_type' => 'permanent',
                        'assigned_by'     => $admin->id,
                        'status'          => 'confirmed',
                        'notes'           => null,
                        'created_at'      => $now,
                        'updated_at'      => $now,
                    ];
                }

                if ($pattern === 'absent') continue;

                // Build attendance
                [$inH, $inM] = explode(':', $times['in']);
                [$outH, $outM] = explode(':', $times['out']);

                $clockIn = $date->copy()->setTime((int)$inH, (int)$inM);
                $clockOut = $date->copy()->setTime((int)$outH, (int)$outM);
                if ($clockOut->lte($clockIn)) $clockOut->addDay(); // night shift

                if ($pattern === 'late') {
                    $clockIn->addMinutes(rand(20, 60));
                }
                if ($pattern === 'half_day') {
                    $clockOut = $clockIn->copy()->addHours(4);
                }

                $durationMinutes = (int) $clockIn->diffInMinutes($clockOut);

                $attendanceBulk[] = [
                    'user_id'          => $user->id,
                    'site_id'          => $site->id,
                    'client_id'        => $site->client_id,
                    'date'             => $date->format('Y-m-d'),
                    'clock_in'         => $clockIn->format('H:i:s'),
                    'clock_out'        => $clockOut->format('H:i:s'),
                    'duration_minutes' => $durationMinutes,
                    'status'           => $pattern,
                    'is_locked'        => ($daysAgo > 10) ? 1 : 0,
                    'is_verified'      => 1,
                    'distance_detected'=> rand(10, 280),
                    'source'           => ($daysAgo <= 7) ? 'client_portal' : 'admin',
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];
            }
        }

        // Bulk insert in chunks
        foreach (array_chunk($assignmentBulk, 200) as $chunk) {
            DB::table('daily_assignments')->insert($chunk);
        }
        foreach (array_chunk($attendanceBulk, 200) as $chunk) {
            DB::table('attendances')->insert($chunk);
        }
        $this->command->info('  ✓ Assignments & Attendance created (' . count($attendanceBulk) . ' records)');

        // ══════════════════════════════════════════════════════════════════
        // LEAVES
        // ══════════════════════════════════════════════════════════════════
        $leaveTypes   = ['casual', 'sick', 'earned'];
        foreach ($employees as $i => $e) {
            // Past leave (approved)
            Leave::create([
                'user_id'     => $e['user']->id,
                'leave_type'  => $leaveTypes[$i % 3],
                'start_date'  => Carbon::now()->subDays(rand(20, 40))->format('Y-m-d'),
                'end_date'    => Carbon::now()->subDays(rand(10, 19))->format('Y-m-d'),
                'reason'      => 'Personal work',
                'status'      => 'approved',
                'admin_comment' => 'Approved as requested.',
            ]);
        }

        // A few pending leaves
        foreach (array_slice($employees, 0, 5) as $e) {
            Leave::create([
                'user_id'    => $e['user']->id,
                'leave_type' => 'sick',
                'start_date' => Carbon::today()->addDays(2)->format('Y-m-d'),
                'end_date'   => Carbon::today()->addDays(3)->format('Y-m-d'),
                'reason'     => 'Medical appointment',
                'status'     => 'pending',
            ]);
        }

        // A rejected leave
        Leave::create([
            'user_id'    => $employees[0]['user']->id,
            'leave_type' => 'earned',
            'start_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
            'end_date'   => Carbon::now()->subDays(4)->format('Y-m-d'),
            'reason'     => 'Family function',
            'status'     => 'rejected',
            'admin_comment' => 'Insufficient leave balance for this period.',
        ]);

        $this->command->info('  ✓ Leaves created');

        // ══════════════════════════════════════════════════════════════════
        // PAYROLL (Feb + Mar 2026)
        // ══════════════════════════════════════════════════════════════════
        $payrollMonths = [
            ['month' => 'February', 'year' => 2026, 'working_days' => 24, 'present_days' => 22, 'status' => 'paid'],
            ['month' => 'March',    'year' => 2026, 'working_days' => 26, 'present_days' => 24, 'status' => 'pending'],
        ];

        foreach ($employees as $e) {
            foreach ($payrollMonths as $pm) {
                $basic     = $e['salary'];
                $perDay    = round($basic / $pm['working_days'], 2);
                $payable   = $pm['present_days'];
                $gross     = round($perDay * $payable, 2);
                $hra       = round($basic * 0.10, 2);
                $washing   = 500;
                $pf        = round($basic * 0.12, 2);
                $esi       = $basic <= 21000 ? round($gross * 0.0075, 2) : 0;
                $pt        = 200;
                $net       = $gross + $hra + $washing - $pf - $esi - $pt;

                Payroll::create([
                    'user_id'         => $e['user']->id,
                    'month'           => $pm['month'],
                    'year'            => $pm['year'],
                    'total_days'      => 28 + ($pm['month'] === 'March' ? 3 : 0),
                    'working_days'    => $pm['working_days'],
                    'present_days'    => $pm['present_days'],
                    'payable_days'    => $payable,
                    'paid_holidays'   => 1,
                    'basic_salary'    => $basic,
                    'per_day_salary'  => $perDay,
                    'gross_salary'    => $gross,
                    'hra'             => $hra,
                    'washing_allowance' => $washing,
                    'allowances'      => $hra + $washing,
                    'pf_amount'       => $pf,
                    'esi_amount'      => $esi,
                    'pt_amount'       => $pt,
                    'deductions'      => $pf + $esi + $pt,
                    'net_salary'      => round($net, 2),
                    'bank_amount'     => round($net, 2),
                    'cash_amount'     => 0,
                    'status'          => $pm['status'],
                ]);
            }
        }
        $this->command->info('  ✓ Payroll created (2 months × ' . count($employees) . ' employees)');

        // ══════════════════════════════════════════════════════════════════
        // WORKER REPLACEMENTS (a few samples)
        // ══════════════════════════════════════════════════════════════════
        $firstAssignment = DB::table('daily_assignments')
            ->where('user_id', $employees[2]['user']->id)
            ->orderBy('assigned_date')
            ->first();

        if ($firstAssignment) {
            WorkerReplacement::create([
                'original_assignment_id'  => $firstAssignment->id,
                'original_worker_id'      => $employees[2]['user']->id,
                'replacement_worker_id'   => $employees[3]['user']->id,
                'site_id'                 => $firstAssignment->site_id,
                'shift_id'                => $firstAssignment->shift_id,
                'replacement_date'        => Carbon::today()->subDays(5)->format('Y-m-d'),
                'reason'                  => 'leave',
                'reason_details'          => 'Original worker reported sick, replacement arranged.',
                'requested_by'            => $manager1->id,
                'approved_by'             => $admin->id,
                'status'                  => 'approved',
            ]);
        }
        $this->command->info('  ✓ Worker replacements created');

        // ══════════════════════════════════════════════════════════════════
        // NOTIFICATIONS
        // ══════════════════════════════════════════════════════════════════
        $notifs = [
            ['title' => 'Leave Request Pending',      'message' => 'Amit Sharma has submitted a sick leave request for approval.',           'type' => 'warning', 'user_id' => $admin->id],
            ['title' => 'New Contract Created',       'message' => 'Contract CNT-GLO-2026-001 for Global Logistics Ltd has been activated.', 'type' => 'success', 'user_id' => $admin->id],
            ['title' => 'Payroll Processed',          'message' => 'March 2026 payroll has been processed for 28 employees.',                'type' => 'success', 'user_id' => $admin->id],
            ['title' => 'Worker Replacement Approved','message' => 'Replacement for Ravi Gupta on 18 Mar 2026 has been approved.',          'type' => 'info',    'user_id' => $manager1->id],
            ['title' => 'Attendance Correction',      'message' => 'Priya Patel submitted a correction request for 15 Mar 2026.',           'type' => 'warning', 'user_id' => $manager1->id],
            ['title' => 'Contract Expiring Soon',     'message' => 'Contract CNT-MET-2026-001 expires in 30 days. Review renewal.',         'type' => 'danger',  'user_id' => $admin->id],
        ];

        foreach ($notifs as $n) {
            Notification::create([
                'user_id'    => $n['user_id'],
                'title'      => $n['title'],
                'message'    => $n['message'],
                'type'       => $n['type'],
                'is_read'    => false,
                'created_at' => Carbon::now()->subMinutes(rand(5, 1440)),
                'updated_at' => now(),
            ]);
        }
        $this->command->info('  ✓ Notifications created');

        // ─────────────────────────────────────────────────────────────────
        $this->command->info('');
        $this->command->info('✅ Demo Data Ready! Login Credentials:');
        $this->command->info('  Admin    → admin@hrms.com    / password');
        $this->command->info('  Manager  → manager@hrms.com  / password');
        $this->command->info('  Employee → employee@hrms.com / password');
        $this->command->info('  Client 1 → client@hrms.com   / password  (Global Logistics)');
        $this->command->info('  Client 2 → client2@hrms.com  / password  (Star Industries)');
        $this->command->info('  Client 3 → client3@hrms.com  / password  (Metro Corp)');
    }
}
