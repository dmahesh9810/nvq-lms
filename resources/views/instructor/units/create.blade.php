@extends('layouts.app')
@section('title', 'Add Unit') @section('page-title', 'Add Unit')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-7">
<div class="card">
    <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-folder2-open text-warning me-2"></i>New Unit — {{ $module->title }}</h6>
        <a href="{{ route('instructor.courses.show', $course) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('instructor.courses.modules.units.store', [$course, $module]) }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-medium">Unit Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-medium">Description</label>
                <textarea name="description" rows="3" class="form-control">{{ old('description') }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label fw-medium">Order</label>
                <input type="number" name="order" class="form-control" value="{{ old('order', 0) }}" min="0">
            </div>
            <div class="mb-4 form-check">
                <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-plus-circle me-2"></i>Add Unit</button>
                <a href="{{ route('instructor.courses.show', $course) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection
