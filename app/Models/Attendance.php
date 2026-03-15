<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use HasFactory, \App\Traits\LogsActivity, SoftDeletes;

    protected $fillable = [
        'user_id',
        'site_id',
        'client_id',
        'date',
        'clock_in',
        'clock_out',
        'duration_minutes',
        'status',
        'is_locked',
        'flag_reason',
        'latitude',
        'longitude',
        'is_verified',
        'distance_detected',
        'source',
    ];

    protected $casts = [
        'date' => 'date',
        'is_locked' => 'boolean',
        'is_verified' => 'boolean',
    ];

    public $logEvents = ['updated', 'deleted'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function site()
    {
        return $this->belongsTo(ClientSite::class, 'site_id');
    }

    public function breaks()
    {
        return $this->hasMany(AttendanceBreak::class);
    }

    public function corrections()
    {
        return $this->hasMany(AttendanceCorrection::class);
    }

    public function getDurationAttribute()
    {
        if (!$this->clock_in || !$this->clock_out) return 0;

        $in = \Carbon\Carbon::parse($this->date->format('Y-m-d') . ' ' . $this->clock_in);
        $out = \Carbon\Carbon::parse($this->date->format('Y-m-d') . ' ' . $this->clock_out);

        if ($out->lt($in)) {
            $out->addDay();
        }

        $totalMinutes = $in->diffInMinutes($out);
        $breakMinutes = $this->breaks()->sum('duration_minutes');

        return max(0, $totalMinutes - $breakMinutes);
    }

    public function dailyAssignment()
    {
        return $this->hasOne(DailyAssignment::class, 'user_id', 'user_id')
            ->whereColumn('daily_assignments.assigned_date', 'attendances.date')
            ->whereColumn('daily_assignments.site_id', 'attendances.site_id');
    }
}
