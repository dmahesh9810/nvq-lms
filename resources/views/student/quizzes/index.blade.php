@extends('layouts.app')
@section('title', 'My Quizzes')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0"><i class="bi bi-patch-question me-2 text-primary"></i>My Quizzes</h2>
</div>

@if($quizzes->isEmpty())
    <div class="alert alert-info">No quizzes available yet. Enroll in courses to see quizzes.</div>
@else
<div class="row g-3">
    @foreach($quizzes as $quiz)
    @php $attempt = $attempts->get($quiz->id); @endphp
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body">
                <h6 class="fw-bold">{{ $quiz->title }}</h6>
                <p class="text-muted small mb-1">
                    <i class="bi bi-book me-1"></i>{{ $quiz->unit->module->course->title }}
                </p>
                <p class="text-muted small mb-2">
                    <i class="bi bi-grid me-1"></i>{{ $quiz->unit->title }}
                </p>
                <div class="d-flex gap-2 mb-3">
                    <span class="badge bg-secondary">{{ $quiz->questions_count }} Qs</span>
                    <span class="badge bg-warning text-dark">Pass: {{ $quiz->pass_mark }}%</span>
                </div>

                @if($attempt)
                    <div class="mb-2">
                        @if($attempt->result === 'PASS')
                            <span class="badge bg-success p-2"><i class="bi bi-check-circle me-1"></i>Passed · {{ rtrim(rtrim(number_format($attempt->score, 2), '0'), '.') }}%</span>
                        @else
                            <span class="badge bg-danger p-2"><i class="bi bi-x-circle me-1"></i>Failed · {{ rtrim(rtrim(number_format($attempt->score, 2), '0'), '.') }}%</span>
                        @endif
                    </div>
                    <small class="text-muted">{{ $attempt->completed_at ? $attempt->completed_at->format('d M Y') : 'In Progress' }}</small>
                @else
                    <span class="badge bg-light text-dark border">Not Attempted</span>
                @endif
            </div>
            <div class="card-footer bg-transparent border-0">
                @if($attempt)
                    <a href="{{ route('student.quizzes.result', [$quiz, $attempt]) }}" class="btn btn-sm btn-outline-primary w-100 mb-1">
                        <i class="bi bi-eye me-1"></i>View Result
                    </a>
                    @if($attempt->result !== 'PASS')
                    <a href="{{ route('student.quizzes.start.view', $quiz) }}" class="btn btn-sm btn-warning w-100">
                        <i class="bi bi-arrow-repeat me-1"></i>Retry Quiz
                    </a>
                    @endif
                @else
                    <a href="{{ route('student.quizzes.start.view', $quiz) }}" class="btn btn-sm btn-primary w-100">
                        <i class="bi bi-play-circle me-1"></i>Start Quiz
                    </a>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
<div class="mt-4">{{ $quizzes->links() }}</div>
@endif
@endsection
