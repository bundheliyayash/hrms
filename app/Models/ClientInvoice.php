<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class ClientInvoice extends Model
{
    protected $fillable = [
        'client_id',
        'contract_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'billing_period_start',
        'billing_period_end',
        'total_worker_days',
        'rate_per_day',
        'gross_amount',
        'tax_percentage',
        'tax_amount',
        'discount_amount',
        'net_amount',
        'status',
        'payment_date',
        'payment_method',
        'payment_reference',
        'notes',
        'generated_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'billing_period_start' => 'date',
        'billing_period_end' => 'date',
        'payment_date' => 'date',
        'total_worker_days' => 'decimal:2',
        'rate_per_day' => 'decimal:2',
        'gross_amount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    // Relationships
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(InvoiceLineItem::class, 'invoice_id');
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'sent')
            ->where('due_date', '<', now());
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeForContract($query, $contractId)
    {
        return $query->where('contract_id', $contractId);
    }

    public function scopeForPeriod($query, $start, $end)
    {
        return $query->whereBetween('billing_period_start', [$start, $end]);
    }

    // Business Logic Methods
    public function isOverdue(): bool
    {
        if ($this->status === 'paid') {
            return false;
        }

        return Carbon::parse($this->due_date)->isPast();
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft']);
    }

    public function canBeDeleted(): bool
    {
        return $this->status === 'draft';
    }

    public function markAsSent(): void
    {
        $this->update(['status' => 'sent']);
    }

    public function markAsPaid($paymentDate, $paymentMethod, $paymentReference = null): void
    {
        $this->update([
            'status' => 'paid',
            'payment_date' => $paymentDate,
            'payment_method' => $paymentMethod,
            'payment_reference' => $paymentReference,
        ]);
    }

    public function markAsOverdue(): void
    {
        if ($this->status === 'sent' && $this->isOverdue()) {
            $this->update(['status' => 'overdue']);
        }
    }

    public function calculateTotals(): void
    {
        $grossAmount = $this->total_worker_days * $this->rate_per_day;
        $taxAmount = $grossAmount * ($this->tax_percentage / 100);
        $netAmount = $grossAmount + $taxAmount - $this->discount_amount;

        $this->update([
            'gross_amount' => $grossAmount,
            'tax_amount' => $taxAmount,
            'net_amount' => $netAmount,
        ]);
    }

    public function recalculateFromLineItems(): void
    {
        $totalWorkerDays = $this->lineItems()->sum('hours_worked') / 8; // Convert hours to days
        $grossAmount = $this->lineItems()->sum('amount');

        $this->update([
            'total_worker_days' => $totalWorkerDays,
            'gross_amount' => $grossAmount,
        ]);

        $this->calculateTotals();
    }

    // Auto number generation
    public static function generateInvoiceNumber(): string
    {
        $year = now()->year;
        $month = now()->format('m');
        
        $lastInvoice = self::where('invoice_number', 'like', "INV-{$year}{$month}-%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int)substr($lastInvoice->invoice_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('INV-%s%s-%04d', $year, $month, $newNumber);
    }

    public function daysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        return Carbon::parse($this->due_date)->diffInDays(now());
    }
}
