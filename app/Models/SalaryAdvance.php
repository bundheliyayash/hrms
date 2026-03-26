<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryAdvance extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'approved_by',
        'amount',
        'advance_date',
        'reason',
        'status',
        'admin_remark',
        'recovered_amount',
        'recovery_month',
        'payroll_id',
    ];

    protected $casts = [
        'advance_date'   => 'date',
        'recovery_month' => 'date',
        'amount'         => 'float',
        'recovered_amount' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    /** Pending amount still to be recovered */
    public function getPendingAmountAttribute(): float
    {
        return max(0, $this->amount - $this->recovered_amount);
    }

    /** Scopes */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeUnrecovered($query)
    {
        return $query->whereIn('status', ['approved'])->whereColumn('recovered_amount', '<', 'amount');
    }
}
