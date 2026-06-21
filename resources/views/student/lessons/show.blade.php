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

                {{-- ── AI Micro-Topics / Flashcards ── --}}
                @if ($lesson->microTopics->count() > 0)
                <div class="mb-4 mt-5">
                    <h5 class="fw-bold mb-3"><i class="bi bi-stars text-warning me-2"></i>Interactive Study (AI Generated)</h5>
                    <div class="accordion" id="microTopicsAccordion">
                        @foreach ($lesson->microTopics as $index => $topic)
                        <div class="accordion-item border-0 bg-light mb-3 rounded-3 shadow-sm">
                            <h2 class="accordion-header" id="heading-{{ $topic->id }}">
                                <button class="accordion-button {{ $index !== 0 ? 'collapsed' : '' }} fw-semibold bg-white rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $topic->id }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="collapse-{{ $topic->id }}">
                                    {{ $topic->topic_name }}
                                    <span class="badge bg-secondary ms-auto me-2"><i class="bi bi-clock me-1"></i>{{ $topic->estimated_minutes }} min</span>
                                </button>
                            </h2>
                            <div id="collapse-{{ $topic->id }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" aria-labelledby="heading-{{ $topic->id }}" data-bs-parent="#microTopicsAccordion">
                                <div class="accordion-body">
                                    @if ($topic->key_takeaway)
                                    <div class="alert alert-info border-0 shadow-sm rounded-3 py-2 px-3 mb-4">
                                        <strong><i class="bi bi-lightbulb-fill text-warning me-2"></i>Key Takeaway:</strong> {{ $topic->key_takeaway }}
                                    </div>
                                    @endif

                                    @if (is_array($topic->concept_cards) && count($topic->concept_cards) > 0)
                                    <h6 class="fw-bold mb-3">Concept Cards</h6>
                                    <div class="row g-3 mb-4">
                                        @foreach ($topic->concept_cards as $card)
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card h-100 border-0 shadow-sm rounded-3">
                                                <div class="card-body">
                                                    <div class="fs-1 mb-2">{{ $card['emoji'] ?? '💡' }}</div>
                                                    <h6 class="fw-bold">{{ $card['title'] ?? 'Concept' }}</h6>
                                                    <p class="text-muted small mb-0">{{ $card['body'] ?? '' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif

                                    @if ($topic->microQuizQuestions->count() > 0)
                                    <h6 class="fw-bold mb-3">Knowledge Check</h6>
                                    <div class="row g-3">
                                        @foreach ($topic->microQuizQuestions as $qIndex => $question)
                                        <div class="col-12">
                                            <div class="card border-0 shadow-sm rounded-3">
                                                <div class="card-body">
                                                    <p class="fw-medium mb-3">Q{{ $qIndex + 1 }}: {{ $question->question_text }}</p>
                                                    <div class="d-flex flex-column gap-2">
                                                        @foreach ($question->options as $option)
                                                        <button class="btn btn-outline-primary text-start quiz-option" data-correct="{{ $option->is_correct ? 'true' : 'false' }}" onclick="checkAnswer(this)">
                                                            {{ $option->option_text }}
                                                        </button>
                                                        @endforeach
                                                    </div>
                                                    <div class="feedback-msg mt-2 d-none fw-semibold small"></div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <script>
                    function checkAnswer(btn) {
                        let parent = btn.parentElement;
                        let buttons = parent.querySelectorAll('button');
                        buttons.forEach(b => b.classList.add('disabled'));
                        
                        let feedback = parent.nextElementSibling;
                        feedback.classList.remove('d-none');

                        if (btn.dataset.correct === 'true') {
                            btn.classList.replace('btn-outline-primary', 'btn-success');
                            feedback.classList.add('text-success');
                            feedback.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Correct!';
                        } else {
                            btn.classList.replace('btn-outline-primary', 'btn-danger');
                            feedback.classList.add('text-danger');
                            feedback.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i>Incorrect! The correct answer is highlighted.';
                            // Highlight correct answer
                            buttons.forEach(b => {
                                if (b.dataset.correct === 'true') {
                                    b.classList.replace('btn-outline-primary', 'btn-outline-success');
                                    b.classList.add('fw-bold');
                                }
                            });
                        }
                    }
                </script>
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
