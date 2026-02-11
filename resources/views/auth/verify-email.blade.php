<x-guest-layout>
    <div class="text-center mb-4">
        <h1 class="h4 fw-bold text-white mb-2">VERIFY EMAIL</h1>
        <p class="text-info small fw-medium mb-0">Check your inbox</p>
    </div>

    <div class="mb-4 small text-secondary">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success border-0 small fw-semibold mb-4 py-3" style="background-color: rgba(16, 185, 129, 0.1); color: #34d399; border-radius: 0.75rem;">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="d-flex align-items-center justify-content-between mt-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" 
                class="btn px-4 py-2 fw-semibold text-white"
                style="background: linear-gradient(to right, #06b6d4, #3b82f6); border: none; border-radius: 0.5rem;">
                Resend Verification Email
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-link small text-secondary text-decoration-none">
                Log Out
            </button>
        </form>
    </div>
</x-guest-layout>
