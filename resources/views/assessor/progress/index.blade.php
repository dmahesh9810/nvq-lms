@extends('layouts.app')

@section('title', 'Overall Student Progress')
@section('page-title', 'Progress Tracking')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-1">Overall Student Progress</h5>
            <p class="text-muted small mb-0">Track and filter student performance across all courses.</p>
        </div>
        <a href="{{ route('assessor.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Dashboard</a>
    </div>
</div>

{{-- Filters Card --}}
<div class="card shadow-sm border-0 rounded-4 mb-4">
    <div class="card-body bg-light rounded-4">
        <form method="GET" action="{{ route('assessor.progress.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="student_id" class="form-label small fw-semibold">Student</label>
                <select name="student_id" id="student_id" class="form-select form-select-sm">
                    <option value="">All Students</option>
                    @foreach($students as $st)
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

            <div class="col-md-2">
                <label for="from_date" class="form-label small fw-semibold">Enrolled From</label>
                <input type="date" name="from_date" id="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
            </div>

            <div class="col-md-2">
                <label for="to_date" class="form-label small fw-semibold">Enrolled To</label>
                <input type="date" name="to_date" id="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="bi bi-funnel me-1"></i>Filter</button>
                <a href="{{ route('assessor.progress.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear Filters"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

{{-- Progress Data Table --}}
<div class="card shadow-sm border-0 rounded-4">
    <div class="card-body p-0">
        @if($enrollments->isEmpty())
            <div class="p-5 text-center text-muted">
                <i class="bi bi-bar-chart-steps" style="font-size: 2.5rem;"></i>
                <p class="mt-3 mb-0">No progress data found matching the current filters.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Student</th>
                            <th>Course</th>
                            <th class="text-center">Enrolled</th>
                            <th style="min-width: 180px;">Progress</th>
                            <th class="text-end pe-4">Actions</th>
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
                                <div class="d-flex align-items-center gap-2">
                                    @if($enrollment->course->thumbnail)
                                        <img src="{{ asset('storage/' . $enrollment->course->thumbnail) }}" alt="Thumbnail" class="rounded" style="width: 40px; height: 30px; object-fit: cover;">
                                    @else
                                        <div class="bg-secondary rounded" style="width: 40px; height: 30px; opacity: 0.2;"></div>
                                    @endif
                                    <span class="fw-medium text-primary">{{ Str::limit($enrollment->course->title, 40) }}</span>
                                </div>
                            </td>
                            <td class="text-center text-muted small">
                                {{ $enrollment->enrolled_at->format('M d, Y') }}
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    <div class="d-flex justify-content-between align-items-center small">
                                        <span class="text-muted border rounded px-1">{{ $enrollment->completed_lessons }} / {{ $enrollment->total_lessons }} Lessons</span>
                                        <span class="fw-bold {{ $enrollment->progress_percentage == 100 ? 'text-success' : 'text-primary' }}">{{ $enrollment->progress_percentage }}%</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar {{ $enrollment->progress_percentage == 100 ? 'bg-success' : 'bg-primary' }}" role="progressbar" style="width: {{ $enrollment->progress_percentage }}%;" aria-valuenow="{{ $enrollment->progress_percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('assessor.progress.detail', ['student' => $enrollment->user_id, 'course' => $enrollment->course_id]) }}" class="btn btn-sm btn-light border shadow-sm">
                                    <i class="bi bi-eye text-primary me-1"></i>View Detail
                                </a>
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
