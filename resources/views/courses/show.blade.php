@extends('layouts.main')

@section('title', $course->title . ' - ' . config('app.name'))

@section('content')
    <div class="hero mb-5">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <span class="badge bg-primary mb-3">NVQ Program</span>
                    <h1 class="display-5 fw-bold mb-3">{{ $course->title }}</h1>
                    <p class="lead opacity-75 mb-4">{{ Str::limit(strip_tags($course->description), 150) }}</p>
                    
                    @auth
                        @php
                            $isEnrolled = $course->students()->where('user_id', Auth::id())->exists();
                        @endphp
                        
                        @if($isEnrolled)
                            <a href="{{ route('student.dashboard') }}" class="btn btn-success btn-lg px-4 shadow-sm"><i class="bi bi-play-circle-fill me-2"></i> Continue Learning</a>
                        @else
                            <form action="{{ route('student.courses.enroll', $course->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-lg px-4 shadow-sm">Enroll Now</button>
                            </form>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-4 shadow-sm">Login to Enroll</a>
                    @endauth
                </div>
                <div class="col-lg-5">
                    @if($course->thumbnail)
                        <img src="{{ Storage::url($course->thumbnail) }}" class="img-fluid hero-img w-100" alt="{{ $course->title }}">
                    @else
                        <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" class="img-fluid hero-img w-100" alt="{{ $course->title }}">
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="container pb-5">
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <h3 class="fw-bold mb-4">Course Description</h3>
                        <div>
                            {!! $course->description !!}
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 p-lg-5">
                        <h3 class="fw-bold mb-4">Course Content</h3>
                        <div class="accordion" id="courseContent">
                            @forelse($course->modules as $index => $module)
                                <div class="accordion-item border-0 border-bottom">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button {{ $index !== 0 ? 'collapsed' : '' }} fw-semibold bg-transparent" type="button" data-bs-toggle="collapse" data-bs-target="#module{{ $module->id }}">
                                            {{ $module->title }}
                                        </button>
                                    </h2>
                                    <div id="module{{ $module->id }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" data-bs-parent="#courseContent">
                                        <div class="accordion-body pb-4">
                                            @foreach($module->units as $unit)
                                                <div class="mb-3">
                                                    <h6 class="fw-bold text-muted small text-uppercase mb-2">{{ $unit->title }}</h6>
                                                    <ul class="list-group list-group-flush border">
                                                        @foreach($unit->lessons as $lesson)
                                                            <li class="list-group-item bg-light border-0 py-3">
                                                                <i class="bi bi-file-earmark-text text-primary me-2"></i> {{ $lesson->title }}
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted">Course content will be updated soon.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 mt-4 mt-lg-0">
                <div class="card border-0 shadow-sm rounded-4 position-sticky" style="top: 100px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">Instructor</h5>
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 60px; height: 60px;">
                                {{ substr(optional($course->instructor)->name ?? 'I', 0, 1) }}
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">{{ optional($course->instructor)->name ?? 'System Instructor' }}</h6>
                                <p class="text-muted small mb-0">Professional Trainer</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .hero { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: white; padding: 60px 0; }
        .hero-img { border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); }
    </style>
@endpush

