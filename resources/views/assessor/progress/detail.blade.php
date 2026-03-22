@extends('layouts.app')

@section('title', 'Student Progress Detail')
@section('page-title', 'Detailed Progress')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-1">Student Progress Detail</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mt-2 mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('assessor.progress.index') }}">Progress</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $student->name }} / {{ Str::limit($course->title, 20) }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('assessor.progress.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Progress</a>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- Student Info summary card --}}
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 rounded-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-4">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow" style="width: 50px; height: 50px; font-size: 1.5rem;">
                        {{ substr($student->name, 0, 1) }}
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">{{ $student->name }}</h5>
                        <p class="text-muted small mb-0">{{ $student->email }}</p>
                    </div>
                </div>
                
                <hr class="text-muted opacity-25">
                
                <h6 class="fw-semibold text-dark"><i class="bi bi-journal-bookmark me-2 text-primary"></i>{{ $course->title }}</h6>
                <div class="d-flex flex-wrap gap-3 mt-3">
                    <span class="badge bg-light text-dark border"><i class="bi bi-calendar-check me-1"></i>Enrolled: {{ $enrollment->enrolled_at->format('M d, Y') }}</span>
                    <span class="badge {{ $enrollment->status === 'completed' ? 'bg-success' : 'bg-primary' }} text-white"><i class="bi bi-activity me-1"></i>Status: {{ ucfirst($enrollment->status) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Progress summary ring card --}}
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 rounded-4 h-100">
            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                <h6 class="text-muted fw-semibold text-uppercase mb-3" style="font-size: 0.8rem; letter-spacing: 1px;">Overall Progress</h6>
                
                <div class="position-relative d-inline-block" style="width: 120px; height: 120px;">
                    <svg class="w-100 h-100" viewBox="0 0 36 36">
                        <path class="text-light opacity-50" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="{{ $progressPercent == 100 ? 'text-success' : 'text-primary' }}" stroke-dasharray="{{ $progressPercent }}, 100" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="position-absolute top-50 start-50 translate-middle d-flex flex-column align-items-center">
                        <span class="fs-4 fw-bold {{ $progressPercent == 100 ? 'text-success' : 'text-dark' }}">{{ $progressPercent }}%</span>
                    </div>
                </div>
                
                <div class="mt-3 text-muted small fw-medium">
                    <span class="text-dark">{{ $completedLessons }}</span> of <span class="text-dark">{{ $totalLessons }}</span> lessons completed
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Lessons Table --}}
    <div class="col-lg-12">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white py-3 border-0 d-flex gap-2 align-items-center">
                <i class="bi bi-list-check fs-5 text-primary"></i>
                <h6 class="mb-0 fw-bold">Lesson Progress Breakdown</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" style="width: 60px;">#</th>
                                <th>Lesson Title</th>
                                <th>Module & Unit</th>
                                <th class="text-center">Status</th>
                                <th class="text-end pe-4">Completed On</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lessons as $i => $lesson)
                                @php $isCompleted = $lesson->progress->isNotEmpty(); @endphp
                                <tr>
                                    <td class="ps-4 fw-semibold text-muted">{{ $i + 1 }}</td>
                                    <td class="fw-medium text-dark">{{ $lesson->title }}</td>
                                    <td>
                                        <div class="small text-muted">{{ $lesson->unit->module->title }} / {{ $lesson->unit->title }}</div>
                                    </td>
                                    <td class="text-center">
                                        @if($isCompleted)
                                            <span class="badge bg-soft-success text-success border border-success border-opacity-25 rounded-pill px-3"><i class="bi bi-check-circle-fill me-1"></i>Completed</span>
                                        @else
                                            <span class="badge bg-soft-warning text-warning border border-warning border-opacity-25 rounded-pill px-3"><i class="bi bi-clock me-1"></i>Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4 small text-muted">
                                        {{ $isCompleted ? $lesson->progress->first()->completed_at->format('M d, Y h:i A') : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted p-4">No active lessons found in this course.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Quizzes and Assignments Side-by-Side --}}
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 rounded-4 h-100">
            <div class="card-header bg-white py-3 border-0 d-flex gap-2 align-items-center">
                <i class="bi bi-patch-question fs-5 text-warning"></i>
                <h6 class="mb-0 fw-bold">Quiz Attempts</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($quizAttempts as $attempt)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                            <div>
                                <h6 class="mb-1 text-dark fw-semibold">{{ $attempt->quiz->title }}</h6>
                                <small class="text-muted d-block">{{ $attempt->created_at->format('M d, Y h:i A') }}</small>
                            </div>
                            <div class="text-end">
                                @php
                                    $qCount = $attempt->quiz->questions()->count();
                                    $pct = $qCount > 0 ? (int)round(($attempt->score / $qCount) * 100) : 0;
                                    $isPassed = ($attempt->result === 'passed');
                                @endphp
                                <div class="fw-bold mb-1 {{ $isPassed ? 'text-success' : 'text-danger' }}">
                                    {{ $attempt->score }} / {{ $qCount }} ({{ $pct }}%)
                                </div>
                                <span class="badge {{ $isPassed ? 'bg-success' : 'bg-danger' }} rounded-pill" style="font-size: 0.7rem;">
                                    {{ $isPassed ? 'PASSED' : 'FAILED' }}
                                </span>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted p-4 border-bottom-0">
                            No quiz attempts yet.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm border-0 rounded-4 h-100">
            <div class="card-header bg-white py-3 border-0 d-flex gap-2 align-items-center">
                <i class="bi bi-journal-text fs-5 text-info"></i>
                <h6 class="mb-0 fw-bold">Assignment Outcomes</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($assignmentResults as $result)
                        <li class="list-group-item px-4 py-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1 text-dark fw-semibold">{{ $result->submission->assignment->title }}</h6>
                                    <span class="badge {{ $result->competency_status == 'competent' ? 'bg-success' : 'bg-warning text-dark' }} rounded-pill">
                                        {{ $result->competency_status == 'competent' ? 'Competent' : 'Not Yet Competent' }}
                                    </span>
                                </div>
                                @if($result->marks !== null)
                                    <div class="fs-5 fw-bold text-primary">{{ $result->marks }}<span class="fs-6 text-muted fw-normal">/100</span></div>
                                @endif
                            </div>
                            @if($result->feedback)
                                <div class="bg-light p-2 rounded small text-muted mt-2 border border-light">
                                    <i class="bi bi-chat-left-quote me-2"></i>"{{ Str::limit($result->feedback, 100) }}"
                                </div>
                            @endif
                            <div class="d-flex justify-content-between mt-2 pt-2 border-top border-light">
                                <small class="text-muted"><i class="bi bi-calendar me-1"></i>{{ $result->graded_at->format('M d, Y') }}</small>
                                <small class="text-muted"><i class="bi bi-person me-1"></i>{{ $result->assessor->name }}</small>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted p-4 border-bottom-0">
                            No graded assignments yet.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
