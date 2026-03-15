<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'name', 'contact_person', 'email', 'phone', 'address', 
        'is_active', 'service_start_date', 'service_end_date', 'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function sites()
    {
        return $this->hasMany(ClientSite::class);
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Check if the client's service is currently active.
     */
    public function isServiceActive()
    {
        if (!$this->is_active) {
            return false;
        }

        $today = now()->startOfDay();

        if ($this->service_start_date && $today->lt(\Carbon\Carbon::parse($this->service_start_date))) {
            return false;
        }

        if ($this->service_end_date && $today->gt(\Carbon\Carbon::parse($this->service_end_date))) {
            return false;
        }

        return true;
    }
}
