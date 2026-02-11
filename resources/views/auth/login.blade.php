<x-guest-layout>
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <!-- Company Name -->
            <h4 class="text-center mb-4 fw-bold text-dark">
                {{ $globalCompanyName ?? config('app.name', 'HRMS') }}
            </h4>

            <!-- Session Status -->
            @if (session('status'))
                <div class="alert alert-success small py-2 mb-3">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label small text-muted">Email</label>
                    <input type="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required 
                           autofocus>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="form-label small text-muted">Password</label>
                    <input type="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           id="password" 
                           name="password" 
                           required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        Login
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
