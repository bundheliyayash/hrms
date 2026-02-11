<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HolidayDepartment extends Model
{
    protected $table = 'holiday_department';
    protected $fillable = ['holiday_id', 'department'];

    public function holiday()
    {
        return $this->belongsTo(Holiday::class);
    }
}
