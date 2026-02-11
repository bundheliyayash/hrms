<x-guest-layout>
    <div class="text-center mb-4">
        <h1 class="h4 fw-bold text-white mb-2">FORGOT PASSWORD</h1>
        <p class="text-info small fw-medium mb-0">We'll send you a reset link</p>
    </div>

    <div class="mb-4 small text-secondary">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div class="alert alert-success border-0 small fw-semibold mb-4 py-3" style="background-color: rgba(16, 185, 129, 0.1); color: #34d399; border-radius: 0.75rem;">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-4">
            <label for="email" class="form-label small fw-medium text-secondary">Email</label>
            <input id="email" 
                class="form-control bg-dark bg-opacity-50 border-secondary text-white" 
                type="email" 
                name="email" 
                value="{{ old('email') }}" 
                required 
                autofocus
                style="padding: 0.75rem 1rem; border-radius: 0.5rem; border-color: rgba(71, 85, 105, 0.5);" />
            @error('email')
                <div class="small mt-2 text-danger fw-medium">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex align-items-center justify-content-between">
            <a class="small text-secondary text-decoration-none" href="{{ route('login') }}">
                <i class="bi bi-arrow-left me-1"></i> Back to login
            </a>

            <button type="submit" 
                class="btn px-4 py-2 fw-semibold text-white"
                style="background: linear-gradient(to right, #06b6d4, #3b82f6); border: none; border-radius: 0.5rem;">
                Send Reset Link
            </button>
        </div>
    </form>
</x-guest-layout>
