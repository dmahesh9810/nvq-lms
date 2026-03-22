@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')

@section('content')
<div class="row g-4 mb-4">
    {{-- Stat Cards --}}
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="stat-card" style="background:#e8f0fe;">
            <div class="stat-icon bg-white text-soft-blue"><i class="bi bi-people-fill"></i></div>
            <div class="stat-value text-dark">{{ $stats['total_users'] }}</div>
            <div class="stat-label text-muted">Total Users</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="stat-card" style="background:#e6f4ea;">
            <div class="stat-icon bg-white text-soft-green"><i class="bi bi-mortarboard"></i></div>
            <div class="stat-value text-dark">{{ $stats['total_students'] }}</div>
            <div class="stat-label text-muted">Total Students</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="stat-card" style="background:#fef8e1;">
            <div class="stat-icon bg-white text-soft-amber"><i class="bi bi-person-video3"></i></div>
            <div class="stat-value text-dark">{{ $stats['total_instructors'] }}</div>
            <div class="stat-label text-muted">Instructors</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="stat-card" style="background:#f3e8fd;">
            <div class="stat-icon bg-white text-soft-purple"><i class="bi bi-book-half"></i></div>
            <div class="stat-value text-dark">{{ $stats['total_courses'] }}</div>
            <div class="stat-label text-muted">Total Courses</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="stat-card" style="background:#e4f4f2;">
            <div class="stat-icon bg-white text-soft-teal"><i class="bi bi-check-circle"></i></div>
            <div class="stat-value text-dark">{{ $stats['published_courses'] }}</div>
            <div class="stat-label text-muted">Published Courses</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="stat-card" style="background:#fce9e8;">
            <div class="stat-icon bg-white text-soft-red"><i class="bi bi-journal-check"></i></div>
            <div class="stat-value text-dark">{{ $stats['total_enrollments'] }}</div>
            <div class="stat-label text-muted">Enrollments</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-4">
        <a href="{{ route('admin.change-requests.index') }}" class="text-decoration-none">
            <div class="stat-card" style="background:#fff3cd;">
                <div class="stat-icon bg-white text-warning"><i class="bi bi-pencil-square"></i></div>
                <div class="stat-value text-dark">{{ $stats['pending_requests'] }}</div>
                <div class="stat-label text-muted">Pending Change Requests</div>
            </div>
        </a>
    </div>
</div>

{{-- Pending Change Requests --}}
<div class="row g-4 mt-1">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between py-3 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-pencil-square text-warning me-2"></i>Pending Change Requests
                    @if($stats['pending_requests'] > 0)
                        <span class="badge bg-warning text-dark ms-1">{{ $stats['pending_requests'] }}</span>
                    @endif
                </h6>
                <a href="{{ route('admin.change-requests.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                @if($pendingRequests->isEmpty())
                    <div class="p-4 text-center text-muted">No pending change requests.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Type</th>
                                    <th>Action</th>
                                    <th>Target</th>
                                    <th>Instructor</th>
                                    <th>Submitted</th>
                                    <th class="pe-4 text-end">Review</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingRequests as $req)
                                <tr>
                                    <td class="ps-4"><span class="badge bg-secondary">{{ $req->typeLabel() }}</span></td>
                                    <td><span class="badge bg-{{ $req->action === 'delete' ? 'danger' : 'primary' }} bg-opacity-75">{{ $req->actionLabel() }}</span></td>
                                    <td class="fw-medium">{{ $req->target_title }}</td>
                                    <td class="text-muted small">{{ $req->requester->name }}</td>
                                    <td class="text-muted small">{{ $req->created_at->diffForHumans() }}</td>
                                    <td class="pe-4 text-end">
                                        <a href="{{ route('admin.change-requests.show', $req) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i>Review
                                        </a>
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

{{-- Pending Course Approvals --}}
<div class="row g-4 mt-1">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between py-3 px-4">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-hourglass-split text-warning me-2"></i>Pending Course Approvals</h6>
            </div>
            <div class="card-body p-0">
                @if($pendingCourses->isEmpty())
                    <div class="p-4 text-center text-muted">No pending courses to review.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Course</th>
                                    <th>Instructor</th>
                                    <th>Submitted</th>
                                    <th class="pe-4 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingCourses as $pendingCourse)
                                <tr>
                                    <td class="ps-4 fw-medium">
                                        {{ $pendingCourse->title }}
                                        <a href="{{ route('instructor.courses.show', $pendingCourse) }}" target="_blank" class="ms-1 small text-primary" title="Preview Course"><i class="bi bi-box-arrow-up-right"></i></a>
                                    </td>
                                    <td>{{ $pendingCourse->instructor->name }}</td>
                                    <td class="text-muted small">{{ $pendingCourse->updated_at->diffForHumans() }}</td>
                                    <td class="pe-4 text-end">
                                        <form action="{{ route('admin.courses.approve', $pendingCourse) }}" method="POST" class="d-inline">
                                            @csrf @method('PATCH')
                                            <button class="btn btn-sm btn-success"><i class="bi bi-check-circle me-1"></i>Approve</button>
                                        </form>
                                        <form action="{{ route('admin.courses.reject', $pendingCourse) }}" method="POST" class="d-inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Reject this course?')"><i class="bi bi-x-circle me-1"></i>Reject</button>
                                        </form>
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
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between py-3 px-4">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Quick Actions</h6>
            </div>
            <div class="card-body px-4 py-3">
                <div class="row g-3">
                    <div class="col-auto">
                        <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Create Course
                        </a>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('instructor.courses.index') }}" class="btn btn-outline-primary">
                            <i class="bi bi-collection me-2"></i>View All Courses
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
