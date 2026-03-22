@extends('layouts.app')
@section('title', 'Grading Queue')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0"><i class="bi bi-check2-square me-2 text-primary"></i>Grading Queue</h2>
        @if($pendingCount > 0)
        <span class="badge bg-danger fs-6 mt-1">{{ $pendingCount }} pending</span>
        @else
        <span class="badge bg-success mt-1">All caught up!</span>
        @endif
    </div>
</div>

{{-- Pending Submissions --}}
<h5 class="fw-semibold mb-3">📋 Pending Submissions</h5>
@if($pending->isEmpty())
    <div class="alert alert-success mb-4"><i class="bi bi-check-circle me-2"></i>No pending submissions at this time.</div>
@else
<div class="card shadow-sm border-0 mb-5">
    <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Student</th>
                    <th>Assignment</th>
                    <th>Course</th>
                    <th>Submitted</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pending as $submission)
                <tr>
                    <td class="fw-semibold">{{ $submission->student->name }}</td>
                    <td>{{ $submission->assignment->title }}</td>
                    <td><small class="text-muted">{{ $submission->assignment->unit->module->course->title }}</small></td>
                    <td><small>{{ $submission->submitted_at->diffForHumans() }}</small></td>
                    <td>
                        <span class="badge bg-{{ $submission->status === 'instructor_assessed' ? 'primary' : 'secondary' }}">
                            Instructor Assessed
                        </span><br>
                        <span class="badge bg-{{ $submission->instructor_competency_status === 'competent' ? 'success' : 'danger' }} mt-1">
                            {{ $submission->instructor_competency_status === 'competent' ? 'C' : 'NYC' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('assessor.grading.show', $submission) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-eye me-1"></i>Verify
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div>{{ $pending->links() }}</div>
@endif

{{-- Recently Verified --}}
<h5 class="fw-semibold mb-3">✅ Recently Verified (by you)</h5>
@if($recentlyGraded->isEmpty())
    <div class="alert alert-info">You have not graded any submissions yet.</div>
@else
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <table class="table mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Student</th>
                    <th>Assignment</th>
                    <th>Instructor Note</th>
                    <th>Verified</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentlyGraded as $submission)
                <tr>
                    <td>{{ $submission->student->name }}</td>
                    <td>{{ $submission->assignment->title }}</td>
                    <td>
                        <span class="badge bg-{{ $submission->instructor_competency_status === 'competent' ? 'success' : 'danger' }} fs-6 px-3 py-2">
                            {{ $submission->instructor_competency_status === 'competent' ? 'Competent' : 'NYC' }}
                        </span>
                        @if($submission->assessor_verification_note)
                        <br><small class="text-muted">{{ Str::limit($submission->assessor_verification_note, 20) }}</small>
                        @endif
                    </td>
                    <td><small class="text-muted">{{ $submission->verified_at->format('d M Y') }}</small></td>
                    <td>
                        <a href="{{ route('assessor.grading.show', $submission) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
