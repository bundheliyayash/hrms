<?php

namespace App\Services;

use App\Models\ClientInvoice;
use App\Models\Contract;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;

class BillingService
{
    public function generateInvoice(Contract $contract, $startDate, $endDate, $dueDate, $generatedBy)
    {
        $attendances = Attendance::with(['user', 'site'])
            ->whereIn('site_id', $contract->sites->pluck('id'))
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->whereNotNull('clock_out') // Only completed shifts
            ->get();

        if ($attendances->isEmpty()) {
            throw new \Exception('No billable attendance records found for this period.');
        }

        // 2. Create Invoice Header
        $invoice = ClientInvoice::create([
            'client_id' => $contract->client_id,
            'contract_id' => $contract->id,
            'invoice_number' => ClientInvoice::generateInvoiceNumber(),
            'invoice_date' => now(),
            'due_date' => $dueDate,
            'billing_period_start' => $startDate,
            'billing_period_end' => $endDate,
            'rate_per_day' => $contract->rate_per_day,
            'tax_percentage' => 18, // Default tax
            'status' => 'draft',
            'generated_by' => $generatedBy,
        ]);

        // 3. Create Line Items
        $lineItems = [];
        $totalWorkerDays = 0;

        foreach ($attendances as $attendance) {
            // Fetch Assignment Manually to avoid Eager Loading issue with composite keys
            $assignment = \App\Models\DailyAssignment::where('user_id', $attendance->user_id)
                ->whereDate('assigned_date', $attendance->date)
                ->where('site_id', $attendance->site_id)
                ->first();

            // Calculation strategy based on billing type
            $hours = $attendance->duration_minutes / 60;
            $days = match($contract->billing_type) {
                'per_service' => $hours / 8,
                default => 1 // per_day or per_month
            };

            $amount = $days * $contract->rate_per_day;

            $invoice->lineItems()->create([
                'attendance_id' => $attendance->id,
                'daily_assignment_id' => $assignment->id ?? null,
                'site_id' => $attendance->site_id,
                'worker_id' => $attendance->user_id,
                'worker_name' => $attendance->user->name,
                'service_date' => $attendance->date,
                'hours_worked' => $hours,
                'rate' => $contract->rate_per_day,
                'amount' => $amount,
            ]);

            $totalWorkerDays += $days;
        }

        // 4. Update Totals
        $invoice->update(['total_worker_days' => $totalWorkerDays]);
        $invoice->calculateTotals();

        return $invoice;
    }
}
