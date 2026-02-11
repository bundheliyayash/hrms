<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveAllocation extends Model
{
    protected $fillable = ['user_id', 'leave_type', 'total_days', 'used_days', 'year'];
}
