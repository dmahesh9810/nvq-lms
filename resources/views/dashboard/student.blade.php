@extends('layouts.app')

@section('title', 'My Dashboard')
@section('page-title', 'My Dashboard')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-12 col-sm-6">
        <div class="stat-card" style="background:#e8f0fe;">
            <div class="stat-icon bg-white text-soft-blue"><i class="bi bi-collection-play"></i></div>
            <div class="stat-value text-dark">{{ $stats['enrolled_courses'] }}</div>
            <div class="stat-label text-muted">Enrolled Courses</div>
        </div>
    </div>
    <div class="col-12 col-sm-6">
        <div class="stat-card" style="background:#e6f4ea;">
            <div class="stat-icon bg-white text-soft-green"><i class="bi bi-check2-all"></i></div>
            <div class="stat-value text-dark">{{ $stats['active_enrollments'] }}</div>
            <div class="stat-label text-muted">Active Enrollments</div>
        </div>
    </div>
</div>

{{-- Enrolled Courses --}}
@if ($courses->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-journal-x text-muted" style="font-size:3rem;"></i>
            <h5 class="mt-3">No courses yet!</h5>
            <p class="text-muted">Browse and enroll in a course to start learning.</p>
            <a href="{{ route('student.courses.browse') }}" class="btn btn-primary">
                <i class="bi bi-search me-2"></i>Browse Courses
            </a>
        </div>
    </div>
@else
    <h6 class="fw-semibold mb-3">My Enrolled Courses</h6>
    <div class="row g-4">
        @foreach ($courses as $course)
        @php $pct = $courseProgress[$course->id] ?? 0; @endphp
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100">
                @if ($course->thumbnail)
                    <img src="{{ asset('storage/'.$course->thumbnail) }}" class="card-img-top" style="height:160px; object-fit:cover;" alt="{{ $course->title }}">
                @else
                    <div style="height:160px; background: linear-gradient(135deg,#1a73e8,#6c3fc7);" class="d-flex align-items-center justify-content-center">
                        <i class="bi bi-book text-white" style="font-size:2.5rem;"></i>
                    </div>
                @endif
                <div class="card-body">
                    <h6 class="card-title fw-semibold">{{ $course->title }}</h6>
                    <p class="text-muted small mb-3">by {{ $course->instructor->name }}</p>
                    <div class="mb-1 d-flex justify-content-between small text-muted">
                        <span>Progress</span><span>{{ $pct }}%</span>
                    </div>
                    <div class="progress mb-3">
                        <div class="progress-bar bg-success" role="progressbar" style="width:{{ $pct }}%" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <a href="{{ route('student.courses.show', $course) }}" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-play-circle me-1"></i>Continue Learning
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-4">
        <a href="{{ route('student.courses.browse') }}" class="btn btn-outline-primary">
            <i class="bi bi-plus-circle me-2"></i>Enroll in More Courses
        </a>
    </div>
@endif
@endsection
