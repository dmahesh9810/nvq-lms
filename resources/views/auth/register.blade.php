<x-guest-layout>
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4 p-sm-5">
            <h4 class="text-center fw-bold mb-4">Create an Account</h4>

            <form method="POST" action="{{ route('register') }}">
                @csrf
                
                <!-- Name -->
                <div class="mb-3">
                    <label for="name" class="form-label fw-medium text-dark">Full Name</label>
                    <input type="text" name="name" id="name" class="form-control form-control-lg bg-light border-0 py-3 @error('name') is-invalid @enderror" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="John Doe">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Email Address -->
                <div class="mb-3">
                    <label for="email" class="form-label fw-medium text-dark">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control form-control-lg bg-light border-0 py-3 @error('email') is-invalid @enderror" value="{{ old('email') }}" required autocomplete="username" placeholder="name@example.com">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label for="password" class="form-label fw-medium text-dark">Password</label>
                    <input type="password" name="password" id="password" class="form-control form-control-lg bg-light border-0 py-3 @error('password') is-invalid @enderror" required autocomplete="new-password" placeholder="••••••••">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="mb-4">
                    <label for="password_confirmation" class="form-label fw-medium text-dark">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control form-control-lg bg-light border-0 py-3 @error('password_confirmation') is-invalid @enderror" required autocomplete="new-password" placeholder="••••••••">
                    @error('password_confirmation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 mb-4 fw-bold rounded-pill shadow-sm">Register</button>

                <p class="text-center text-muted small mb-0">Already registered? <a href="{{ route('login') }}" class="text-primary text-decoration-none fw-medium">Log in</a></p>
            </form>
        </div>
    </div>
</x-guest-layout>
