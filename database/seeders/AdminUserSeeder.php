<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure Admin Role exists (RbacSeeder should have run)
        $adminRole = \App\Models\Role::where('name', 'admin')->first();
        
        if (!$adminRole) {
            $this->command->error('Admin Role not found! Ensure RbacSeeder runs before AdminUserSeeder.');
            return;
        }

        User::firstOrCreate(
            ['email' => 'admin@hrms.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password'),
                'role' => 'admin', // Legacy string column for quick checks
                'role_id' => $adminRole->id, // Foreign key for RBAC
                'status' => 'active',
            ]
        );
    }
}
