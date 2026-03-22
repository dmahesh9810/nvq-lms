@extends('layouts.app')
@section('title', 'Grade Submission')
@section('content')
<div class="row justify-content-center">
<div class="col-lg-9">

<div class="d-flex align-items-center mb-4">
    <a href="{{ route('assessor.grading.index') }}" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
    <div>
        <h2 class="fw-bold mb-0">Verify Submission</h2>
        <small class="text-muted">{{ $submission->assignment->unit->module->course->title }} → {{ $submission->assignment->title }}</small>
    </div>
</div>

<div class="row g-4">
    {{-- Submission Info --}}
    <div class="col-md-5">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header fw-semibold bg-light">Student Details</div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><th class="text-muted" style="width:40%">Name</th><td class="fw-semibold">{{ $submission->student->name }}</td></tr>
                    <tr><th class="text-muted">Email</th><td>{{ $submission->student->email }}</td></tr>
                    <tr><th class="text-muted">Submitted</th><td>{{ $submission->submitted_at->format('d M Y H:i') }}</td></tr>
                    <tr><th class="text-muted">Status</th>
                        <td><span class="badge bg-{{ $submission->status === 'resubmitted' ? 'warning text-dark' : 'info text-dark' }}">
                            {{ ucfirst($submission->status) }}</span>
                        </td>
                    </tr>
                    @if($submission->assignment->max_marks)
                    <tr><th class="text-muted">Max Marks</th><td>{{ $submission->assignment->max_marks }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        {{-- Download / View Submission --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header fw-semibold bg-light">Submission File</div>
            <div class="card-body text-center">
                <i class="bi bi-file-earmark-text text-primary" style="font-size:3rem"></i>
                <p class="small text-muted mt-2 mb-3">{{ basename($submission->file_path) }}</p>
                <a href="{{ Storage::url($submission->file_path) }}" target="_blank" class="btn btn-primary w-100">
                    <i class="bi bi-download me-2"></i>Open / Download File
                </a>
            </div>
        </div>

        {{-- Assignment Brief --}}
        @if($submission->assignment->description)
        <div class="card shadow-sm border-0">
            <div class="card-header fw-semibold bg-light">Assignment Brief</div>
            <div class="card-body">
                <p class="mb-0 small">{!! nl2br(e($submission->assignment->description)) !!}</p>
            </div>
        </div>
        @endif

        {{-- Instructor Review --}}
        @if($submission->instructor_review)
        <div class="card shadow-sm border-0 mt-4">
            <div class="card-header fw-semibold bg-info text-dark border-0">
                <i class="bi bi-person-badge me-2"></i>Instructor Review
            </div>
            <div class="card-body bg-light">
                <div class="mb-3 border-bottom pb-2">
                    <span class="badge bg-primary">Reviewed by {{ $submission->instructor->name ?? 'Instructor' }}</span>
                    <small class="text-muted ms-2">{{ $submission->instructor_reviewed_at?->format('d M Y H:i') }}</small>
                </div>
                <p class="mb-0 text-dark" style="white-space: pre-wrap;">{{ $submission->instructor_review }}</p>
            </div>
        </div>
        @endif
    </div>

    {{-- Verification Form --}}
    <div class="col-md-7">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-success text-white fw-semibold">
                <i class="bi bi-shield-check me-2"></i>NVQ Assessor Verification
            </div>
            <div class="card-body p-4">

                {{-- Show existing result if re-grading --}}
                @if($submission->isAssessorActioned())
                <div class="alert alert-info py-2 mb-3">
                    <strong>Previously Verified:</strong>
                    {{ $submission->verified_at->format('d M Y H:i') }}
                    by {{ $submission->assessor->name }}
                </div>
                @endif

                <div class="mb-4">
                    <h5 class="fw-bold">Instructor Decision: 
                        <span class="badge bg-{{ $submission->instructor_competency_status === 'competent' ? 'success' : 'danger' }} fs-5">
                            {{ $submission->instructor_competency_status === 'competent' ? 'Competent (C)' : 'Not Yet Competent (NYC)' }}
                        </span>
                    </h5>
                    <p class="text-muted small">Review the student's submission and the instructor's feedback on the left, then verify the decision.</p>
                </div>

                <form action="{{ route('assessor.grading.verify', $submission) }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label fw-bold fs-5">Audit Action <span class="text-danger">*</span></label>
                        <div class="row g-3">
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="action" id="action_verify"
                                       value="verify" required {{ old('action', $submission->status === 'assessor_verified' ? 'verify' : '') === 'verify' ? 'checked' : '' }}>
                                <label class="btn btn-outline-success w-100 py-3" for="action_verify">
                                    <i class="bi bi-check-circle fs-3 d-block mb-1"></i>
                                    Verify & Endorse
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="action" id="action_reject"
                                       value="reject" required {{ old('action', $submission->status === 'assessor_rejected' ? 'reject' : '') === 'reject' ? 'checked' : '' }}>
                                <label class="btn btn-outline-danger w-100 py-3" for="action_reject">
                                    <i class="bi bi-x-circle fs-3 d-block mb-1"></i>
                                    Reject Assessment
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Verification Note --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Verification Note (Optional)</label>
                        <textarea name="note" class="form-control" rows="4"
                                  placeholder="Add an internal note for TVEC auditing purposes...">{{ old('note', $submission->assessor_verification_note) }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg w-100">
                        <i class="bi bi-shield-lock me-2"></i>Submit Verification
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
</div>
@endsection
