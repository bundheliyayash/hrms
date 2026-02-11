<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientSite;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index()
    {
        $sites = ClientSite::with('client')->latest()->paginate(10);
        return view('admin.sites.index', compact('sites'));
    }

    public function create()
    {
        $clients = Client::where('is_active', true)->get()->filter(fn($c) => $c->isServiceActive());
        return view('admin.sites.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'site_name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_meters' => 'required|integer|min:1',
        ]);

        ClientSite::create($validated);

        return redirect()->route('admin.sites.index')->with('success', 'Site created successfully.');
    }

    public function edit(ClientSite $site)
    {
        $clients = Client::where('is_active', true)->get()->filter(fn($c) => $c->isServiceActive());
        return view('admin.sites.edit', compact('site', 'clients'));
    }

    public function update(Request $request, ClientSite $site)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'site_name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_meters' => 'required|integer|min:1',
        ]);

        $site->update($validated);

        return redirect()->route('admin.sites.index')->with('success', 'Site updated successfully.');
    }

    public function toggleStatus(ClientSite $site)
    {
        $site->update(['is_active' => !$site->is_active]);
        $status = $site->is_active ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', "Site {$status} successfully.");
    }

    public function destroy(ClientSite $site)
    {
        $site->delete(); // Soft delete
        return redirect()->route('admin.sites.index')->with('success', 'Site archived successfully.');
    }
}
