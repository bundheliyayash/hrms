<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = $user->employeeDetail;
        
        // Get editable fields configuration (admin controlled)
        $editableFields = $this->getEditableFields();
        
        return view('employee.profile.index', compact('user', 'employee', 'editableFields'));
    }
    
    public function update(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employeeDetail;
        
        // Get editable fields
        $editableFields = $this->getEditableFields();
        
        // Build validation rules based on editable fields
        $rules = [];
        if ($editableFields['phone']) {
            $rules['phone'] = 'nullable|string|max:15';
        }
        if ($editableFields['email']) {
            $rules['email'] = 'required|email|unique:users,email,' . $user->id;
        }
        if ($editableFields['address']) {
            $rules['address'] = 'nullable|string|max:255';
        }
        if ($editableFields['emergency_contact']) {
            $rules['emergency_contact'] = 'nullable|string|max:15';
        }
        if ($editableFields['blood_group']) {
            $rules['blood_group'] = 'nullable|string|max:10';
        }
        if ($editableFields['date_of_birth']) {
            $rules['date_of_birth'] = 'nullable|date';
        }
        if ($editableFields['gender']) {
            $rules['gender'] = 'nullable|string|in:Male,Female,Other';
        }
        
        $validated = $request->validate($rules);
        
        // Update only editable fields
        if ($editableFields['email'] && isset($validated['email'])) {
            $user->update(['email' => $validated['email']]);
        }
        
        if ($employee) {
            $employeeData = [];
            if ($editableFields['phone'] && isset($validated['phone'])) {
                $employeeData['phone'] = $validated['phone'];
            }
            if ($editableFields['address'] && isset($validated['address'])) {
                $employeeData['address'] = $validated['address'];
            }
            if ($editableFields['emergency_contact'] && isset($validated['emergency_contact'])) {
                $employeeData['emergency_contact'] = $validated['emergency_contact'];
            }
            if ($editableFields['blood_group'] && isset($validated['blood_group'])) {
                $employeeData['blood_group'] = $validated['blood_group'];
            }
            if ($editableFields['date_of_birth'] && isset($validated['date_of_birth'])) {
                $employeeData['date_of_birth'] = $validated['date_of_birth'];
            }
            if ($editableFields['gender'] && isset($validated['gender'])) {
                $employeeData['gender'] = $validated['gender'];
            }
            
            if (!empty($employeeData)) {
                $employee->update($employeeData);
            }
        }
        
        return redirect()->back()->with('success', 'Profile updated successfully!');
    }
    
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        
        $user = Auth::user();
        
        // Delete old photo if exists
        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
        }
        
        // Store new photo
        $path = $request->file('profile_photo')->store('profile_photos', 'public');
        
        $user->update(['profile_photo' => $path]);
        
        return redirect()->back()->with('success', 'Profile photo updated successfully!');
    }
    
    private function getEditableFields()
    {
        $fields = [
            'email', 'phone', 'address', 'emergency_contact', 'blood_group', 'date_of_birth', 'gender', 'profile_photo'
        ];

        $config = [];
        foreach ($fields as $field) {
            $setting = \App\Models\Setting::where('key', 'edit_profile_' . $field)->first();
            // Default to true for basic fields if not set, false for others
            $default = in_array($field, ['email', 'phone', 'address', 'emergency_contact', 'blood_group', 'profile_photo']);
            $config[$field] = $setting ? (bool)$setting->value : $default;
        }

        return $config;
    }
}
