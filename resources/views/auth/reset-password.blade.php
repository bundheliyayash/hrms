<x-guest-layout>
    <div class="text-center mb-4">
        <h1 class="h4 fw-bold text-white mb-2">RESET PASSWORD</h1>
        <p class="text-info small fw-medium mb-0">Enter your new password</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label small fw-medium text-secondary">Email</label>
            <input id="email" 
                class="form-control bg-dark bg-opacity-50 border-secondary text-white" 
                type="email" 
                name="email" 
                value="{{ old('email', $request->email) }}" 
                required 
                autofocus 
                autocomplete="username"
                style="padding: 0.75rem 1rem; border-radius: 0.5rem; border-color: rgba(71, 85, 105, 0.5);" />
            @error('email')
                <div class="small mt-2 text-danger fw-medium">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label small fw-medium text-secondary">Password</label>
            <input id="password" 
                class="form-control bg-dark bg-opacity-50 border-secondary text-white" 
                type="password" 
                name="password" 
                required 
                autocomplete="new-password"
                style="padding: 0.75rem 1rem; border-radius: 0.5rem; border-color: rgba(71, 85, 105, 0.5);" />
            @error('password')
                <div class="small mt-2 text-danger fw-medium">{{ $message }}</div>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <label for="password_confirmation" class="form-label small fw-medium text-secondary">Confirm Password</label>
            <input id="password_confirmation" 
                class="form-control bg-dark bg-opacity-50 border-secondary text-white"
                type="password"
                name="password_confirmation" 
                required 
                autocomplete="new-password"
                style="padding: 0.75rem 1rem; border-radius: 0.5rem; border-color: rgba(71, 85, 105, 0.5);" />
            @error('password_confirmation')
                <div class="small mt-2 text-danger fw-medium">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" 
                class="btn px-4 py-2 fw-semibold text-white"
                style="background: linear-gradient(to right, #06b6d4, #3b82f6); border: none; border-radius: 0.5rem;">
                Reset Password
            </button>
        </div>
    </form>
</x-guest-layout>
