<x-guest-layout>
    <div class="text-center mb-4">
        <h1 class="h4 fw-bold text-white mb-2">REGISTER</h1>
        <p class="text-info small fw-medium mb-0">Create your account</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div class="mb-3">
            <label for="name" class="form-label small fw-medium text-secondary">Name</label>
            <input id="name" 
                class="form-control bg-dark bg-opacity-50 border-secondary text-white" 
                type="text" 
                name="name" 
                value="{{ old('name') }}" 
                required 
                autofocus 
                autocomplete="name"
                style="padding: 0.75rem 1rem; border-radius: 0.5rem; border-color: rgba(71, 85, 105, 0.5);" />
            @error('name')
                <div class="small mt-2 text-danger fw-medium">{{ $message }}</div>
            @enderror
        </div>

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label small fw-medium text-secondary">Email</label>
            <input id="email" 
                class="form-control bg-dark bg-opacity-50 border-secondary text-white" 
                type="email" 
                name="email" 
                value="{{ old('email') }}" 
                required 
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

        <div class="d-flex align-items-center justify-content-between">
            <a class="small text-secondary text-decoration-none" href="{{ route('login') }}">
                Already registered?
            </a>

            <button type="submit" 
                class="btn px-4 py-2 fw-semibold text-white"
                style="background: linear-gradient(to right, #06b6d4, #3b82f6); border: none; border-radius: 0.5rem;">
                Register
            </button>
        </div>
    </form>
</x-guest-layout>
