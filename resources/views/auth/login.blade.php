<x-guest-layout>
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4 p-sm-5">
            <h4 class="text-center fw-bold mb-4">Welcome Back</h4>
            
            <!-- Session Status -->
            @if (session('status'))
                <div class="alert alert-success fs-6 mb-4">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                
                <!-- Email Address -->
                <div class="mb-3">
                    <label for="email" class="form-label fw-medium text-dark">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control form-control-lg bg-light border-0 py-3 @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="name@example.com">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="form-label fw-medium text-dark">Password</label>
                    <input type="password" name="password" id="password" class="form-control form-control-lg bg-light border-0 py-3 @error('password') is-invalid @enderror" required autocomplete="current-password" placeholder="••••••••">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input type="checkbox" name="remember" id="remember_me" class="form-check-input shadow-none">
                        <label class="form-check-label text-muted" for="remember_me">Remember me</label>
                    </div>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-decoration-none text-primary small fw-medium">Forgot password?</a>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 mb-4 fw-bold rounded-pill shadow-sm">Log in</button>

                <p class="text-center text-muted small mb-0">Don't have an account? <a href="{{ route('register') }}" class="text-primary text-decoration-none fw-medium">Register now</a></p>
            </form>
        </div>
    </div>
</x-guest-layout>
