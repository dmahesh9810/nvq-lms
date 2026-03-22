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
                        <span class="badge bg-{{ $submission->status === 'reviewed' ? 'primary' : 'secondary' }}">
                            {{ ucfirst($submission->status) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('assessor.grading.show', $submission) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-eye me-1"></i>Grade
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

{{-- Recently Graded --}}
<h5 class="fw-semibold mb-3">✅ Recently Graded (by you)</h5>
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
                    <th>Result</th>
                    <th>Graded</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentlyGraded as $result)
                <tr>
                    <td>{{ $result->submission->student->name }}</td>
                    <td>{{ $result->submission->assignment->title }}</td>
                    <td>
                        <span class="badge bg-{{ $result->competencyBadge() }} fs-6 px-3 py-2">
                            {{ $result->competencyLabel() }}
                        </span>
                    </td>
                    <td><small class="text-muted">{{ $result->graded_at->format('d M Y') }}</small></td>
                    <td>
                        <a href="{{ route('assessor.grading.show', $result->submission) }}" class="btn btn-sm btn-outline-secondary">
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
