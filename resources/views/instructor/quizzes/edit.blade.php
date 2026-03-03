@extends('layouts.app')
@section('title', 'Edit Quiz')
@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="d-flex align-items-center mb-4">
    <a href="{{ route('instructor.quizzes.index') }}" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
    <h2 class="fw-bold mb-0">Edit Quiz</h2>
</div>
<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form action="{{ route('instructor.quizzes.update', $quiz) }}" method="POST">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label fw-semibold">Unit <span class="text-danger">*</span></label>
                <select name="unit_id" class="form-select" required>
                    @foreach($units as $unit)
                    <option value="{{ $unit->id }}" {{ $quiz->unit_id == $unit->id ? 'selected' : '' }}>
                        {{ $unit->module->course->title }} → {{ $unit->module->title }} → {{ $unit->title }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $quiz->title) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $quiz->description) }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Pass Mark (%)</label>
                <input type="number" name="pass_mark" class="form-control"
                       value="{{ old('pass_mark', $quiz->pass_mark) }}" min="1" max="100" required>
            </div>
            <div class="form-check form-switch mb-4">
                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                       id="is_active" {{ $quiz->is_active ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i>Update</button>
                <a href="{{ route('instructor.quizzes.questions', $quiz) }}" class="btn btn-outline-primary">
                    <i class="bi bi-list-check me-1"></i>Manage Questions
                </a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection
