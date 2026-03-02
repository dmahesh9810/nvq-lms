@extends('layouts.app')
@section('title', $assignment->title)
@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">

<div class="d-flex align-items-center mb-4">
    <a href="{{ route('student.assignments.index') }}" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
    <h2 class="fw-bold mb-0">{{ $assignment->title }}</h2>
</div>

{{-- Assignment Details --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <p class="text-muted mb-1 small">Course</p>
                <p class="fw-semibold mb-0">{{ $assignment->unit->module->course->title }}</p>
            </div>
            <div class="col-md-6">
                <p class="text-muted mb-1 small">Unit</p>
                <p class="fw-semibold mb-0">{{ $assignment->unit->title }}</p>
            </div>
            @if($assignment->due_date)
            <div class="col-md-6">
                <p class="text-muted mb-1 small">Due Date</p>
                <p class="fw-semibold mb-0 {{ now()->gt($assignment->due_date) ? 'text-danger' : '' }}">
                    {{ $assignment->due_date->format('d M Y H:i') }}
                    @if(now()->gt($assignment->due_date))
                        <span class="badge bg-danger ms-1">Overdue</span>
                    @endif
                </p>
            </div>
            @endif
            @if($assignment->max_marks)
            <div class="col-md-6">
                <p class="text-muted mb-1 small">Max Marks</p>
                <p class="fw-semibold mb-0">{{ $assignment->max_marks }}</p>
            </div>
            @endif
        </div>
        @if($assignment->description)
        <hr>
        <h6 class="fw-semibold">Instructions</h6>
        <p class="mb-0">{!! nl2br(e($assignment->description)) !!}</p>
        @endif
    </div>
</div>

{{-- Assessor Feedback (if graded) --}}
@if($submission && $submission->result)
<div class="card border-0 mb-4 bg-{{ $submission->result->competencyBadge() === 'success' ? 'success' : 'danger' }} bg-opacity-10 border-{{ $submission->result->competencyBadge() }}">
    <div class="card-body">
        <div class="d-flex align-items-center mb-3">
            <div class="me-3">
                <span class="badge bg-{{ $submission->result->competencyBadge() }} p-3 fs-4">
                    {{ $submission->result->competencyLabel() }}
                </span>
            </div>
            <div>
                <h5 class="fw-bold mb-0">
                    {{ $submission->result->competency_status === 'competent' ? '✅ Competent' : '❌ Not Yet Competent' }}
                </h5>
                <small class="text-muted">Graded by {{ $submission->result->assessor->name }} on {{ $submission->result->graded_at->format('d M Y') }}</small>
            </div>
        </div>
        @if($submission->result->marks !== null)
        <p class="mb-1"><strong>Marks:</strong> {{ $submission->result->marks }}@if($assignment->max_marks) / {{ $assignment->max_marks }}@endif</p>
        @endif
        @if($submission->result->feedback)
        <p class="mb-0"><strong>Feedback:</strong><br>{{ $submission->result->feedback }}</p>
        @endif
    </div>
</div>
@endif

{{-- Current Submission Status --}}
@if($submission)
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <h6 class="fw-semibold mb-2">Your Submission</h6>
        <p class="mb-1">
            <span class="badge bg-info text-dark">{{ ucfirst($submission->status) }}</span>
            · Submitted {{ $submission->submitted_at->format('d M Y H:i') }}
        </p>
        <a href="{{ Storage::url($submission->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-download me-1"></i>Download My File
        </a>
    </div>
</div>
@endif

{{-- Upload Form --}}
@if(!$submission || $submission->status !== 'graded')
<div class="card shadow-sm border-0">
    <div class="card-header fw-semibold bg-light">
        {{ $submission ? '📤 Re-submit Assignment' : '📤 Submit Assignment' }}
    </div>
    <div class="card-body">
        <form action="{{ route('student.assignments.submit', $assignment) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label">Upload File <span class="text-danger">*</span></label>
                <input type="file" name="file" class="form-control @error('file') is-invalid @enderror"
                       accept=".pdf,.doc,.docx,.zip" required>
                <div class="form-text">Accepted formats: PDF, DOC, DOCX, ZIP · Max 10MB</div>
                @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-upload me-1"></i> Submit
            </button>
        </form>
    </div>
</div>
@else
<div class="alert alert-secondary">
    <i class="bi bi-lock me-2"></i>This assignment has been graded and can no longer be resubmitted.
</div>
@endif

</div>
</div>
@endsection
