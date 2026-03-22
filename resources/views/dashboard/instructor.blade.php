@extends('layouts.app')

@section('title', 'Instructor Dashboard')
@section('page-title', 'Instructor Dashboard')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-12 col-sm-4">
        <div class="stat-card" style="background:#e8f0fe;">
            <div class="stat-icon bg-white text-soft-blue"><i class="bi bi-journals"></i></div>
            <div class="stat-value text-dark">{{ $stats['my_courses'] }}</div>
            <div class="stat-label text-muted">My Courses</div>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="stat-card" style="background:#e6f4ea;">
            <div class="stat-icon bg-white text-soft-green"><i class="bi bi-check2-circle"></i></div>
            <div class="stat-value text-dark">{{ $stats['published_courses'] }}</div>
            <div class="stat-label text-muted">Published</div>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="stat-card" style="background:#fef8e1;">
            <div class="stat-icon bg-white text-soft-amber"><i class="bi bi-mortarboard"></i></div>
            <div class="stat-value text-dark">{{ $stats['total_students'] }}</div>
            <div class="stat-label text-muted">Total Students</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center py-3 px-4">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-book me-2 text-primary"></i>My Recent Courses</h6>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus me-1"></i>New Course
        </a>
        @endif
    </div>
    <div class="card-body p-0">
        @if ($recentCourses->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-journal-x" style="font-size:2.5rem;"></i>
                <p class="mt-3 mb-2">No courses yet.</p>
                @if(auth()->user()->isAdmin())
                <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary btn-sm">Create your first course</a>
                @endif
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Course</th>
                            <th>Status</th>
                            <th>Students</th>
                            <th>Modules</th>
                            <th class="pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentCourses as $course)
                        <tr>
                            <td class="ps-4 fw-medium">{{ $course->title }}</td>
                            <td>
                                @php
                                    $sc = ['draft'=>'secondary','published'=>'success','archived'=>'dark'];
                                @endphp
                                <span class="badge bg-{{ $sc[$course->status] ?? 'secondary' }}">{{ ucfirst($course->status) }}</span>
                            </td>
                            <td>{{ $course->enrollments_count }}</td>
                            <td>{{ $course->modules_count }}</td>
                            <td class="pe-4">
                                <a href="{{ route('instructor.courses.show', $course) }}" class="btn btn-sm btn-outline-primary me-1">Manage</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@if($assignedModules->isNotEmpty())
<div class="card mt-4">
    <div class="card-header py-3 px-4">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-diagram-3 me-2 text-info"></i>My Assigned Modules</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Module Name</th>
                        <th>Parent Course</th>
                        <th class="pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($assignedModules as $module)
                    <tr>
                        <td class="ps-4 fw-medium">Module {{ $module->order }}: {{ $module->title }}</td>
                        <td class="text-muted">{{ $module->course->title }}</td>
                        <td class="pe-4">
                            <a href="{{ route('instructor.courses.show', $module->course) }}" class="btn btn-sm btn-outline-info">Manage</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@endsection
