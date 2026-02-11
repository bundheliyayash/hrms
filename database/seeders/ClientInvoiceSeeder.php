<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClientInvoice;
use App\Models\InvoiceLineItem;
use App\Models\Contract;
use App\Models\DailyAssignment;
use Carbon\Carbon;

class ClientInvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $contracts = Contract::with('client', 'sites')->get();

        foreach ($contracts as $contract) {
            // Create one PAID invoice for 2 months ago
            $this->createSampleInvoice($contract, 2, 'paid');
            
            // Create one SENT (Outstanding) invoice for last month
            $this->createSampleInvoice($contract, 1, 'sent');
            
            // Create one OVERDUE invoice if it's the permanent contract
            if ($contract->contract_type === 'permanent') {
                $this->createSampleOverdueInvoice($contract);
            }
        }
    }

    private function createSampleInvoice($contract, $monthsAgo, $status)
    {
        $start = Carbon::now()->subMonths($monthsAgo)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $admin = \App\Models\User::where('role', 'admin')->first();
        $worker = \App\Models\User::where('role', 'employee')->first();
        
        $invoice = ClientInvoice::create([
            'client_id' => $contract->client_id,
            'contract_id' => $contract->id,
            'invoice_number' => 'INV-' . strtoupper(bin2hex(random_bytes(3))),
            'invoice_date' => $end->copy()->addDays(2),
            'billing_period_start' => $start,
            'billing_period_end' => $end,
            'due_date' => $end->copy()->addDays(15),
            'total_worker_days' => 25,
            'rate_per_day' => $contract->rate_per_day,
            'gross_amount' => $contract->rate_per_day * 25,
            'tax_percentage' => 18.00,
            'tax_amount' => ($contract->rate_per_day * 25) * 0.18,
            'discount_amount' => 0,
            'net_amount' => ($contract->rate_per_day * 25) * 1.18,
            'status' => $status,
            'notes' => 'Generated via system seeder.',
            'payment_date' => $status == 'paid' ? $end->copy()->addDays(10) : null,
            'payment_method' => $status == 'paid' ? 'Bank Transfer' : null,
            'payment_reference' => $status == 'paid' ? 'UTR-' . rand(100000, 999999) : null,
            'generated_by' => $admin->id
        ]);

        // Add 5 line items
        for ($i = 0; $i < 5; $i++) {
            InvoiceLineItem::create([
                'invoice_id' => $invoice->id,
                'site_id' => $contract->sites->first()->id,
                'worker_id' => $worker->id,
                'worker_name' => $worker->name,
                'service_date' => $start->copy()->addDays($i * 5),
                'hours_worked' => 8.0,
                'rate' => $contract->rate_per_day,
                'amount' => $contract->rate_per_day,
                'description' => 'Regular cleaning service'
            ]);
        }
    }

    private function createSampleOverdueInvoice($contract)
    {
        $start = Carbon::now()->subMonths(3)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        
        ClientInvoice::create([
            'client_id' => $contract->client_id,
            'contract_id' => $contract->id,
            'invoice_number' => 'INV-OVERDUE-001',
            'invoice_date' => $end->copy()->addDays(1),
            'billing_period_start' => $start,
            'billing_period_end' => $end,
            'due_date' => Carbon::now()->subDays(10), // Pass due date
            'total_worker_days' => 20,
            'rate_per_day' => $contract->rate_per_day,
            'gross_amount' => $contract->rate_per_day * 20,
            'tax_percentage' => 18.00,
            'tax_amount' => ($contract->rate_per_day * 20) * 0.18,
            'discount_amount' => 0,
            'net_amount' => ($contract->rate_per_day * 20) * 1.18,
            'status' => 'sent', // Will be detected as overdue by model scope
            'notes' => 'URGENT: PAYMENT OVERDUE',
        ]);
    }
}
