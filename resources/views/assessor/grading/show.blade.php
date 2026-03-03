@extends('layouts.app')
@section('title', 'Grade Submission')
@section('content')
<div class="row justify-content-center">
<div class="col-lg-9">

<div class="d-flex align-items-center mb-4">
    <a href="{{ route('assessor.grading.index') }}" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
    <div>
        <h2 class="fw-bold mb-0">Grade Submission</h2>
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
    </div>

    {{-- Grading Form --}}
    <div class="col-md-7">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white fw-semibold">
                <i class="bi bi-clipboard-check me-2"></i>NVQ Competency Assessment
            </div>
            <div class="card-body p-4">

                {{-- Show existing result if re-grading --}}
                @if($submission->result)
                <div class="alert alert-info py-2 mb-3">
                    <strong>Previously graded:</strong>
                    <span class="badge bg-{{ $submission->result->competencyBadge() }} ms-1">{{ $submission->result->competencyLabel() }}</span>
                    by {{ $submission->result->assessor->name }}
                </div>
                @endif

                <form action="{{ route('assessor.grading.grade', $submission) }}" method="POST">
                    @csrf

                    {{-- NVQ Competency Status (primary decision) --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold fs-5">Competency Decision <span class="text-danger">*</span></label>
                        <div class="row g-3">
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="competency_status" id="competent"
                                       value="competent" required
                                       {{ old('competency_status', $submission->result?->competency_status) === 'competent' ? 'checked' : '' }}>
                                <label class="btn btn-outline-success w-100 py-4" for="competent">
                                    <div class="fw-bold fs-2">C</div>
                                    <div class="small">Competent</div>
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="competency_status" id="not_yet_competent"
                                       value="not_yet_competent" required
                                       {{ old('competency_status', $submission->result?->competency_status) === 'not_yet_competent' ? 'checked' : '' }}>
                                <label class="btn btn-outline-danger w-100 py-4" for="not_yet_competent">
                                    <div class="fw-bold fs-2">NYC</div>
                                    <div class="small">Not Yet Competent</div>
                                </label>
                            </div>
                        </div>
                        @error('competency_status')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    {{-- Optional Marks --}}
                    @if($submission->assignment->max_marks)
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Marks (Optional)</label>
                        <div class="input-group">
                            <input type="number" name="marks" class="form-control"
                                   value="{{ old('marks', $submission->result?->marks) }}"
                                   min="0" max="{{ $submission->assignment->max_marks }}">
                            <span class="input-group-text">/ {{ $submission->assignment->max_marks }}</span>
                        </div>
                    </div>
                    @endif

                    {{-- Feedback --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Feedback / Comments</label>
                        <textarea name="feedback" class="form-control" rows="5"
                                  placeholder="Provide constructive feedback to the student...">{{ old('feedback', $submission->result?->feedback) }}</textarea>
                        <div class="form-text">This feedback will be visible to the student.</div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-check-circle me-2"></i>Save Assessment
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
</div>
@endsection
