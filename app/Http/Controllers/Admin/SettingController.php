<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display a listing of settings.
     */
    public function index()
    {
        $profileFields = [
            'email' => 'Email Address',
            'phone' => 'Phone Number',
            'address' => 'Current Address',
            'emergency_contact' => 'Emergency Contact',
            'blood_group' => 'Blood Group',
            'date_of_birth' => 'Date of Birth',
            'gender' => 'Gender',
            'profile_photo' => 'Profile Photo'
        ];

        // Fetch current profile permissions
        $permissions = [];
        foreach ($profileFields as $key => $label) {
            $setting = Setting::where('key', 'edit_profile_' . $key)->first();
            // Default logic to match what was there but more robust
            $default = in_array($key, ['email', 'phone', 'address', 'emergency_contact', 'blood_group', 'profile_photo']);
            $permissions[$key] = $setting ? (bool)$setting->value : $default;
        }

        // General Settings
        $generalSettings = [
            'app_name' => Setting::where('key', 'app_name')->first()?->value ?? config('app.name'),
            'company_name' => Setting::where('key', 'company_name')->first()?->value ?? 'CleanHR',
            'footer_text' => Setting::where('key', 'footer_text')->first()?->value ?? '© ' . date('Y') . ' CleanHR'
        ];

        // Notification Config
        $notifConfig = [
            'notify_attendance' => Setting::where('key', 'notify_attendance')->first()?->value ?? '1',
            'notify_leaves' => Setting::where('key', 'notify_leaves')->first()?->value ?? '1',
            'notify_payroll' => Setting::where('key', 'notify_payroll')->first()?->value ?? '1'
        ];

        // Payroll Config
        $payrollConfig = [
            'pf_percentage'               => Setting::get('pf_percentage', 12),
            'pf_employer_percentage'      => Setting::get('pf_employer_percentage', 13),
            'esi_percentage'              => Setting::get('esi_percentage', 0.75),
            'esi_employer_percentage'     => Setting::get('esi_employer_percentage', 3.25),
            'pt_amount'                   => Setting::get('pt_amount', 200),
            'hra_percentage'              => Setting::get('hra_percentage', 10),
            'washing_allowance_percentage'=> Setting::get('washing_allowance_percentage', 5),
            'gst_percentage'              => Setting::get('gst_percentage', 18),
            'working_hours_per_day'       => Setting::get('working_hours_per_day', 8),
        ];

        return view('admin.settings.index', compact('profileFields', 'permissions', 'generalSettings', 'notifConfig', 'payrollConfig'));
    }

    /**
     * Update profile field permissions.
     */
    public function updateProfilePermissions(Request $request)
    {
        $profileFields = [
            'email', 'phone', 'address', 'emergency_contact', 'blood_group', 'date_of_birth', 'gender', 'profile_photo'
        ];

        foreach ($profileFields as $field) {
            $value = $request->has('fields.' . $field) ? '1' : '0';
            Setting::updateOrCreate(
                ['key' => 'edit_profile_' . $field],
                ['value' => $value, 'group' => 'profile_fields', 'type' => 'boolean']
            );
        }

        \Illuminate\Support\Facades\Cache::forget('app_settings');
        return redirect()->back()->with('success', 'Profile field permissions updated successfully.');
    }

    /**
     * Update general settings.
     */
    public function updateGeneralSettings(Request $request)
    {
        $settings = $request->only(['app_name', 'company_name', 'footer_text']);

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => 'general', 'type' => 'string']
            );
        }

        \Illuminate\Support\Facades\Cache::forget('app_settings');
        return redirect()->back()->with('success', 'General settings updated successfully.');
    }

    /**
     * Update payroll configuration settings.
     */
    public function updatePayrollConfig(Request $request)
    {
        $request->validate([
            'pf_percentage'               => 'required|numeric|min:0|max:100',
            'pf_employer_percentage'      => 'required|numeric|min:0|max:100',
            'esi_percentage'              => 'required|numeric|min:0|max:100',
            'esi_employer_percentage'     => 'required|numeric|min:0|max:100',
            'pt_amount'                   => 'required|numeric|min:0',
            'hra_percentage'              => 'required|numeric|min:0|max:100',
            'washing_allowance_percentage'=> 'required|numeric|min:0|max:100',
            'gst_percentage'              => 'required|numeric|min:0|max:100',
            'working_hours_per_day'       => 'required|integer|min:1|max:24',
        ]);

        $keys = [
            'pf_percentage', 'pf_employer_percentage',
            'esi_percentage', 'esi_employer_percentage',
            'pt_amount', 'hra_percentage', 'washing_allowance_percentage',
            'gst_percentage', 'working_hours_per_day',
        ];

        foreach ($keys as $key) {
            Setting::set($key, $request->input($key), 'payroll', 'float');
        }

        \Illuminate\Support\Facades\Cache::forget('app_settings');
        return redirect()->back()->with('success', 'Payroll configuration updated successfully.');
    }

    /**
     * Update notification settings.
     */
    public function updateNotificationSettings(Request $request)
    {
        $fields = ['notify_attendance', 'notify_leaves', 'notify_payroll'];

        foreach ($fields as $field) {
            $value = $request->has($field) ? '1' : '0';
            Setting::updateOrCreate(
                ['key' => $field],
                ['value' => $value, 'group' => 'notifications', 'type' => 'boolean']
            );
        }

        \Illuminate\Support\Facades\Cache::forget('app_settings');
        return redirect()->back()->with('success', 'Notification configurations updated successfully.');
    }
}
