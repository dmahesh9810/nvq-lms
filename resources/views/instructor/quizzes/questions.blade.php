@extends('layouts.app')
@section('title', 'Manage Questions — ' . $quiz->title)
@section('content')
<div class="d-flex align-items-center mb-4">
    <a href="{{ route('instructor.quizzes.index') }}" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
    <div>
        <h2 class="fw-bold mb-0">{{ $quiz->title }}</h2>
        <small class="text-muted">{{ $quiz->questions->count() }} question(s) · Pass mark: {{ $quiz->pass_mark }}%</small>
    </div>
</div>

<div class="row g-4">
    {{-- Existing Questions --}}
    <div class="col-lg-7">
        <h5 class="fw-semibold mb-3">Questions</h5>
        @forelse($quiz->questions as $i => $question)
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <p class="fw-semibold mb-2">Q{{ $i+1 }}. {{ $question->question_text }}
                            <span class="badge bg-secondary ms-1">{{ $question->marks }} mark{{ $question->marks > 1 ? 's' : '' }}</span>
                        </p>
                        <ul class="list-unstyled mb-0 ms-2">
                            @foreach($question->options as $option)
                            <li class="{{ $option->is_correct ? 'text-success fw-bold' : 'text-muted' }}">
                                <i class="bi {{ $option->is_correct ? 'bi-check-circle-fill' : 'bi-circle' }} me-2"></i>
                                {{ $option->option_text }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    <form action="{{ route('instructor.quizzes.questions.destroy', [$quiz, $question]) }}" method="POST"
                          class="ms-3" onsubmit="return confirm('Delete this question?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="alert alert-info">No questions yet. Add your first question →</div>
        @endforelse
    </div>

    {{-- Add Question Form --}}
    <div class="col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white fw-semibold">
                <i class="bi bi-plus-circle me-2"></i>Add New Question
            </div>
            <div class="card-body">
                <form action="{{ route('instructor.quizzes.questions.store', $quiz) }}" method="POST" id="questionForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Question <span class="text-danger">*</span></label>
                        <textarea name="question_text" class="form-control @error('question_text') is-invalid @enderror"
                                  rows="2" required>{{ old('question_text') }}</textarea>
                        @error('question_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Marks</label>
                        <input type="number" name="marks" class="form-control" value="{{ old('marks', 1) }}" min="1">
                    </div>

                    <label class="form-label fw-semibold">Answer Options <span class="text-danger">*</span></label>
                    <div id="optionsContainer">
                        @for($i = 0; $i < 4; $i++)
                        <div class="input-group mb-2">
                            <div class="input-group-text">
                                <input type="radio" name="correct_option" value="{{ $i }}"
                                       {{ old('correct_option') == $i ? 'checked' : ($i === 0 ? 'checked' : '') }}
                                       title="Mark as correct">
                            </div>
                            <input type="text" name="options[{{ $i }}]"
                                   class="form-control @error('options.'.$i) is-invalid @enderror"
                                   placeholder="Option {{ $i+1 }}" value="{{ old('options.'.$i) }}" required>
                        </div>
                        @endfor
                    </div>
                    <small class="text-muted d-block mb-3">Click the radio button to mark the correct answer.</small>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-plus-lg me-1"></i>Add Question
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
