@extends('layouts.app')
@section('title', 'Quiz Result — ' . $quiz->title)
@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">

{{-- Result Banner --}}
<div class="card shadow-sm border-0 mb-4 {{ $attempt->passed ? 'border-success' : 'border-danger' }}" style="border-left:5px solid !important">
    <div class="card-body text-center p-5">
        @if($attempt->passed)
            <div class="mb-3"><i class="bi bi-trophy-fill text-warning" style="font-size:4rem"></i></div>
            <h2 class="fw-bold text-success mb-1">Congratulations!</h2>
            <p class="text-muted mb-3">You passed the quiz</p>
        @else
            <div class="mb-3"><i class="bi bi-x-circle-fill text-danger" style="font-size:4rem"></i></div>
            <h2 class="fw-bold text-danger mb-1">Not Quite There Yet</h2>
            <p class="text-muted mb-3">You can try again to improve your score</p>
        @endif

        <div class="row justify-content-center g-3 mb-4">
            <div class="col-auto">
                <div class="border rounded p-3 text-center" style="min-width:100px">
                    <div class="fs-2 fw-bold {{ $attempt->passed ? 'text-success' : 'text-danger' }}">{{ $attempt->percentage }}%</div>
                    <small class="text-muted">Your Score</small>
                </div>
            </div>
            <div class="col-auto">
                <div class="border rounded p-3 text-center" style="min-width:100px">
                    <div class="fs-2 fw-bold text-primary">{{ $quiz->pass_mark }}%</div>
                    <small class="text-muted">Pass Mark</small>
                </div>
            </div>
            <div class="col-auto">
                <div class="border rounded p-3 text-center" style="min-width:100px">
                    <div class="fs-2 fw-bold">{{ $attempt->score }}</div>
                    <small class="text-muted">Marks Scored</small>
                </div>
            </div>
        </div>

        <span class="badge fs-5 p-3 bg-{{ $attempt->passed ? 'success' : 'danger' }}">
            {{ $attempt->passed ? '✅ PASSED' : '❌ FAILED' }}
        </span>
    </div>
</div>

{{-- Question Review --}}
<h5 class="fw-semibold mb-3">Questions Overview</h5>
@foreach($quiz->questions as $i => $question)
<div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
        <p class="fw-semibold mb-2">
            <span class="badge bg-secondary me-2">{{ $i+1 }}</span>
            {{ $question->question_text }}
        </p>
        <ul class="list-unstyled mb-0">
            @foreach($question->options as $option)
            <li class="py-1 px-2 rounded mb-1 {{ $option->is_correct ? 'bg-success bg-opacity-15 text-success fw-bold' : 'text-muted' }}">
                <i class="bi {{ $option->is_correct ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }} me-2"></i>
                {{ $option->option_text }}
            </li>
            @endforeach
        </ul>
    </div>
</div>
@endforeach

<div class="d-flex gap-2 mt-4 mb-5">
    <a href="{{ route('student.quizzes.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to Quizzes
    </a>
    @if(!$attempt->passed)
    <a href="{{ route('student.quizzes.take', $quiz) }}" class="btn btn-warning">
        <i class="bi bi-arrow-repeat me-1"></i>Retry Quiz
    </a>
    @endif
</div>

</div>
</div>
@endsection
