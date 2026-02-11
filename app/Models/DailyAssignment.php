<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class DailyAssignment extends Model
{
    protected $fillable = [
        'user_id',
        'site_id',
        'contract_id',
        'assigned_date',
        'shift_id',
        'assignment_type',
        'assigned_by',
        'status',
        'notes',
    ];

    protected $casts = [
        'assigned_date' => 'date',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(ClientSite::class, 'site_id');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function attendance(): HasOne
    {
        return $this->hasOne(Attendance::class, 'user_id', 'user_id')
            ->whereColumn('attendances.date', 'daily_assignments.assigned_date')
            ->whereColumn('attendances.site_id', 'daily_assignments.site_id');
    }

    public function replacement(): HasOne
    {
        return $this->hasOne(WorkerReplacement::class, 'original_assignment_id');
    }

    // Scopes
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('assigned_date', $date);
    }

    public function scopeForSite($query, $siteId)
    {
        return $query->where('site_id', $siteId);
    }

    public function scopeForEmployee($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['assigned', 'confirmed']);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('assigned_date', '>=', now())->orderBy('assigned_date');
    }

    public function scopePast($query)
    {
        return $query->where('assigned_date', '<', now())->orderBy('assigned_date', 'desc');
    }

    // Business Logic Methods
    public function canBeCancelled(): bool
    {
        // Cannot cancel if already completed or if attendance exists
        if ($this->status === 'completed') {
            return false;
        }

        if ($this->attendance()->exists()) {
            return false;
        }

        return true;
    }

    public function markConfirmed(): void
    {
        if ($this->status === 'assigned') {
            $this->update(['status' => 'confirmed']);
        }
    }

    public function markCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }

    public function isToday(): bool
    {
        return Carbon::parse($this->assigned_date)->isToday();
    }

    public function isPast(): bool
    {
        return Carbon::parse($this->assigned_date)->isPast();
    }

    public function isFuture(): bool
    {
        return Carbon::parse($this->assigned_date)->isFuture();
    }

    // Validation
    public static function hasConflict($userId, $date, $excludeId = null): bool
    {
        $query = self::where('user_id', $userId)
            ->whereDate('assigned_date', $date)
            ->whereNotIn('status', ['cancelled']);

if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public static function getAvailableEmployees($date, $siteId = null)
    {
        $assignedUserIds = self::whereDate('assigned_date', $date)
            ->whereNotIn('status', ['cancelled'])
            ->pluck('user_id');

        return User::where('role', 'employee')
            ->where('status', 'active')
            ->whereNotIn('id', $assignedUserIds)
            ->with('employeeDetail')
            ->get();
    }
}
