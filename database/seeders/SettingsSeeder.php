<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder populates the settings table with system defaults.
     * Uses updateOrCreate() to be idempotent - safe to run multiple times.
     */
    public function run(): void
    {
        DB::beginTransaction();
        
        try {
            // Application Settings
            $settings = [
                // General Application Settings
                [
                    'key' => 'app_name',
                    'value' => 'CleanHR HRMS',
                    'type' => 'string',
                    'group' => 'general'
                ],
                [
                    'key' => 'company_name',
                    'value' => 'CleanHR',
                    'type' => 'string',
                    'group' => 'general'
                ],
                [
                    'key' => 'footer_text',
                    'value' => '© ' . date('Y') . ' CleanHR - All Rights Reserved',
                    'type' => 'string',
                    'group' => 'general'
                ],
                
                // Notification Toggles
                [
                    'key' => 'notify_attendance',
                    'value' => '1',
                    'type' => 'boolean',
                    'group' => 'notifications'
                ],
                [
                    'key' => 'notify_leaves',
                    'value' => '1',
                    'type' => 'boolean',
                    'group' => 'notifications'
                ],
                [
                    'key' => 'notify_payroll',
                    'value' => '1',
                    'type' => 'boolean',
                    'group' => 'notifications'
                ],
                
                // Profile Edit Permissions
                [
                    'key' => 'edit_profile_name',
                    'value' => '0',
                    'type' => 'boolean',
                    'group' => 'profile_permissions'
                ],
                [
                    'key' => 'edit_profile_email',
                    'value' => '0',
                    'type' => 'boolean',
                    'group' => 'profile_permissions'
                ],
                [
                    'key' => 'edit_profile_phone',
                    'value' => '1',
                    'type' => 'boolean',
                    'group' => 'profile_permissions'
                ],
                [
                    'key' => 'edit_profile_address',
                    'value' => '1',
                    'type' => 'boolean',
                    'group' => 'profile_permissions'
                ],
            ];

            // Insert or update each setting
            foreach ($settings as $setting) {
                Setting::updateOrCreate(
                    ['key' => $setting['key']], // Search by key
                    $setting // Update or create with these values
                );
            }

            DB::commit();
            
            $this->command->info('✓ Settings seeded successfully (' . count($settings) . ' settings)');
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('✗ Settings seeding failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
