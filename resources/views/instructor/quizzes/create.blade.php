@extends('layouts.app')
@section('title', 'Create Quiz')
@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="d-flex align-items-center mb-4">
    <a href="{{ route('instructor.quizzes.index') }}" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
    <h2 class="fw-bold mb-0">Create Quiz</h2>
</div>
<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form action="{{ route('instructor.quizzes.store') }}" method="POST">
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
                       value="{{ old('title') }}" required>
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Pass Mark (%) <span class="text-danger">*</span></label>
                <input type="number" name="pass_mark" class="form-control @error('pass_mark') is-invalid @enderror"
                       value="{{ old('pass_mark', 50) }}" min="1" max="100" required>
                <div class="form-text">Students must score at least this percentage to pass.</div>
                @error('pass_mark')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-check form-switch mb-4">
                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                       id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Active (visible to students)</label>
            </div>
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-arrow-right-circle me-1"></i> Create & Add Questions
            </button>
        </form>
    </div>
</div>
</div>
</div>
@endsection
