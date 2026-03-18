@extends('layouts.app')
@section('title', 'Certificates — Admin')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0"><i class="bi bi-award-fill text-warning me-2"></i>Issued Certificates</h2>
        <small class="text-muted">{{ $certificates->total() }} total certificates</small>
    </div>
</div>

{{-- Filter Form --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.certificates.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Search Student</label>
                <input type="text" name="search" class="form-control" placeholder="Name or email..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Filter by Course</label>
                <select name="course_id" class="form-select">
                    <option value="">All Courses</option>
                    @foreach($courses as $course)
                    <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                        {{ $course->title }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="revoked" {{ request('status') === 'revoked' ? 'selected' : '' }}>Revoked</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
                <a href="{{ route('admin.certificates.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Certificates Table --}}
@if($certificates->isEmpty())
<div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>No certificates found matching your criteria.</div>
@else
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Certificate #</th>
                    <th>Student</th>
                    <th>Course</th>
                    <th>Issued</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($certificates as $cert)
                <tr>
                    <td><code class="text-primary">{{ $cert->certificate_number }}</code></td>
                    <td>
                        <div class="fw-semibold">{{ $cert->user->name }}</div>
                        <small class="text-muted">{{ $cert->user->email }}</small>
                    </td>
                    <td>{{ $cert->course->title }}</td>
                    <td><small>{{ $cert->issued_at->format('d M Y') }}</small></td>
                    <td>
                        <span class="badge bg-{{ $cert->status === 'active' ? 'success' : 'danger' }}">
                            {{ ucfirst($cert->status) }}
                        </span>
                    </td>
                    <td class="text-end">
                        @if($cert->status === 'active')
                        <form action="{{ route('admin.certificates.revoke', $cert) }}" method="POST"
                              class="d-inline"
                              onsubmit="return confirm('Revoke certificate {{ $cert->certificate_number }}?')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-ban me-1"></i>Revoke
                            </button>
                        </form>
                        @else
                        <form action="{{ route('admin.certificates.reinstate', $cert) }}" method="POST"
                              class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Reinstate
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $certificates->links() }}</div>
@endif
@endsection
