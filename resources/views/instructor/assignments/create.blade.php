@extends('layouts.app')
@section('title', 'Create Assignment')
@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="d-flex align-items-center mb-4">
    <a href="{{ route('instructor.assignments.index') }}" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
    <h2 class="fw-bold mb-0">Create Assignment</h2>
</div>
<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form action="{{ route('instructor.assignments.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Unit <span class="text-danger">*</span></label>
                <select name="unit_id" class="form-select @error('unit_id') is-invalid @enderror" required>
                    <option value="">— Select Unit —</option>
                    @foreach($units as $unit)
                    <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                        {{ $unit->module->course->title }} → {{ $unit->module->title }} → {{ $unit->title }}
                    </option>
                    @endforeach
                </select>
                @error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                       value="{{ old('title') }}" placeholder="Assignment title" required>
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Description</label>
                <textarea name="description" class="form-control" rows="4"
                          placeholder="Instructions for students...">{{ old('description') }}</textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Due Date</label>
                    <input type="datetime-local" name="due_date" class="form-control"
                           value="{{ old('due_date') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Max Marks</label>
                    <input type="number" name="max_marks" class="form-control"
                           value="{{ old('max_marks') }}" min="1" placeholder="e.g. 100">
                </div>
            </div>
            <div class="form-check form-switch mb-4">
                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                       id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Active (visible to students)</label>
            </div>
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-lg me-1"></i> Create Assignment
            </button>
        </form>
    </div>
</div>
</div>
</div>
@endsection
