<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HrmsAccessTest extends TestCase
{
    // Use RefreshDatabase to reset DB for tests, but be careful if using local DB.
    // Ideally, use sqlite :memory: for tests, but we configured mysql.
    // For this simple base project, we will skip RefreshDatabase to avoid wiping user's work
    // or we should use a separate test env. Laravel defaults to sqlite in phpunit.xml usually.
    // Let's check phpunit.xml first. Assuming default is used.
    
    use RefreshDatabase;

    public function test_admin_can_access_dashboard()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_employee_cannot_access_admin_employees_page()
    {
        $employee = User::factory()->create(['role' => 'employee']);

        $response = $this->actingAs($employee)->get('/admin/employees');

        $response->assertStatus(403); // Forbidden
    }

    public function test_employee_can_access_attendance()
    {
        $employee = User::factory()->create(['role' => 'employee']);

        $response = $this->actingAs($employee)->get('/employee/attendance');

        $response->assertStatus(200);
    }

    public function test_admin_can_load_reports()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/reports');

        $response->assertStatus(200);
    }
}
