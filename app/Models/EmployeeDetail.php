<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDetail extends Model
{
    use HasFactory, \App\Traits\LogsActivity;

    protected $fillable = [
        'user_id',
        'employee_id',
        'site_id',
        'shift_id',
        'manager_id',
        'phone',
        'address',
        'department',
        'designation',
        'employment_type',
        'joining_date',
        'basic_salary',
        'bank_name',
        'account_number',
        'ifsc_code',
        'aadhar_number',
        'pan_number',
        'emergency_contact_name',
        'emergency_contact_phone',
    ];

    public $logEvents = ['updated'];

    public function site()
    {
        return $this->belongsTo(ClientSite::class, 'site_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
