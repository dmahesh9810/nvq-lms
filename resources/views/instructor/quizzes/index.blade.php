@extends('layouts.app')
@section('title', 'Quizzes')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0"><i class="bi bi-patch-question me-2 text-primary"></i>Quizzes</h2>
    <a href="{{ route('instructor.quizzes.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Quiz
    </a>
</div>

@if($quizzes->isEmpty())
    <div class="alert alert-info">No quizzes yet. <a href="{{ route('instructor.quizzes.create') }}">Create one</a>.</div>
@else
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Title</th>
                    <th>Course / Unit</th>
                    <th>Pass Mark</th>
                    <th>Questions</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quizzes as $quiz)
                <tr>
                    <td class="fw-semibold">{{ $quiz->title }}</td>
                    <td>
                        <small class="text-muted">{{ $quiz->unit->module->course->title }}</small><br>
                        {{ $quiz->unit->title }}
                    </td>
                    <td><span class="badge bg-warning text-dark">{{ $quiz->pass_mark }}%</span></td>
                    <td><span class="badge bg-info text-dark">{{ $quiz->questions_count }}</span></td>
                    <td>
                        <span class="badge {{ $quiz->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $quiz->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('instructor.quizzes.questions', $quiz) }}" class="btn btn-sm btn-outline-primary" title="Manage Questions">
                            <i class="bi bi-list-check"></i>
                        </a>
                        <a href="{{ route('instructor.quizzes.edit', $quiz) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('instructor.quizzes.destroy', $quiz) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Delete this quiz and all its questions?')">
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
<div class="mt-3">{{ $quizzes->links() }}</div>
@endif
@endsection
