<x-guest-layout>
    <div class="d-flex flex-column align-items-center w-100 py-4 py-md-5">
        
        {{-- Brand Header / Banner --}}
        <div class="text-center mb-4">
            <h1 class="fw-bolder text-dark mb-1">Certificate Verification</h1>
            <p class="text-muted">IqBrave NVQ & TVEC Management System</p>
        </div>

        {{-- Main Card --}}
        <div class="card shadow-lg border-0 rounded-4 w-100" style="max-width: 600px;">
            <div class="card-body p-4 p-md-5 text-center">
                
                <div class="d-inline-flex justify-content-center align-items-center rounded-circle bg-primary bg-opacity-10 mb-4 border border-primary-subtle shadow-sm" style="width: 80px; height: 80px;">
                    <i class="bi bi-shield-lock text-primary" style="font-size: 2.5rem;"></i>
                </div>

                <h2 class="h3 fw-bold text-dark mb-3">Verify Credential</h2>
                <p class="text-muted fs-6 mb-5 px-md-4">
                    Enter the unique tracking ID found at the bottom of the certificate to instantly verify its authenticity and accreditation status.
                </p>
                
                <form method="POST" action="{{ route('verify.submit') }}" class="w-100 px-md-3">
                    @csrf
                    
                    <div class="mb-4 text-start">
                        <label for="certificate_number" class="form-label fw-bold text-dark mb-2" style="font-size: 0.9rem; letter-spacing: 0.5px;">TRACKING ID / CERTIFICATE NUMBER</label>
                        <div class="input-group input-group-lg shadow-sm">
                            <span class="input-group-text bg-light border-end-0 text-muted">
                                <i class="bi bi-upc-scan"></i>
                            </span>
                            <input 
                                type="text" 
                                name="certificate_number" 
                                id="certificate_number" 
                                required
                                value="{{ old('certificate_number') }}"
                                placeholder="e.g. IQB-2026-A1B2C3"
                                class="form-control border-start-0 text-dark font-monospace fw-bold py-3 fs-5"
                                style="text-transform: uppercase;"
                                autofocus
                            >
                        </div>
                        @error('certificate_number')
                            <div class="text-danger mt-2 small fw-medium text-start">
                                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mt-5">
                        <button type="submit" class="btn btn-primary btn-lg w-100 py-3 shadow-sm rounded-3 fw-bold fs-5">
                            <i class="bi bi-search me-2"></i> Verify Now
                        </button>
                    </div>
                </form>

            </div>
            
            {{-- Card Footer Trust Element --}}
            <div class="card-footer bg-light border-top p-3 text-center">
                <div class="d-flex justify-content-center align-items-center text-muted fw-medium small">
                    <i class="bi bi-lock-fill me-2"></i>
                    <span>Secure 256-bit Encryption • Direct Database Verification</span>
                </div>
            </div>
        </div>
        
    </div>
</x-guest-layout>
