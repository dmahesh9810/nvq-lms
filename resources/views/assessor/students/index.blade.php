@extends('layouts.app')

@section('title', 'Student Progress Monitoring')
@section('page-title', 'Student Progress')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-1">Student Progress</h5>
            <p class="text-muted small mb-0">Monitor individual learning paths and completion rates.</p>
        </div>
        <a href="{{ route('assessor.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Dashboard</a>
    </div>
</div>

<div class="card shadow-sm border-0 rounded-4">
    <div class="card-body p-0">
        @if($enrollments->isEmpty())
            <div class="p-5 text-center text-muted">
                <i class="bi bi-people" style="font-size: 2.5rem;"></i>
                <p class="mt-3">No student enrollments found.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Student</th>
                            <th>Enrolled Course</th>
                            <th class="text-center">Completed Lessons</th>
                            <th class="text-center">Pending Lessons</th>
                            <th style="min-width: 150px;">Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($enrollments as $enrollment)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold text-dark">{{ $enrollment->student->name }}</div>
                                <div class="small text-muted">{{ $enrollment->student->email }}</div>
                            </td>
                            <td>
                                <span class="fw-medium text-primary">{{ Str::limit($enrollment->course->title, 40) }}</span>
                                <div class="small text-muted">Enrolled: {{ $enrollment->created_at->format('M d, Y') }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success rounded-pill px-3">{{ $enrollment->completed_lessons }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning text-dark rounded-pill px-3">{{ $enrollment->pending_lessons }}</span>
                            </td>
                            <td class="pe-4">
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $enrollment->progress_percentage }}%;" aria-valuenow="{{ $enrollment->progress_percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <span class="ms-2 small fw-bold">{{ $enrollment->progress_percentage }}%</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    @if($enrollments->hasPages())
    <div class="card-footer bg-white px-4 py-3">
        {{ $enrollments->links() }}
    </div>
    @endif
</div>
@endsection
