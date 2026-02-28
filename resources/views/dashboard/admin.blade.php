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
</div>

<div class="row g-4">
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
