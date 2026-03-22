@extends('layouts.app')
@section('title', 'Submissions — ' . $assignment->title)
@section('content')
<div class="d-flex align-items-center mb-4">
    <a href="{{ route('instructor.assignments.index') }}" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
    <div>
        <h2 class="fw-bold mb-0">Submissions</h2>
        <small class="text-muted">{{ $assignment->title }}</small>
    </div>
</div>

@if($submissions->isEmpty())
    <div class="alert alert-info">No submissions yet for this assignment.</div>
@else
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Student</th>
                    <th>Submitted At</th>
                    <th>Status</th>
                    <th>Competency</th>
                    <th>File</th>
                    <th>Review</th>
                </tr>
            </thead>
            <tbody>
                @foreach($submissions as $submission)
                <tr>
                    <td class="fw-semibold">{{ $submission->student->name }}</td>
                    <td>{{ $submission->submitted_at->format('d M Y H:i') }}</td>
                    <td>
                        @php
                            $statusColors = ['submitted' => 'info', 'resubmitted' => 'warning', 'reviewed' => 'primary', 'assessed' => 'success', 'graded' => 'success'];
                        @endphp
                        <span class="badge bg-{{ $statusColors[$submission->status] ?? 'secondary' }} text-dark">
                            {{ ucfirst($submission->status) }}
                        </span>
                    </td>
                    <td>
                        @if($submission->result)
                            <span class="badge bg-{{ $submission->result->competencyBadge() }} fs-6 px-3 py-2">
                                {{ $submission->result->competencyLabel() }}
                            </span>
                        @else
                            <span class="text-muted">Pending</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ Storage::url($submission->file_path) }}" target="_blank"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-download me-1"></i>Download
                        </a>
                    </td>
                    <td>
                        @if(!$submission->isReviewed() && !$submission->isAssessed())
                            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#reviewModal{{ $submission->id }}">
                                Add Review
                            </button>
                        @else
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#reviewModal{{ $submission->id }}">
                                View Review
                            </button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modals for Reviews -->
@foreach($submissions as $submission)
    <div class="modal fade" id="reviewModal{{ $submission->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('instructor.assignments.submissions.review', $submission->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Instructor Review for {{ $submission->student->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Your Review/Feedback</label>
                            <textarea name="instructor_review" class="form-control" rows="5" required {{ $submission->isAssessed() ? 'disabled' : '' }}>{{ $submission->instructor_review }}</textarea>
                            <small class="text-muted mt-2 d-block">This review will be forwarded to the Assessor.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        @if(!$submission->isAssessed())
                            <button type="submit" class="btn btn-primary">Save Review & Forward</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endif
@endsection
