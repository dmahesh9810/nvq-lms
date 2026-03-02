@extends('layouts.app')

@section('title', 'Assignments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0"><i class="bi bi-journal-text me-2 text-primary"></i>Assignments</h2>
    <a href="{{ route('instructor.assignments.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Assignment
    </a>
</div>

@if($assignments->isEmpty())
    <div class="alert alert-info">No assignments yet. <a href="{{ route('instructor.assignments.create') }}">Create one</a>.</div>
@else
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Title</th>
                    <th>Course / Unit</th>
                    <th>Due Date</th>
                    <th>Submissions</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assignments as $assignment)
                <tr>
                    <td class="fw-semibold">{{ $assignment->title }}</td>
                    <td>
                        <small class="text-muted">{{ $assignment->unit->module->course->title }}</small><br>
                        {{ $assignment->unit->title }}
                    </td>
                    <td>
                        @if($assignment->due_date)
                            <span class="badge {{ now()->gt($assignment->due_date) ? 'bg-danger' : 'bg-secondary' }}">
                                {{ $assignment->due_date->format('d M Y') }}
                            </span>
                        @else
                            <span class="text-muted">No deadline</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-info text-dark">{{ $assignment->submissions_count }}</span>
                    </td>
                    <td>
                        <span class="badge {{ $assignment->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $assignment->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('instructor.assignments.submissions', $assignment) }}" class="btn btn-sm btn-outline-info" title="Submissions">
                            <i class="bi bi-people"></i>
                        </a>
                        <a href="{{ route('instructor.assignments.edit', $assignment) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('instructor.assignments.destroy', $assignment) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Delete this assignment?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $assignments->links() }}</div>
@endif
@endsection
