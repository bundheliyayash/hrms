<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Holiday extends Model
{
    use HasFactory, \App\Traits\LogsActivity;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'type',
        'applicable_type',
        'status',
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public $logEvents = ['created', 'updated', 'deleted'];

    public function departments()
    {
        return $this->hasMany(HolidayDepartment::class);
    }

    public function sites()
    {
        return $this->belongsToMany(ClientSite::class, 'holiday_site', 'holiday_id', 'site_id');
    }
}
