@extends('layouts.app')

@section('title', 'Assessor Dashboard')
@section('page-title', 'Assessor Overview')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card" style="background:#e8f0fe;">
            <div class="stat-icon bg-white text-soft-blue"><i class="bi bi-people-fill"></i></div>
            <div class="stat-value text-dark">{{ $stats['total_students'] }}</div>
            <div class="stat-label text-muted">Total Students</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card" style="background:#e6f4ea;">
            <div class="stat-icon bg-white text-soft-green"><i class="bi bi-journal-bookmark"></i></div>
            <div class="stat-value text-dark">{{ $stats['active_courses'] }}</div>
            <div class="stat-label text-muted">Active Courses</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card" style="background:#fef8e1;">
            <div class="stat-icon bg-white text-soft-amber"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="stat-value text-dark">{{ $stats['average_progress'] }}%</div>
            <div class="stat-label text-muted">Average Progress</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card" style="background:#fce9e8;">
            <div class="stat-icon bg-white text-soft-red"><i class="bi bi-clipboard-check"></i></div>
            <div class="stat-value text-dark">{{ $stats['pending_grading'] }}</div>
            <div class="stat-label text-muted">Pending Evaluations</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-12">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3 px-4 border-bottom">
                <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history text-primary me-2"></i>Recent Enrollments Preview</h6>
                <a href="{{ route('assessor.students.index') }}" class="btn btn-sm btn-outline-primary">View All Students</a>
            </div>
            <div class="card-body p-0">
                @if($recentEnrollments->isEmpty())
                    <div class="p-4 text-center text-muted">No recent enrollments.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Student</th>
                                    <th>Course</th>
                                    <th>Enrolled Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentEnrollments as $enrollment)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-semibold text-dark">{{ $enrollment->student->name }}</div>
                                        <div class="small text-muted">{{ $enrollment->student->email }}</div>
                                    </td>
                                    <td>
                                        <span class="fw-medium text-primary">{{ Str::limit($enrollment->course->title, 40) }}</span>
                                    </td>
                                    <td class="text-muted small">
                                        {{ $enrollment->created_at->diffForHumans() }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-md-12">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3 px-4 border-bottom">
                <h6 class="mb-0 fw-bold"><i class="bi bi-lightning-charge text-warning me-2"></i>Quick Actions</h6>
            </div>
            <div class="card-body px-4 py-3">
                <div class="d-flex gap-3 flex-wrap">
                    <a href="{{ route('assessor.students.index') }}" class="btn btn-primary">
                        <i class="bi bi-people me-2"></i>Student Progress
                    </a>
                    <a href="{{ route('assessor.courses.index') }}" class="btn btn-outline-primary">
                        <i class="bi bi-bar-chart me-2"></i>Course Analytics
                    </a>
                    @if(Route::has('assessor.grading.index'))
                    <a href="{{ route('assessor.grading.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-clipboard-check me-2"></i>Grading Portal
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
