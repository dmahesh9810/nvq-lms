@extends('layouts.app')
@section('title', 'Start Quiz: ' . $quiz->title)
@section('page-title', 'Start Quiz')

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-7 col-md-9">

        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('student.quizzes.index') }}">My Quizzes</a></li>
                <li class="breadcrumb-item active">{{ $quiz->title }}</li>
            </ol>
        </nav>

        <div class="card shadow-sm border-0">
            <div class="card-header py-3 px-4 bg-white border-bottom">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-patch-question-fill text-primary me-2"></i>{{ $quiz->title }}
                </h5>
            </div>
            <div class="card-body p-4">

                @if($quiz->description)
                    <p class="text-muted mb-4">{{ $quiz->description }}</p>
                @endif

                <div class="row g-3 mb-4">
                    <div class="col-4">
                        <div class="text-center bg-light rounded-3 py-3">
                            <div class="fs-3 fw-bold text-primary">{{ $quiz->questions()->count() }}</div>
                            <div class="small text-muted">Questions</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="text-center bg-light rounded-3 py-3">
                            <div class="fs-3 fw-bold text-warning">{{ $quiz->pass_mark }}%</div>
                            <div class="small text-muted">Pass Mark</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="text-center bg-light rounded-3 py-3">
                            <div class="fs-3 fw-bold text-success">{{ $quiz->totalMarks() }}</div>
                            <div class="small text-muted">Total Marks</div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info d-flex align-items-start" role="alert">
                    <i class="bi bi-info-circle-fill me-2 mt-1 flex-shrink-0"></i>
                    <span>Read each question carefully. You can only submit once. Make sure you have answered all questions before submitting.</span>
                </div>

                <form action="{{ route('student.quizzes.start', $quiz) }}" method="POST" class="mt-4">
                    @csrf
                    <div class="d-flex align-items-center justify-content-between">
                        <a href="{{ route('student.quizzes.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-play-circle-fill me-2"></i>Start Quiz Now
                        </button>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>

@endsection
