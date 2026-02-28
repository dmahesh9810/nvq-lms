@extends('layouts.app')
@section('title', 'Browse Courses') @section('page-title', 'Browse Courses')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">Available Courses</h5>
        <p class="text-muted small mb-0">Enroll in a course to start learning.</p>
    </div>
</div>

@if ($courses->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-search" style="font-size:2.5rem;"></i>
            <p class="mt-3 mb-0">No courses available at the moment. Check back soon!</p>
        </div>
    </div>
@else
    <div class="row g-4">
        @foreach ($courses as $course)
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100">
                @if ($course->thumbnail)
                    <img src="{{ asset('storage/'.$course->thumbnail) }}" class="card-img-top"
                         style="height:160px; object-fit:cover;" alt="{{ $course->title }}">
                @else
                    <div style="height:160px; background:linear-gradient(135deg,#1a73e8,#6c3fc7);"
                         class="d-flex align-items-center justify-content-center">
                        <i class="bi bi-book text-white" style="font-size:2.5rem;"></i>
                    </div>
                @endif
                <div class="card-body d-flex flex-column">
                    <h6 class="card-title fw-semibold">{{ $course->title }}</h6>
                    <p class="text-muted small mb-1">
                        <i class="bi bi-person me-1"></i>{{ $course->instructor->name }}
                    </p>
                    <p class="text-muted small mb-3">
                        <i class="bi bi-people me-1"></i>{{ $course->enrollments_count }} enrolled
                    </p>
                    <p class="text-muted small flex-grow-1">{{ Str::limit($course->description, 100) }}</p>
                    <form action="{{ route('student.courses.enroll', $course) }}" method="POST" class="mt-auto">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-plus-circle me-2"></i>Enroll Now
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-4">
        {{ $courses->links() }}
    </div>
@endif
@endsection
