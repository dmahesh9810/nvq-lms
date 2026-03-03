@extends('layouts.app')
@section('title', 'My Certificates')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0"><i class="bi bi-award-fill text-warning me-2"></i>My Certificates</h2>
        <small class="text-muted">NVQ competency certificates you have earned</small>
    </div>
    <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Dashboard
    </a>
</div>

@if($certificates->isEmpty())
<div class="card shadow-sm border-0">
    <div class="card-body text-center py-5">
        <i class="bi bi-award text-muted" style="font-size:4rem"></i>
        <h5 class="mt-3 text-muted">No certificates yet</h5>
        <p class="text-muted mb-0">Complete all assignments in a course with Competent (C) results to earn a certificate.</p>
    </div>
</div>
@else
<div class="row g-4">
    @foreach($certificates as $cert)
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm border-0 h-100 {{ $cert->status === 'revoked' ? 'border-danger border' : '' }}">
            <div class="card-body d-flex flex-column">

                {{-- Status badge --}}
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="badge bg-{{ $cert->status === 'active' ? 'success' : 'danger' }} fs-6 px-3 py-2">
                        @if($cert->status === 'active')
                            <i class="bi bi-patch-check-fill me-1"></i>Active
                        @else
                            <i class="bi bi-x-circle-fill me-1"></i>Revoked
                        @endif
                    </span>
                    <small class="text-muted">{{ $cert->issued_at->format('d M Y') }}</small>
                </div>

                {{-- Course title --}}
                <h5 class="fw-bold mb-1">{{ $cert->course->title }}</h5>
                <p class="text-muted small mb-3">
                    <i class="bi bi-bookmark-check me-1"></i>NVQ Course — All units competent
                </p>

                {{-- Certificate number --}}
                <div class="bg-light rounded p-2 mb-3">
                    <small class="text-muted d-block">Certificate Number</small>
                    <code class="text-primary fw-bold">{{ $cert->certificate_number }}</code>
                </div>

                <div class="mt-auto">
                    @if($cert->status === 'active')
                    <a href="{{ route('student.certificates.download', $cert) }}"
                       class="btn btn-warning w-100 fw-semibold">
                        <i class="bi bi-download me-2"></i>Download Certificate (PDF)
                    </a>
                    @else
                    <button class="btn btn-outline-danger w-100" disabled>
                        <i class="bi bi-ban me-2"></i>Certificate Revoked
                    </button>
                    @endif
                </div>

            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection
