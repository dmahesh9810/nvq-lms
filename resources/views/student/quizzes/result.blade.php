@extends('layouts.app')
@section('title', 'Quiz Result: ' . $quiz->title)
@section('page-title', 'Quiz Result')

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-8">

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>{{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Result Summary Card --}}
        <div class="card shadow-sm border-0 mb-4 text-center">
            <div class="card-body py-5">
                @if($attempt->result === 'PASS')
                    <div class="mb-3">
                        <i class="bi bi-trophy-fill text-warning" style="font-size: 3rem;"></i>
                    </div>
                    <h3 class="fw-bold text-success mb-1">Congratulations! You Passed!</h3>
                @else
                    <div class="mb-3">
                        <i class="bi bi-x-circle-fill text-danger" style="font-size: 3rem;"></i>
                    </div>
                    <h3 class="fw-bold text-danger mb-1">Quiz Not Passed</h3>
                @endif
                <p class="text-muted mb-4">{{ $quiz->title }}</p>

                <div class="row g-3 justify-content-center">
                    <div class="col-auto">
                        <div class="border rounded-3 px-4 py-3 bg-light">
                            <div class="fs-2 fw-black text-dark">
                                {{ $attempt->score }} <span class="fs-5 text-muted fw-normal">/ {{ $quiz->totalMarks() }}</span>
                            </div>
                            <div class="small text-muted">Your Score</div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="border rounded-3 px-4 py-3 bg-light">
                            <div class="fs-2 fw-black {{ $attempt->result === 'PASS' ? 'text-success' : 'text-danger' }}">
                                {{ $quiz->totalMarks() > 0 ? round(($attempt->score / $quiz->totalMarks()) * 100, 1) : 0 }}%
                            </div>
                            <div class="small text-muted">Percentage</div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="border rounded-3 px-4 py-3 bg-light">
                            <div class="fs-2 fw-black text-warning">{{ $quiz->pass_mark }}%</div>
                            <div class="small text-muted">Pass Mark</div>
                        </div>
                    </div>
                </div>

                @php
                    $passLabel = $attempt->result === 'PASS' ? 'bg-success' : 'bg-danger';
                @endphp
                <span class="badge {{ $passLabel }} fs-6 mt-4 px-4 py-2 rounded-pill">
                    {{ $attempt->result === 'PASS' ? 'PASSED' : 'FAILED' }}
                </span>
            </div>
        </div>

        {{-- Answer Review --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header py-3 px-4 bg-white border-bottom">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-list-check me-2 text-primary"></i>Review Your Answers
                </h6>
            </div>
            <div class="card-body p-4">

                @foreach ($quiz->questions as $index => $question)
                    @php
                        $studentAnswer = $attempt->answers->firstWhere('question_id', $question->id);
                        $selectedOptionId = $studentAnswer ? $studentAnswer->selected_option_id : null;
                        $isCorrect = $studentAnswer ? $studentAnswer->is_correct : false;
                    @endphp

                    <div class="p-4 mb-3 rounded-3 border {{ $isCorrect ? 'border-success bg-success bg-opacity-10' : 'border-danger bg-danger bg-opacity-10' }}">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h6 class="fw-bold text-dark mb-0" style="max-width: 85%;">
                                {{ $index + 1 }}. {{ $question->question_text }}
                            </h6>
                            @if($isCorrect)
                                <span class="badge bg-success ms-2 flex-shrink-0">
                                    <i class="bi bi-check-lg me-1"></i>{{ $question->marks }} / {{ $question->marks }}
                                </span>
                            @else
                                <span class="badge bg-danger ms-2 flex-shrink-0">
                                    <i class="bi bi-x-lg me-1"></i>0 / {{ $question->marks }}
                                </span>
                            @endif
                        </div>

                        <div class="d-flex flex-column gap-2 ms-2">
                            @foreach ($question->options as $option)
                                @php
                                    $isSelected = ($selectedOptionId == $option->id);
                                    $isActualCorrect = $option->is_correct;
                                @endphp

                                <div class="d-flex align-items-center gap-2 p-2 rounded
                                    {{ $isActualCorrect ? 'bg-success bg-opacity-25 fw-semibold' : ($isSelected && !$isActualCorrect ? 'bg-danger bg-opacity-25' : '') }}">
                                    <div class="rounded-circle border d-flex align-items-center justify-content-center flex-shrink-0"
                                         style="width:20px;height:20px;
                                            {{ $isSelected && $isActualCorrect ? 'background:#198754;border-color:#198754;' : ($isSelected && !$isActualCorrect ? 'background:#dc3545;border-color:#dc3545;' : 'background:#fff;') }}">
                                        @if($isSelected)
                                            <div class="rounded-circle bg-white" style="width:8px;height:8px;"></div>
                                        @endif
                                    </div>
                                    <span class="text-dark">{{ $option->option_text }}</span>
                                    @if($isActualCorrect)
                                        <i class="bi bi-check-circle-fill text-success ms-1"></i>
                                    @endif
                                    @if($isSelected && !$isActualCorrect)
                                        <i class="bi bi-x-circle-fill text-danger ms-1"></i>
                                    @endif
                                    @if($isSelected)
                                        <span class="text-muted small fst-italic ms-1">(Your Answer)</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                @endforeach

            </div>
        </div>

        {{-- Back Button --}}
        <div class="text-center mb-4">
            <a href="{{ route('student.quizzes.index') }}" class="btn btn-outline-primary px-4">
                <i class="bi bi-arrow-left me-1"></i> Back to Quizzes
            </a>
        </div>

    </div>
</div>

@endsection
