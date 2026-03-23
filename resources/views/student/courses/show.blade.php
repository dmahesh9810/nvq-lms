@extends('layouts.app')
@section('title', $course->title) @section('page-title', $course->title)

@push('styles')
<style>
    /* Sidebar for course navigation */
    .course-sidebar {
        height: calc(100vh - 120px);
        overflow-y: auto;
        position: sticky;
        top: 80px;
    }
    .lesson-link {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.85rem;
        color: #444;
        transition: background 0.15s;
    }
    .lesson-link:hover { background: #f0f4ff; color: #1a73e8; }
    .lesson-link.completed { color: #2d9e5a; }
    .unit-title { font-size: 0.78rem; font-weight: 600; text-transform: uppercase; color: #999; margin: 8px 0 4px; }
</style>
@endpush

@section('content')
<div class="row g-4">
    {{-- Left: Course navigation sidebar --}}
    <div class="col-lg-3">
        <div class="card course-sidebar">
            <div class="card-header py-3 px-3">
                <h6 class="mb-1 fw-semibold small">{{ $course->title }}</h6>
                <div class="small text-muted mb-2">Overall progress</div>
                <div class="progress mb-1">
                    <div class="progress-bar bg-success" style="width:{{ $progress }}%"></div>
                </div>
                <div class="small text-muted">{{ $progress }}% complete</div>
            </div>
            <div class="card-body px-3 py-2">
                @foreach ($course->modules as $module)
                <div class="mb-2">
                    <div class="fw-medium small text-dark mb-1">
                        <i class="bi bi-collection text-primary me-1"></i>{{ $module->title }}
                    </div>
                    @foreach ($module->units as $unit)
                    <div class="ms-2">
                        <div class="unit-title d-flex align-items-center justify-content-between mb-2">
                            <span>{{ $unit->title }}</span>
                            @php
                                $competencyCode = $unit->nvq_unit_code ? '<span class="badge bg-light text-secondary border me-1" style="font-size: 0.6rem;">'.$unit->nvq_unit_code.'</span>' : '';
                                $competency = $unit->competencyAssessments->first();
                                $status = $competency ? $competency->status : 'not_assessed';
                                $badgeClass = match($status) {
                                    'competent' => 'bg-soft-green text-success border border-success border-opacity-25',
                                    'not_competent' => 'bg-soft-red text-danger border border-danger border-opacity-25',
                                    default => 'bg-light text-warning border border-warning border-opacity-25'
                                };
                                $badgeLabel = match($status) {
                                    'competent' => '<i class="bi bi-check-circle-fill me-1"></i>Competent',
                                    'not_competent' => '<i class="bi bi-x-circle-fill me-1"></i>NYC',
                                    default => '<i class="bi bi-hourglass-split me-1"></i>Pending'
                                };
                            @endphp
                            <div>
                                {!! $competencyCode !!}
                                <span class="badge {{ $badgeClass }} rounded-pill" style="font-size: 0.65rem; font-weight: 600;">{!! $badgeLabel !!}</span>
                            </div>
                        </div>
                        @foreach ($unit->lessons as $lesson)
                        <a href="{{ route('student.lessons.show', [$course, $lesson]) }}"
                           class="lesson-link {{ in_array($lesson->id, $completedLessonIds) ? 'completed' : '' }}">
                            @if(in_array($lesson->id, $completedLessonIds))
                                <i class="bi bi-check-circle-fill text-success"></i>
                            @else
                                <i class="bi bi-circle text-muted"></i>
                            @endif
                            {{ $lesson->title }}
                        </a>
                        @endforeach
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Right: Course welcome / overview --}}
    <div class="col-lg-9">
        <div class="card mb-4">
            @if ($course->thumbnail)
                <img src="{{ asset('storage/'.$course->thumbnail) }}" class="card-img-top" style="max-height:300px; object-fit:cover;" alt="{{ $course->title }}">
            @endif
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                    <div>
                        <h4 class="fw-bold mb-1">{{ $course->title }}</h4>
                        <p class="text-muted mb-0"><i class="bi bi-person me-1"></i>{{ $course->instructor->name }}</p>
                    </div>
                    <div class="text-end">
                        <div class="small text-muted mb-1">Your Progress</div>
                        <span class="fs-4 fw-bold text-success">{{ $progress }}%</span>
                    </div>
                </div>
                @if ($course->description)
                    <p class="text-muted">{{ $course->description }}</p>
                @endif
                <div class="progress" style="height:10px;">
                    <div class="progress-bar bg-success" style="width:{{ $progress }}%"></div>
                </div>
            </div>
        </div>

        {{-- First lesson shortcut --}}
        @php
            $firstLesson = $course->modules->first()?->units->first()?->lessons->first();
        @endphp
        @if ($firstLesson)
        <div class="d-flex gap-2">
            <a href="{{ route('student.lessons.show', [$course, $firstLesson]) }}" class="btn btn-primary">
                <i class="bi bi-play-circle-fill me-2"></i>
                {{ $progress > 0 ? 'Continue Learning' : 'Start Learning' }}
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
