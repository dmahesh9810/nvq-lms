@extends('layouts.app')
@section('title', $lesson->title) @section('page-title', 'Lesson Player')

@push('styles')
<style>
    .course-sidebar {
        height: calc(100vh - 120px);
        overflow-y: auto;
        position: sticky;
        top: 80px;
    }
    .lesson-link {
        display: flex; align-items: center; gap: 8px;
        padding: 6px 12px; border-radius: 6px;
        text-decoration: none; font-size: 0.83rem; color: #444;
        transition: background 0.15s;
    }
    .lesson-link:hover { background: #f0f4ff; color: #1a73e8; }
    .lesson-link.active { background: #e8f0fe; color: #1a73e8; font-weight: 600; }
    .lesson-link.completed { color: #2d9e5a; }
    .unit-title { font-size: 0.76rem; font-weight: 600; text-transform: uppercase; color: #aaa; margin: 8px 0 4px 4px; }
    .video-wrapper { position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 10px; }
    .video-wrapper iframe { position: absolute; top:0; left:0; width:100%; height:100%; border:0; }
    .pdf-viewer { width: 100%; height: 600px; border-radius: 10px; border: 1px solid #e0e0e0; }
    .lesson-content { line-height: 1.8; font-size: 0.95rem; }
    .lesson-content h1, .lesson-content h2, .lesson-content h3 { margin-top: 1.5rem; }
    .completed-banner { background: #e6f4ea; border: 1px solid #a8d5b5; border-radius: 10px; }
</style>
@endpush

@section('content')
<div class="row g-4">
    {{-- ── Course Navigation Sidebar ── --}}
    <div class="col-lg-3">
        <div class="card course-sidebar">
            <div class="card-header py-3 px-3">
                <a href="{{ route('student.courses.show', $course) }}" class="text-decoration-none text-dark fw-semibold small">
                    <i class="bi bi-arrow-left me-1"></i>{{ Str::limit($course->title, 30) }}
                </a>
                <div class="mt-2">
                    <div class="small text-muted mb-1">Course Progress</div>
                    <div class="progress mb-1">
                        <div class="progress-bar bg-success" style="width:{{ $progress }}%"></div>
                    </div>
                    <div class="small text-muted">{{ $progress }}%</div>
                </div>
            </div>
            <div class="card-body px-3 py-2">
                @foreach ($course->modules as $module)
                <div class="mb-2">
                    <div class="fw-medium small text-dark">
                        <i class="bi bi-collection text-primary me-1"></i>{{ $module->title }}
                    </div>
                    @foreach ($module->units as $unit)
                    <div class="ms-2">
                        <div class="unit-title">{{ $unit->title }}</div>
                        @foreach ($unit->lessons as $navLesson)
                        @php
                            $isThisLesson = $navLesson->id === $lesson->id;
                            $isNavCompleted = $navLesson->isCompletedByUser(auth()->id());
                        @endphp
                        <a href="{{ route('student.lessons.show', [$course, $navLesson]) }}"
                           class="lesson-link {{ $isThisLesson ? 'active' : ($isNavCompleted ? 'completed' : '') }}">
                            @if($isNavCompleted)
                                <i class="bi bi-check-circle-fill text-success"></i>
                            @elseif($isThisLesson)
                                <i class="bi bi-play-circle-fill text-primary"></i>
                            @else
                                <i class="bi bi-circle text-muted"></i>
                            @endif
                            {{ $navLesson->title }}
                        </a>
                        @endforeach
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Lesson Player ── --}}
    <div class="col-lg-9">
        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="{{ route('student.courses.show', $course) }}">{{ $course->title }}</a></li>
                <li class="breadcrumb-item">{{ $lesson->unit->module->title }}</li>
                <li class="breadcrumb-item">{{ $lesson->unit->title }}</li>
                <li class="breadcrumb-item active">{{ $lesson->title }}</li>
            </ol>
        </nav>

        {{-- Completed banner --}}
        @if ($isCompleted)
        <div class="completed-banner p-3 mb-4 d-flex align-items-center gap-3">
            <i class="bi bi-check-circle-fill text-success fs-4"></i>
            <div>
                <div class="fw-semibold text-success">Lesson Completed!</div>
                <div class="small text-muted">You have already marked this lesson as complete.</div>
            </div>
        </div>
        @endif

        <div class="card mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                    <div>
                        <h4 class="fw-bold mb-1">{{ $lesson->title }}</h4>
                        @if ($lesson->description)
                            <p class="text-muted mb-0">{{ $lesson->description }}</p>
                        @endif
                    </div>
                    @php
                        $typeLabels = ['video'=>['icon'=>'camera-video','color'=>'danger'],'pdf'=>['icon'=>'file-earmark-pdf','color'=>'danger'],'text'=>['icon'=>'file-text','color'=>'info'],'mixed'=>['icon'=>'layers','color'=>'warning']];
                        $tl = $typeLabels[$lesson->type] ?? ['icon'=>'file','color'=>'secondary'];
                    @endphp
                    <span class="badge bg-{{ $tl['color'] }} bg-opacity-25 text-{{ $tl['color'] }} border border-{{ $tl['color'] }} border-opacity-25">
                        <i class="bi bi-{{ $tl['icon'] }} me-1"></i>{{ ucfirst($lesson->type) }}
                    </span>
                </div>

                {{-- ── Video Player ── --}}
                @if ($lesson->embed_url)
                <div class="mb-4">
                    <div class="video-wrapper">
                        <iframe src="{{ $lesson->embed_url }}"
                                title="{{ $lesson->title }}"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen></iframe>
                    </div>
                </div>
                @endif

                {{-- ── PDF Viewer ── --}}
                @if ($lesson->pdf_path)
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 fw-semibold"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>PDF Document</h6>
                        <a href="{{ asset('storage/'.$lesson->pdf_path) }}" target="_blank" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-download me-1"></i>Download
                        </a>
                    </div>
                    <iframe src="{{ asset('storage/'.$lesson->pdf_path) }}" class="pdf-viewer" title="PDF Viewer"></iframe>
                </div>
                @endif

                {{-- ── Text / Rich Content ── --}}
                @if ($lesson->content)
                <div class="lesson-content mb-4">
                    {!! $lesson->content !!}
                </div>
                @endif

                {{-- ── Mark as Complete ── --}}
                @if (!$isCompleted)
                <div class="border-top pt-4 mt-2">
                    <form action="{{ route('student.lessons.complete', [$course, $lesson]) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success px-4">
                            <i class="bi bi-check-circle me-2"></i>Mark as Completed
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>

        {{-- ── Previous / Next Navigation ── --}}
        <div class="d-flex justify-content-between">
            @if ($prevLesson)
            <a href="{{ route('student.lessons.show', [$course, $prevLesson]) }}" class="btn btn-outline-secondary">
                <i class="bi bi-chevron-left me-1"></i>Previous Lesson
            </a>
            @else
            <div></div>
            @endif

            @if ($nextLesson)
            <a href="{{ route('student.lessons.show', [$course, $nextLesson]) }}" class="btn btn-primary">
                Next Lesson <i class="bi bi-chevron-right ms-1"></i>
            </a>
            @else
            <a href="{{ route('student.courses.show', $course) }}" class="btn btn-success">
                <i class="bi bi-check-all me-1"></i>Back to Course
            </a>
            @endif
        </div>
    </div>
</div>
@endsection
