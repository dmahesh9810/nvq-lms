@extends('layouts.app')
@section('title', 'TVEC Verification Logs')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0"><i class="bi bi-shield-check me-2 text-success"></i>TVEC Verification Logs</h2>
        <p class="text-muted mb-0">Audit trail of all Assessor actions verifing Instructor assessments.</p>
    </div>
</div>

@if($logs->isEmpty())
    <div class="alert alert-info">No verification logs are recorded yet.</div>
@else
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Date & Time</th>
                        <th>Assessor (Verifier)</th>
                        <th>Instructor (Evaluator)</th>
                        <th>Student</th>
                        <th>Course / Assignment</th>
                        <th>Action</th>
                        <th>Verification Note</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr>
                        <td><small>{{ $log->created_at->format('d M Y H:i') }}</small></td>
                        <td class="fw-semibold text-primary">{{ $log->assessor->name }}</td>
                        <td class="fw-semibold">{{ $log->instructor->name }}</td>
                        <td>{{ $log->submission->student->name ?? 'Unknown Student' }}</td>
                        <td>
                            <div>{{ $log->submission->assignment->title ?? 'N/A' }}</div>
                            <small class="text-muted">{{ $log->submission->assignment->unit->module->course->title ?? 'N/A' }}</small>
                        </td>
                        <td>
                            @if($log->action === 'verify')
                                <span class="badge bg-success">Verified</span>
                            @else
                                <span class="badge bg-danger">Rejected</span>
                            @endif
                        </td>
                        <td><small class="text-muted">{{ Str::limit($log->note, 40) }}</small></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="mt-4">{{ $logs->links() }}</div>
@endif
@endsection
