<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\EmployeeDetail;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Setting;
use App\Services\PayrollService;
use App\Services\HolidayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class PayrollServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $payrollService;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed RBAC
        $this->seed(\Database\Seeders\RbacSeeder::class);
        $this->seed(\Database\Seeders\SettingsSeeder::class);
        
        $this->payrollService = new PayrollService(new HolidayService());
    }

    public function test_calculate_salary_basic_flow()
    {
        // 1. Create Employee
        $user = User::factory()->create(['role' => 'employee']);
        EmployeeDetail::create([
            'user_id' => $user->id,
            'employee_id' => 'EMP001',
            'joining_date' => now()->subYear(),
            'basic_salary' => 30000,
        ]);

        // 2a. Create Site
        $client = \App\Models\Client::factory()->create();
        $site = \App\Models\ClientSite::factory()->create(['client_id' => $client->id]);

        // 2b. Create Attendance for January 2024
        $month = 1;
        $year = 2024;
        $baseDate = Carbon::create(2024, 1, 1);

        // 20 Present days
        for ($i = 1; $i <= 20; $i++) {
            Attendance::create([
                'user_id' => $user->id,
                'date' => $baseDate->copy()->addDays($i - 1),
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
                'status' => 'present',
                'site_id' => $site->id, 
            ]);
        }

        // 3. Calculate
        $stats = $this->payrollService->calculateSalary($user, $month, $year);

        // 4. Assert
        $this->assertEquals(31, $stats['total_days']); // Jan has 31 days
        $this->assertEquals(20, $stats['present_days']);
        $this->assertEquals(20, $stats['payable_days']);
        
        // Salary = (30000 / 31) * 20
        $expectedSalary = (30000 / 31) * 20;
        $this->assertEquals(round($expectedSalary, 2), $stats['base_earnings']);
    }
}
