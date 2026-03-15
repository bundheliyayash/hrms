<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::with('user')->latest()->paginate(10);
        return view('admin.clients.index', compact('clients'));
    }

    public function create()
    {
        return view('admin.clients.create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'service_start_date' => 'nullable|date',
                'service_end_date' => 'nullable|date|after_or_equal:service_start_date',
            ]);

            $client = Client::create($validated);

            // Handle client portal account creation
            if ($request->filled('portal_email')) {
                $request->validate([
                    'portal_email' => 'required|email|max:255|unique:users,email',
                    'portal_password' => 'required|min:6',
                ]);

                $user = User::create([
                    'name' => $client->name,
                    'email' => $request->portal_email,
                    'password' => Hash::make($request->portal_password),
                    'role' => 'client',
                    'status' => 'active',
                ]);

                $client->update(['user_id' => $user->id]);

                return redirect()->route('admin.clients.index')->with('success', 'New Client "' . $client->name . '" has been added with portal access.');
            }

            return redirect()->route('admin.clients.index')->with('success', 'New Client "' . $client->name . '" has been added successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to create client: ' . $e->getMessage());
        }
    }

    public function edit(Client $client)
    {
        $client->load('user');
        return view('admin.clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'service_start_date' => 'nullable|date',
                'service_end_date' => 'nullable|date|after_or_equal:service_start_date',
            ]);

            $client->update($validated);

            // Handle client portal account creation/update
            if ($request->filled('portal_email')) {
                $portalRules = [
                    'portal_email' => 'required|email|max:255',
                ];

                if (!$client->user_id || $request->filled('portal_password')) {
                    $portalRules['portal_password'] = $client->user_id ? 'nullable|min:6' : 'required|min:6';
                }

                $portalRules['portal_email'] .= '|unique:users,email' . ($client->user_id ? ',' . $client->user_id : '');
                $request->validate($portalRules);

                if ($client->user_id && $client->user) {
                    $userData = ['name' => $request->name, 'email' => $request->portal_email];
                    if ($request->filled('portal_password')) {
                        $userData['password'] = Hash::make($request->portal_password);
                    }
                    $client->user->update($userData);
                } else {
                    $user = User::create([
                        'name' => $request->name,
                        'email' => $request->portal_email,
                        'password' => Hash::make($request->portal_password),
                        'role' => 'client',
                        'status' => 'active',
                    ]);
                    $client->user_id = $user->id;
                    $client->save();
                }

                return redirect()->route('admin.clients.index')->with('success', 'Profile & Portal: Client details and login access updated for ' . $client->name);
            }

            return redirect()->route('admin.clients.index')->with('success', 'Profile Updated: Details for ' . $client->name . ' updated.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Update failed: ' . $e->getMessage());
        }
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

