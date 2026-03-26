<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class PayrollConfigSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // PF
            ['key' => 'pf_percentage',          'value' => '12',    'group' => 'payroll', 'type' => 'float'],
            ['key' => 'pf_employer_percentage',  'value' => '13',    'group' => 'payroll', 'type' => 'float'],
            // ESI
            ['key' => 'esi_percentage',          'value' => '0.75',  'group' => 'payroll', 'type' => 'float'],
            ['key' => 'esi_employer_percentage', 'value' => '3.25',  'group' => 'payroll', 'type' => 'float'],
            // Professional Tax (flat monthly amount)
            ['key' => 'pt_amount',               'value' => '200',   'group' => 'payroll', 'type' => 'float'],
            // Allowances (as % of basic salary)
            ['key' => 'hra_percentage',          'value' => '10',    'group' => 'payroll', 'type' => 'float'],
            ['key' => 'washing_allowance_percentage', 'value' => '5', 'group' => 'payroll', 'type' => 'float'],
            // Billing / Invoice
            ['key' => 'gst_percentage',          'value' => '18',    'group' => 'billing', 'type' => 'float'],
            // General
            ['key' => 'working_hours_per_day',   'value' => '8',     'group' => 'payroll', 'type' => 'integer'],
        ];

        foreach ($defaults as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'group' => $setting['group'], 'type' => $setting['type']]
            );
        }
    }
}
