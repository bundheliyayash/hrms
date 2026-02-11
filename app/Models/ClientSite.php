<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClientSite extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = ['client_id', 'site_name', 'address', 'latitude', 'longitude', 'radius_meters', 'is_active'];

    public function client()
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    /**
     * Get the employees assigned to this site.
     */
    public function employees()
    {
        return $this->hasMany(EmployeeDetail::class, 'site_id');
    }

    /**
     * Get the contracts linked to this site.
     */
    public function contracts()
    {
        return $this->belongsToMany(Contract::class, 'contract_sites', 'site_id', 'contract_id')
            ->withPivot('workers_required')
            ->withTimestamps();
    }

    /**
     * Get daily assignments for this site.
     */
    public function dailyAssignments()
    {
        return $this->hasMany(DailyAssignment::class, 'site_id');
    }
}
