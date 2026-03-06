@extends('layouts.app')
@section('title', 'Quiz: ' . $quiz->title)
@section('page-title', 'Attempting Quiz')

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-8">

        {{-- Quiz Header --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-3 px-4 d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="mb-0 fw-bold text-dark">{{ $quiz->title }}</h5>
                    <small class="text-muted">Pass mark: {{ $quiz->pass_mark }}% &nbsp;&bull;&nbsp; Total marks: {{ $quiz->totalMarks() }}</small>
                </div>
                <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                    <i class="bi bi-hourglass-split me-1"></i> In Progress
                </span>
            </div>
        </div>

        @if($quiz->questions->isEmpty())
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                This quiz has no questions yet. Please check back later.
            </div>
        @else

        <form action="{{ route('student.quizzes.submit', [$quiz, $attempt]) }}" method="POST">
            @csrf

            @foreach ($quiz->questions as $index => $question)
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">

                    {{-- Question Header --}}
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h6 class="fw-bold text-dark mb-0 lh-base" style="max-width: 85%;">
                            {{ $index + 1 }}. {{ $question->question_text }}
                        </h6>
                        <span class="badge bg-primary rounded-pill ms-2 flex-shrink-0">
                            {{ $question->marks }} {{ Str::plural('Mark', $question->marks) }}
                        </span>
                    </div>

                    {{-- Options --}}
                    @if($question->options->isEmpty())
                        <p class="text-muted fst-italic small">No options available for this question.</p>
                    @else
                        <div class="d-flex flex-column gap-2 mt-3 ms-2">
                            @foreach ($question->options as $option)
                            <label class="d-flex align-items-center gap-3 p-3 rounded-3 border cursor-pointer quiz-option" 
                                   for="option_{{ $option->id }}"
                                   style="cursor: pointer; transition: background 0.15s;">
                                <input class="form-check-input flex-shrink-0 mt-0"
                                       type="radio"
                                       id="option_{{ $option->id }}"
                                       name="answers[{{ $question->id }}]"
                                       value="{{ $option->id }}">
                                <span class="text-dark">{{ $option->option_text }}</span>
                            </label>
                            @endforeach
                        </div>
                    @endif

                </div>
            </div>
            @endforeach

            {{-- Submit --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <p class="mb-0 text-muted small">
                        <i class="bi bi-info-circle me-1"></i>
                        Make sure you have answered all {{ $quiz->questions->count() }} questions before submitting.
                    </p>
                    <button type="submit"
                            class="btn btn-success px-4"
                            onclick="return confirm('Submit your answers? You cannot change them after submitting.')">
                        <i class="bi bi-send-fill me-2"></i>Submit Quiz
                    </button>
                </div>
            </div>

        </form>

        @endif

    </div>
</div>

@push('styles')
<style>
    .quiz-option:hover { background: #f0f4ff; border-color: #6c8eef !important; }
    .quiz-option:has(input:checked) { background: #e8f0fe; border-color: #4a6ef5 !important; }
</style>
@endpush

@endsection
