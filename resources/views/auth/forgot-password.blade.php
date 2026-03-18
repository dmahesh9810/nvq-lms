<x-guest-layout>
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4 p-sm-5">
            <h4 class="text-center fw-bold mb-3">Forgot Password</h4>
            
            <p class="text-muted text-center small mb-4">
                Forgot your password? No problem. Just let us know your email address and we will email you a password reset link.
            </p>

            <!-- Session Status -->
            @if (session('status'))
                <div class="alert alert-success fs-6 mb-4">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <!-- Email Address -->
                <div class="mb-4">
                    <label for="email" class="form-label fw-medium text-dark">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control form-control-lg bg-light border-0 py-3 @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus placeholder="name@example.com">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 mb-3 fw-bold rounded-pill shadow-sm">
                    Email Password Reset Link
                </button>

                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-decoration-none text-muted small fw-medium"><i class="bi bi-arrow-left me-1"></i>Back to login</a>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
