<x-guest-layout>
    <div class="d-flex flex-column align-items-center w-100">
        {{-- Brand Header / Banner --}}
        <div class="text-center mb-4">
            <h1 class="fw-bolder text-dark mb-1">Certificate Verification</h1>
            <p class="text-muted">IqBrave NVQ & TVEC Management System</p>
        </div>

        {{-- Main Card --}}
        <div class="card shadow-lg border-0 rounded-4 w-100" style="max-width: 700px;">
            <div class="card-body p-4 p-md-5">
                
                @if(!$certificate)
                    {{-- Not Found Status --}}
                    <div class="text-center py-4">
                        <div class="d-inline-flex justify-content-center align-items-center rounded-circle bg-danger bg-opacity-10 mb-4" style="width: 80px; height: 80px;">
                            <i class="bi bi-x-circle text-danger" style="font-size: 2.5rem;"></i>
                        </div>
                        <h2 class="h3 fw-bold text-dark mb-3">Invalid Certificate</h2>
                        <p class="text-muted fs-5 mb-5">
                            The tracking number provided does not match any official records in our system. Ensure the number is typed correctly.
                        </p>
                        <a href="{{ route('verify.form') }}" class="btn btn-primary btn-lg px-5 shadow-sm rounded-3">
                            Verify Another Certificate
                        </a>
                    </div>
                @else
                    
                    {{-- Found Status --}}
                    @if($certificate->status === 'revoked')
                        <div class="text-center border-bottom pb-4 mb-4">
                            <div class="d-inline-flex justify-content-center align-items-center rounded-circle bg-danger bg-opacity-10 mb-3 border border-danger-subtle" style="width: 80px; height: 80px;">
                                <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 2.5rem;"></i>
                            </div>
                            <h2 class="h3 fw-bold text-danger">Revoked Certificate</h2>
                            <p class="text-danger-emphasis mb-0 fs-5">This certificate exists but has been invalidated by administration.</p>
                        </div>
                    @else
                        <div class="text-center border-bottom pb-4 mb-4">
                            <div class="d-inline-flex justify-content-center align-items-center rounded-circle bg-success bg-opacity-10 mb-3 border border-success-subtle shadow-sm" style="width: 90px; height: 90px;">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                            </div>
                            <h2 class="h2 fw-bolder text-success">Authentic Certificate</h2>
                            <p class="text-muted mb-0 fs-5">Valid and officially recognized credential.</p>
                        </div>
                    @endif

                    {{-- Details Grid --}}
                    <div class="bg-light rounded-4 p-4 p-md-5 border mb-5">
                        <div class="row g-4">
                            
                            <div class="col-12">
                                <span class="d-block text-uppercase text-muted fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Recipient Name</span>
                                <h4 class="fw-bold text-dark border-bottom pb-2 mt-1 mb-0">{{ $certificate->user->name }}</h4>
                            </div>

                            <div class="col-12">
                                <span class="d-block text-uppercase text-muted fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Certification Program</span>
                                <span class="d-block fs-5 text-dark mt-1">{{ $certificate->course->title }}</span>
                            </div>

                            <div class="col-md-6">
                                <span class="d-block text-uppercase text-muted fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">NVQ Level</span>
                                <div class="mt-1">
                                    @if($certificate->nvq_level)
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-2 fs-6">
                                            Level {{ $certificate->nvq_level }}
                                        </span>
                                    @else
                                        <span class="text-muted fst-italic">Not Specified</span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <span class="d-block text-uppercase text-muted fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Tracking ID</span>
                                <span class="d-inline-block mt-1 bg-white border rounded px-2 py-1 font-monospace fw-bold text-dark">{{ $certificate->certificate_number }}</span>
                            </div>

                            <div class="col-md-6">
                                <span class="d-block text-uppercase text-muted fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Date of Issue</span>
                                <span class="d-flex align-items-center mt-1 text-dark fw-medium">
                                    <i class="bi bi-calendar3 text-muted me-2"></i>
                                    {{ $certificate->issued_at->format('F j, Y') }}
                                </span>
                            </div>

                            <div class="col-md-6">
                                <span class="d-block text-uppercase text-muted fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Evaluating Assessor</span>
                                <span class="d-block mt-1 text-dark fw-medium">{{ $certificate->assessor->name ?? 'System Automated' }}</span>
                            </div>

                        </div>
                    </div>

                    {{-- Action Area --}}
                    <div class="text-center">
                        <a href="{{ route('verify.form') }}" class="btn btn-dark btn-lg px-5 py-3 shadow-sm rounded-3">
                            <i class="bi bi-arrow-repeat me-2"></i>Verify Another Certificate
                        </a>
                    </div>
                @endif

            </div>
            
            {{-- Card Footer Trust Element --}}
            <div class="card-footer bg-light border-top p-3 px-md-4 d-flex flex-column flex-md-row justify-content-between align-items-center">
                <div class="d-flex align-items-center text-success fw-medium small mb-2 mb-md-0">
                    <i class="bi bi-shield-check me-2 fs-5"></i>
                    <span>Verified by IqBrave NVQ Security System</span>
                </div>
                <div class="text-muted font-monospace" style="font-size: 0.75rem;">
                    Timestamp: {{ now()->format('Y-m-d H:i:s T') }}
                </div>
            </div>
        </div>
        
    </div>
</x-guest-layout>
