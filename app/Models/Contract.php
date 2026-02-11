<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contract extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'client_id',
        'contract_number',
        'contract_type',
        'start_date',
        'end_date',
        'renewal_date',
        'billing_type',
        'rate_per_day',
        'minimum_workers_required',
        'actual_workers_assigned',
        'payment_terms',
        'status',
        'auto_renew',
        'terms_and_conditions',
        'ot_enabled',
        'ot_multiplier',
        'calculate_ot_after_minutes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'renewal_date' => 'date',
        'rate_per_day' => 'decimal:2',
        'auto_renew' => 'boolean',
    ];

    // Relationships
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(ClientSite::class, 'contract_sites', 'contract_id', 'site_id')
            ->withPivot('workers_required')
            ->withTimestamps();
    }

    public function dailyAssignments(): HasMany
    {
        return $this->hasMany(DailyAssignment::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(ClientInvoice::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiring($query, $days = 30)
    {
        return $query->where('status', 'active')
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [now(), now()->addDays($days)]);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('contract_type', $type);
    }

    // Business Logic Methods
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = Carbon::now();

        if ($this->start_date && $now->lt(Carbon::parse($this->start_date))) {
            return false;
        }

        if ($this->end_date && $now->gt(Carbon::parse($this->end_date))) {
            return false;
        }

        return true;
    }

    public function isExpired(): bool
    {
        if (!$this->end_date) {
            return false; // No end date means indefinite
        }

        return Carbon::now()->gt(Carbon::parse($this->end_date));
    }

    public function daysUntilExpiry(): ?int
    {
        if (!$this->end_date) {
            return null;
        }

        $days = Carbon::now()->diffInDays(Carbon::parse($this->end_date), false);
        return $days >= 0 ? (int)$days : 0;
    }

    public function updateWorkerCount(): void
    {
        $count = DailyAssignment::where('contract_id', $this->id)
            ->whereDate('assigned_date', now())
            ->whereNotIn('status', ['cancelled'])
            ->distinct('user_id')
            ->count('user_id');

        $this->update(['actual_workers_assigned' => $count]);
    }

    public function canBeDeleted(): bool
    {
        // Cannot delete if invoices exist
        return $this->invoices()->count() === 0;
    }

    // Auto number generation
    public static function generateContractNumber(): string
    {
        $year = now()->year;
        $lastContract = self::where('contract_number', 'like', "CNT-{$year}-%")
            ->orderBy('contract_number', 'desc')
            ->first();

        if ($lastContract) {
            $lastNumber = (int)substr($lastContract->contract_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('CNT-%d-%04d', $year, $newNumber);
    }
}
