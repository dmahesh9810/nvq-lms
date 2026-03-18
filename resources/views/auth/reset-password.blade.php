<x-guest-layout>
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4 p-sm-5">
            <h4 class="text-center fw-bold mb-4">Set New Password</h4>

            <form method="POST" action="{{ route('password.store') }}">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email Address -->
                <div class="mb-3">
                    <label for="email" class="form-label fw-medium text-dark">Email Address</label>
                    <input id="email" type="email" name="email" class="form-control form-control-lg bg-light border-0 py-3 @error('email') is-invalid @enderror" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label for="password" class="form-label fw-medium text-dark">New Password</label>
                    <input id="password" type="password" name="password" class="form-control form-control-lg bg-light border-0 py-3 @error('password') is-invalid @enderror" required autocomplete="new-password" placeholder="••••••••">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="mb-4">
                    <label for="password_confirmation" class="form-label fw-medium text-dark">Confirm New Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="form-control form-control-lg bg-light border-0 py-3 @error('password_confirmation') is-invalid @enderror" required autocomplete="new-password" placeholder="••••••••">
                    @error('password_confirmation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 mb-3 fw-bold rounded-pill shadow-sm">
                    Reset Password
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
