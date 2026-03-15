@php
    // Fetch client data for sidebar navigation
    $clientModel = \App\Models\Client::where('user_id', Auth::id())->first();
    $clientName = $clientModel ? $clientModel->name : 'Client Portal';
    $clientSites = $clientModel ? $clientModel->sites()->where('is_active', true)->get() : collect();
@endphp

<x-client-layout :clientName="$clientName" :clientSites="$clientSites">
    {{ $slot }}
</x-client-layout>
