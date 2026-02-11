<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Client;
use App\Models\ClientSite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ContractController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Contract::with(['client', 'sites']);

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('contract_type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('contract_number', 'like', "%{$search}%")
                  ->orWhereHas('client', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $contracts = $query->latest()->paginate(15);
        $clients = Client::orderBy('name')->get();

        return view('admin.contracts.index', compact('contracts', 'clients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clients = Client::where('is_active', true)->orderBy('name')->get();
        return view('admin.contracts.create', compact('clients'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'contract_type' => 'required|in:permanent,temporary,one_day',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'billing_type' => 'required|in:per_day,per_month,per_service',
            'rate_per_day' => 'required|numeric|min:0',
            'payment_terms' => 'required|in:weekly,monthly,on_completion',
            'minimum_workers_required' => 'required|integer|min:1',
            'auto_renew' => 'boolean',
            'terms_and_conditions' => 'nullable|string',
            'ot_enabled' => 'boolean',
            'ot_multiplier' => 'nullable|numeric|min:1',
            'calculate_ot_after_minutes' => 'nullable|integer|min:0',
            'site_ids' => 'required|array',
            'site_ids.*' => 'exists:client_sites,id',
        ]);

        DB::beginTransaction();
        try {
            // Auto-generate contract number
            $contractNumber = Contract::generateContractNumber();

            // Handle one-day contract logic
            if ($validated['contract_type'] === 'one_day') {
                $validated['end_date'] = $validated['start_date'];
            }

            $contract = Contract::create([
                ...$validated,
                'contract_number' => $contractNumber,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            // Sync sites with default workers required
            $sites = [];
            foreach ($request->input('site_ids', []) as $siteId) {
                $sites[$siteId] = ['workers_required' => $request->input("workers_count_{$siteId}", 1)];
            }
            $contract->sites()->sync($sites);

            DB::commit();

            return redirect()->route('admin.contracts.show', $contract)
                ->with('success', "Contract {$contract->contract_number} created successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create contract: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Contract $contract)
    {
        $contract->load(['client', 'sites', 'dailyAssignments' => function($q) {
            $q->latest()->limit(5);
        }, 'invoices' => function($q) {
            $q->latest()->limit(5);
        }]);
        
        return view('admin.contracts.show', compact('contract'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contract $contract)
    {
        if ($contract->status === 'completed' || $contract->status === 'cancelled') {
            return back()->with('error', 'Cannot edit completed or cancelled contracts.');
        }

        $clients = Client::where('is_active', true)->orderBy('name')->get();
        $selectedSites = $contract->sites->pluck('pivot.workers_required', 'id')->toArray();
        
        return view('admin.contracts.edit', compact('contract', 'clients', 'selectedSites'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contract $contract)
    {
        if ($contract->status === 'completed' || $contract->status === 'cancelled') {
            return back()->with('error', 'Cannot update completed or cancelled contracts.');
        }

        $validated = $request->validate([
            'contract_type' => 'required|in:permanent,temporary,one_day',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'billing_type' => 'required|in:per_day,per_month,per_service',
            'rate_per_day' => 'required|numeric|min:0',
            'payment_terms' => 'required|in:weekly,monthly,on_completion',
            'minimum_workers_required' => 'required|integer|min:1',
            'auto_renew' => 'boolean',
            'terms_and_conditions' => 'nullable|string',
            'ot_enabled' => 'boolean',
            'ot_multiplier' => 'nullable|numeric|min:1',
            'calculate_ot_after_minutes' => 'nullable|integer|min:0',
            'site_ids' => 'required|array',
            'site_ids.*' => 'exists:client_sites,id',
        ]);

        DB::beginTransaction();
        try {
            if ($validated['contract_type'] === 'one_day') {
                $validated['end_date'] = $validated['start_date'];
            }

            $contract->update($validated);

            // Sync sites
            $sites = [];
            foreach ($request->input('site_ids', []) as $siteId) {
                $sites[$siteId] = ['workers_required' => $request->input("workers_count_{$siteId}", 1)];
            }
            $contract->sites()->sync($sites);

            DB::commit();

            return redirect()->route('admin.contracts.show', $contract)
                ->with('success', 'Contract updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update contract: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contract $contract)
    {
        if (!$contract->canBeDeleted()) {
            return back()->with('error', 'Cannot delete contract because it has linked invoices.');
        }

        $contract->delete();

        return redirect()->route('admin.contracts.index')
            ->with('success', 'Contract deleted successfully.');
    }

    /**
     * Display expiring contracts.
     */
    public function expiring()
    {
        $contracts = Contract::expiring(30)->with('client')->paginate(15);
        return view('admin.contracts.expiring', compact('contracts'));
    }

    /**
     * Toggle contract status.
     */
    public function toggleStatus(Request $request, Contract $contract)
    {
        $request->validate([
            'status' => 'required|in:active,completed,cancelled,draft'
        ]);

        $newStatus = $request->status;

        // Validation for activating
        if ($newStatus === 'active') {
            if (!$contract->start_date) {
                return back()->with('error', 'Cannot activate contract without start date.');
            }
            
            // Check if client is active
            if (!$contract->client->is_active) {
                return back()->with('error', 'Cannot activate contract for inactive client.');
            }
        }

        $contract->update(['status' => $newStatus]);

        return back()->with('success', "Contract status updated to {$newStatus}.");
    }
}
