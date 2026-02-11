<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'clock_in_time',
        'clock_out_time',
        'late_threshold_minutes',
        'early_out_threshold_minutes',
        'break_duration_minutes',
        'is_active'
    ];

    public function isNightShift(): bool
    {
        if (!$this->clock_in_time || !$this->clock_out_time) return false;
        return $this->clock_out_time < $this->clock_in_time;
    }

    public function getDurationMinutes(): int
    {
        $in = \Carbon\Carbon::parse($this->clock_in_time);
        $out = \Carbon\Carbon::parse($this->clock_out_time);

        if ($this->isNightShift()) {
            $out->addDay();
        }

        return $in->diffInMinutes($out);
    }

    public function employeeDetails()
    {
        return $this->hasMany(EmployeeDetail::class);
    }
}
