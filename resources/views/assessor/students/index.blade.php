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

{{-- Filters Card --}}
<div class="card shadow-sm border-0 rounded-4 mb-4">
    <div class="card-body bg-light rounded-4">
        <form method="GET" action="{{ route('assessor.students.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="student_id" class="form-label small fw-semibold">Student</label>
                <select name="student_id" id="student_id" class="form-select form-select-sm">
                    <option value="">All Students</option>
                    @foreach($studentsList as $st)
                        <option value="{{ $st->id }}" {{ request('student_id') == $st->id ? 'selected' : '' }}>
                            {{ $st->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="course_id" class="form-label small fw-semibold">Course</label>
                <select name="course_id" id="course_id" class="form-select form-select-sm">
                    <option value="">All Courses</option>
                    @foreach($courses as $c)
                        <option value="{{ $c->id }}" {{ request('course_id') == $c->id ? 'selected' : '' }}>
                            {{ Str::limit($c->title, 40) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="bi bi-funnel me-1"></i>Filter</button>
                <a href="{{ route('assessor.students.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear Filters"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
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
                                <div class="d-flex flex-column gap-2 mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar {{ $enrollment->progress_percentage == 100 ? 'bg-success' : 'bg-primary' }}" role="progressbar" style="width: {{ $enrollment->progress_percentage }}%;" aria-valuenow="{{ $enrollment->progress_percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <span class="ms-2 small fw-bold">{{ $enrollment->progress_percentage }}%</span>
                                    </div>
                                    <a href="{{ route('assessor.progress.detail', ['student' => $enrollment->user_id, 'course' => $enrollment->course_id]) }}" class="btn btn-sm btn-light border w-100 text-center text-primary" style="font-size: 0.8rem;">
                                        <i class="bi bi-eye me-1"></i>View Detail
                                    </a>
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
