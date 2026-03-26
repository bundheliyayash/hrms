<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\InvoiceSentToClient;
use App\Models\ClientInvoice;
use App\Models\Contract;
use App\Models\Client;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class ClientInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ClientInvoice::with(['client', 'contract']);

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_range')) {
            // Assuming date_range format "YYYY-MM-DD - YYYY-MM-DD"
            $dates = explode(' - ', $request->date_range);
            if (count($dates) === 2) {
                $query->whereBetween('invoice_date', [$dates[0], $dates[1]]);
            }
        }

        $invoices = $query->latest('invoice_date')->paginate(15);
        $clients = Client::orderBy('name')->get();

        return view('admin.invoices.index', compact('invoices', 'clients'));
    }

    /**
     * Show the form for generating a new invoice.
     */
    public function generateView()
    {
        $clients = Client::whereHas('contracts', function($q) {
            $q->active();
        })->orderBy('name')->get();

        return view('admin.invoices.generate', compact('clients'));
    }

    /**
     * Generate a new invoice based on attendance.
     */
    /**
     * Generate a new invoice based on attendance.
     */
    public function generate(Request $request, \App\Services\BillingService $billingService)
    {
        $validated = $request->validate([
            'contract_id' => 'required|exists:contracts,id',
            'billing_period_start' => 'required|date',
            'billing_period_end' => 'required|date|after_or_equal:billing_period_start',
            'due_date' => 'required|date|after_or_equal:billing_period_end',
        ]);

        $contract = Contract::with('client', 'sites')->findOrFail($validated['contract_id']);

        // Check if invoice already exists for this period
        $exists = ClientInvoice::where('contract_id', $contract->id)
            ->where('billing_period_start', $validated['billing_period_start'])
            ->where('billing_period_end', $validated['billing_period_end'])
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($exists) {
            return back()->with('error', 'Invoice already exists for this billing period.');
        }

        DB::beginTransaction();
        try {
            $invoice = $billingService->generateInvoice(
                $contract,
                $validated['billing_period_start'],
                $validated['billing_period_end'],
                $validated['due_date'],
                auth()->id()
            );

            DB::commit();

            return redirect()->route('admin.invoices.show', $invoice)
                ->with('success', 'Invoice generated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Invoice generation failed: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ClientInvoice $invoice)
    {
        $invoice->load(['client', 'contract', 'lineItems.worker', 'lineItems.site']);
        return view('admin.invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ClientInvoice $invoice)
    {
        if (!$invoice->canBeEdited()) {
            return back()->with('error', 'Cannot edit invoices that are not in draft status.');
        }

        return view('admin.invoices.edit', compact('invoice'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ClientInvoice $invoice)
    {
        if (!$invoice->canBeEdited()) {
            return back()->with('error', 'Cannot update invoices that are not in draft status.');
        }

        $validated = $request->validate([
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'tax_percentage' => 'required|numeric|min:0|max:100',
            'discount_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $invoice->update($validated);
        $invoice->calculateTotals();

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Invoice updated successfully.');
    }

    /**
     * Download PDF.
     */
    public function downloadPDF(ClientInvoice $invoice)
    {
        $invoice->load(['client', 'contract', 'lineItems']);
        
        $pdf = Pdf::loadView('admin.invoices.pdf', compact('invoice'));
        return $pdf->download("{$invoice->invoice_number}.pdf");
    }

    /**
     * Mark invoice as sent.
     */
    public function sendToClient(ClientInvoice $invoice)
    {
        if (!$invoice->isDraft()) {
            return back()->with('error', 'Only draft invoices can be sent.');
        }

        $invoice->markAsSent();
        $invoice->load('client');

        // Send invoice email to client
        $clientEmail = $invoice->client->email ?? null;
        if ($clientEmail) {
            Mail::to($clientEmail)->send(new InvoiceSentToClient($invoice));
        }

        return back()->with('success', 'Invoice marked as sent' . ($clientEmail ? ' and emailed to client.' : '.'));
    }

    /**
     * Mark invoice as paid.
     */
    public function markPaid(Request $request, ClientInvoice $invoice)
    {
        if ($invoice->isPaid()) {
            return back()->with('error', 'Invoice is already paid.');
        }

        $validated = $request->validate([
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'payment_reference' => 'nullable|string',
        ]);

        $invoice->markAsPaid(
            $validated['payment_date'],
            $validated['payment_method'],
            $validated['payment_reference']
        );

        return back()->with('success', 'Payment recorded successfully.');
    }

    /**
     * Display overdue invoices.
     */
    public function overdue()
    {
        $invoices = ClientInvoice::overdue()->with('client')->paginate(15);
        return view('admin.invoices.overdue', compact('invoices'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClientInvoice $invoice)
    {
        if (!$invoice->canBeDeleted()) {
            return back()->with('error', 'Only draft invoices can be deleted.');
        }

        $invoice->delete();
        return redirect()->route('admin.invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }
}
