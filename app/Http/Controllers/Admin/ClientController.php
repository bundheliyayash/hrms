<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::latest()->paginate(10);
        return view('admin.clients.index', compact('clients'));
    }

    public function create()
    {
        return view('admin.clients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'service_start_date' => 'nullable|date',
            'service_end_date' => 'nullable|date|after_or_equal:service_start_date',
        ]);

        Client::create($validated);

        return redirect()->route('admin.clients.index')->with('success', 'Client created successfully.');
    }

    public function edit(Client $client)
    {
        return view('admin.clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
         $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'service_start_date' => 'nullable|date',
            'service_end_date' => 'nullable|date|after_or_equal:service_start_date',
        ]);

        $client->update($validated);

        return redirect()->route('admin.clients.index')->with('success', 'Client updated successfully.');
    }

    public function toggleStatus(Client $client)
    {
        $client->update(['is_active' => !$client->is_active]);
        $status = $client->is_active ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', "Client {$status} successfully.");
    }

    public function destroy(Client $client)
    {
        $client->delete(); // This is now a soft delete
        return redirect()->route('admin.clients.index')->with('success', 'Client archived successfully.');
    }
}
