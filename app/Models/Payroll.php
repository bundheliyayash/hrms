<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory, \App\Traits\LogsActivity;

    protected $fillable = [
        'user_id',
        'month',
        'year',
        'basic_salary',
        'total_days',
        'working_days',
        'present_days',
        'payable_days',
        'paid_holidays',
        'ot_hours',
        'ot_amount',
        'per_day_salary',
        'allowances',
        'deductions',
        'pf_amount',
        'esi_amount',
        'net_salary',
        'status',
    ];

    public $logEvents = ['created', 'updated', 'deleted'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
