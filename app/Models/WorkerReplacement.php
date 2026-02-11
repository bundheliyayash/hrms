<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerReplacement extends Model
{
    protected $fillable = [
        'original_assignment_id',
        'original_worker_id',
        'replacement_worker_id',
        'site_id',
        'replacement_date',
        'shift_id',
        'reason',
        'reason_details',
        'requested_by',
        'approved_by',
        'status',
    ];

    protected $casts = [
        'replacement_date' => 'date',
    ];

    // Relationships
    public function originalAssignment(): BelongsTo
    {
        return $this->belongsTo(DailyAssignment::class, 'original_assignment_id');
    }

    public function originalWorker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'original_worker_id');
    }

    public function replacementWorker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replacement_worker_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(ClientSite::class, 'site_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('replacement_date', $date);
    }

    public function scopeForWorker($query, $workerId)
    {
        return $query->where(function ($q) use ($workerId) {
            $q->where('original_worker_id', $workerId)
              ->orWhere('replacement_worker_id', $workerId);
        });
    }

    // Business Logic Methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function canBeApproved(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        // Check if replacement worker is available
        return !DailyAssignment::hasConflict(
            $this->replacement_worker_id,
            $this->replacement_date,
            $this->original_assignment_id
        );
    }

    public function approve($approvedById): bool
    {
        if (!$this->canBeApproved()) {
            return false;
        }

        \DB::beginTransaction();
        try {
            // Update replacement status
            $this->update([
                'status' => 'approved',
                'approved_by' => $approvedById,
            ]);

            // Create new assignment for replacement worker
            DailyAssignment::create([
                'user_id' => $this->replacement_worker_id,
                'site_id' => $this->site_id,
                'contract_id' => $this->originalAssignment->contract_id,
                'assigned_date' => $this->replacement_date,
                'shift_id' => $this->shift_id,
                'assignment_type' => 'replacement',
                'assigned_by' => $approvedById,
                'status' => 'assigned',
                'notes' => "Replacement for {$this->originalWorker->name}",
            ]);

            // Cancel original assignment
            $this->originalAssignment->update([
                'status' => 'cancelled',
                'notes' => "Replaced by {$this->replacementWorker->name}. Reason: {$this->reason}",
            ]);

            \DB::commit();
            return true;
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Worker replacement approval failed: ' . $e->getMessage());
            return false;
        }
    }

    public function reject($rejectedById): void
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $rejectedById, // Using same field for rejected_by
        ]);
    }
}
