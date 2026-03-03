@extends('layouts.app')
@section('title', 'My Assignments')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0"><i class="bi bi-journal-text me-2 text-primary"></i>My Assignments</h2>
</div>

@if($assignments->isEmpty())
    <div class="alert alert-info">No assignments available yet. Enroll in courses to see assignments.</div>
@else
<div class="row g-3">
    @foreach($assignments as $assignment)
    @php $submission = $submissions->get($assignment->id); @endphp
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body">
                <h6 class="fw-bold">{{ $assignment->title }}</h6>
                <p class="text-muted small mb-1">
                    <i class="bi bi-book me-1"></i>{{ $assignment->unit->module->course->title }}
                </p>
                <p class="text-muted small mb-2">
                    <i class="bi bi-grid me-1"></i>{{ $assignment->unit->title }}
                </p>

                @if($assignment->due_date)
                <p class="small mb-2">
                    <i class="bi bi-calendar me-1"></i>
                    <span class="{{ now()->gt($assignment->due_date) ? 'text-danger' : 'text-muted' }}">
                        Due: {{ $assignment->due_date->format('d M Y H:i') }}
                    </span>
                </p>
                @endif

                {{-- Submission Status --}}
                @if($submission)
                    @if($submission->result)
                        {{-- NVQ Competency Result --}}
                        <div class="alert alert-{{ $submission->result->competencyBadge() === 'success' ? 'success' : 'danger' }} py-2 mb-2">
                            <strong class="fs-5">{{ $submission->result->competencyLabel() }}</strong>
                            — {{ $submission->result->competency_status === 'competent' ? 'Competent' : 'Not Yet Competent' }}
                        </div>
                    @else
                        <span class="badge bg-info text-dark mb-2">{{ ucfirst($submission->status) }}</span>
                    @endif
                @else
                    <span class="badge bg-warning text-dark mb-2">Not Submitted</span>
                @endif
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="{{ route('student.assignments.show', $assignment) }}" class="btn btn-primary btn-sm w-100">
                    {{ $submission ? 'View / Resubmit' : 'Start Assignment' }}
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>
<div class="mt-4">{{ $assignments->links() }}</div>
@endif
@endsection
