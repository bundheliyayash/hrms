<x-guest-layout>
    <div class="text-center mb-4">
        <h1 class="h4 fw-bold text-white mb-2">CONFIRM PASSWORD</h1>
        <p class="text-info small fw-medium mb-0">Secure area access</p>
    </div>

    <div class="mb-4 small text-secondary">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div class="mb-4">
            <label for="password" class="form-label small fw-medium text-secondary">Password</label>
            <input id="password" 
                class="form-control bg-dark bg-opacity-50 border-secondary text-white"
                type="password"
                name="password"
                required 
                autocomplete="current-password"
                style="padding: 0.75rem 1rem; border-radius: 0.5rem; border-color: rgba(71, 85, 105, 0.5);" />
            @error('password')
                <div class="small mt-2 text-danger fw-medium">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" 
                class="btn px-4 py-2 fw-semibold text-white"
                style="background: linear-gradient(to right, #06b6d4, #3b82f6); border: none; border-radius: 0.5rem;">
                Confirm
            </button>
        </div>
    </form>
</x-guest-layout>
