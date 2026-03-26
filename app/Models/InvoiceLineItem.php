<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceLineItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'attendance_id',
        'daily_assignment_id',
        'site_id',
        'worker_id',
        'worker_name',
        'service_date',
        'hours_worked',
        'rate',
        'amount',
        'description',
    ];

    protected $casts = [
        'service_date' => 'date',
        'hours_worked' => 'decimal:2',
        'rate' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public $timestamps = true;
    const UPDATED_AT = null; // Only track created_at

    // Relationships
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(ClientInvoice::class, 'invoice_id');
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function dailyAssignment(): BelongsTo
    {
        return $this->belongsTo(DailyAssignment::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(ClientSite::class, 'site_id');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    // Scopes
    public function scopeForSite($query, $siteId)
    {
        return $query->where('site_id', $siteId);
    }

    public function scopeForWorker($query, $workerId)
    {
        return $query->where('worker_id', $workerId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('service_date', $date);
    }

    // Business Logic Methods
    public function calculateAmount(): void
    {
        $workingHours = (float) Setting::get('working_hours_per_day', 8) ?: 8;
        $this->amount = $this->hours_worked * $this->rate / $workingHours;
        $this->save();
    }
}
