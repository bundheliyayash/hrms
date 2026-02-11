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

        return view('admin.settings.index', compact('profileFields', 'permissions', 'generalSettings', 'notifConfig'));
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
