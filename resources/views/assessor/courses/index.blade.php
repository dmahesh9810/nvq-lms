@extends('layouts.app')

@section('title', 'Course Performance Analytics')
@section('page-title', 'Course Analytics')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-1">Course Performance Analytics</h5>
            <p class="text-muted small mb-0">Evaluate how students interact with courses system-wide.</p>
        </div>
        <a href="{{ route('assessor.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Dashboard</a>
    </div>
</div>

{{-- Filters Card --}}
<div class="card shadow-sm border-0 rounded-4 mb-4">
    <div class="card-body bg-light rounded-4">
        <form method="GET" action="{{ route('assessor.courses.index') }}" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label for="course_id" class="form-label small fw-semibold">Search Course</label>
                <select name="course_id" id="course_id" class="form-select form-select-sm">
                    <option value="">All Active Courses</option>
                    @foreach($allCourses ?? [] as $c)
                        <option value="{{ $c->id }}" {{ request('course_id') == $c->id ? 'selected' : '' }}>
                            {{ Str::limit($c->title, 60) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="bi bi-search me-1"></i>Search</button>
                <a href="{{ route('assessor.courses.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear Filters"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0 rounded-4">
    <div class="card-body p-0">
        @if($courses->isEmpty())
            <div class="p-5 text-center text-muted">
                <i class="bi bi-journal-text" style="font-size: 2.5rem;"></i>
                <p class="mt-3">No active courses found.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Course</th>
                            <th>Instructor</th>
                            <th class="text-center">Total Enrollments</th>
                            <th class="text-center">Status</th>
                            <th style="min-width: 150px;">Avg Progress (System-wide)</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($courses as $course)
                        <tr>
                            <td class="ps-4 fw-semibold text-dark">
                                {{ Str::limit($course->title, 50) }}
                            </td>
                            <td>{{ $course->instructor->name }}</td>
                            <td class="text-center">
                                <span class="badge bg-info text-dark rounded-pill px-3">{{ $course->enrollments_count }}</span>
                            </td>
                            <td class="text-center">
                                @php $sc = ['draft'=>'secondary','pending'=>'warning','published'=>'success','rejected'=>'danger','archived'=>'dark']; @endphp
                                <span class="badge bg-{{ $sc[$course->status] ?? 'secondary' }} rounded-pill">{{ ucfirst($course->status) }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $course->average_progress }}%;" aria-valuenow="{{ $course->average_progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <span class="ms-2 small fw-bold">{{ $course->average_progress }}%</span>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('assessor.progress.index', ['course_id' => $course->id]) }}" class="btn btn-sm btn-light border shadow-sm text-primary">
                                    <i class="bi bi-people me-1"></i>View Students
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    @if($courses->hasPages())
    <div class="card-footer bg-white px-4 py-3">
        {{ $courses->links() }}
    </div>
    @endif
</div>
@endsection
