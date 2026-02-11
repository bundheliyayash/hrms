<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use HasFactory, \App\Traits\LogsActivity;

    protected $fillable = [
        'user_id',
        'leave_type',
        'start_date',
        'end_date',
        'reason',
        'status',
        'is_half_day',
        'half_day_period',
        'admin_comment',
    ];

    public $logEvents = ['updated'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
