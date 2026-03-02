@extends('layouts.app')
@section('title', 'Take Quiz — ' . $quiz->title)
@section('content')
<div class="row justify-content-center">
<div class="col-lg-9">

    {{-- Quiz Header --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h3 class="fw-bold mb-1">{{ $quiz->title }}</h3>
                    <p class="text-muted mb-0">{{ $quiz->unit->module->course->title }} · {{ $quiz->unit->title }}</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-warning text-dark fs-6 p-2">Pass: {{ $quiz->pass_mark }}%</span><br>
                    <small class="text-muted">{{ $quiz->questions->count() }} question(s)</small>
                </div>
            </div>
            @if($quiz->description)
            <p class="mt-3 mb-0 text-muted">{{ $quiz->description }}</p>
            @endif
            @if($lastAttempt)
            <div class="alert alert-warning mt-3 mb-0 py-2">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Previous score: <strong>{{ $lastAttempt->percentage }}%</strong> — Retry to improve!
            </div>
            @endif
        </div>
    </div>

    {{-- Progress bar (JavaScript-powered) --}}
    <div class="mb-3">
        <div class="d-flex justify-content-between small text-muted mb-1">
            <span id="progressLabel">Question 1 of {{ $quiz->questions->count() }}</span>
            <span id="answeredCount">0 answered</span>
        </div>
        <div class="progress" style="height:6px">
            <div class="progress-bar bg-primary" id="progressBar" style="width:0%" role="progressbar"></div>
        </div>
    </div>

    {{-- Quiz Form --}}
    <form action="{{ route('student.quizzes.submit', $quiz) }}" method="POST" id="quizForm">
        @csrf

        @foreach($quiz->questions as $i => $question)
        <div class="card shadow-sm border-0 mb-4 question-card" data-question="{{ $i }}">
            <div class="card-body p-4">
                <p class="fw-semibold mb-3">
                    <span class="badge bg-primary me-2">{{ $i + 1 }}</span>
                    {{ $question->question_text }}
                    @if($question->marks > 1)
                    <span class="badge bg-secondary ms-1">{{ $question->marks }} marks</span>
                    @endif
                </p>
                <div class="d-flex flex-column gap-2">
                    @foreach($question->options as $option)
                    <label class="option-label d-flex align-items-center p-3 border rounded cursor-pointer"
                           style="cursor:pointer" for="option_{{ $option->id }}">
                        <input type="radio" class="form-check-input me-3 option-radio"
                               name="answers[{{ $question->id }}]"
                               id="option_{{ $option->id }}"
                               value="{{ $option->id }}"
                               data-question="{{ $i }}">
                        {{ $option->option_text }}
                    </label>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach

        <div class="d-flex justify-content-between align-items-center mt-4 mb-5">
            <a href="{{ route('student.quizzes.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-success btn-lg px-5" id="submitBtn">
                <i class="bi bi-check-circle me-2"></i>Submit Quiz
            </button>
        </div>
    </form>
</div>
</div>

<script>
(function() {
    const total = {{ $quiz->questions->count() }};
    const radios = document.querySelectorAll('.option-radio');
    const answeredQuestions = new Set();
    const bar = document.getElementById('progressBar');
    const answeredCount = document.getElementById('answeredCount');

    // Highlight selected option
    radios.forEach(radio => {
        radio.addEventListener('change', function() {
            // Remove prior highlight in this question
            document.querySelectorAll(`[data-question="${this.dataset.question}"] .option-label`).forEach(l => {
                l.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
            });
            this.closest('.option-label').classList.add('border-primary', 'bg-primary', 'bg-opacity-10');

            answeredQuestions.add(this.dataset.question);
            const pct = Math.round((answeredQuestions.size / total) * 100);
            bar.style.width = pct + '%';
            answeredCount.textContent = answeredQuestions.size + ' answered';
        });
    });

    // Confirm submit
    document.getElementById('quizForm').addEventListener('submit', function(e) {
        if (answeredQuestions.size < total) {
            if (!confirm(`You have answered ${answeredQuestions.size} of ${total} questions. Submit anyway?`)) {
                e.preventDefault();
            }
        }
    });
})();
</script>
@endsection
