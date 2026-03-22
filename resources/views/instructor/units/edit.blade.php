@extends('layouts.app')
@section('title', 'Edit Unit') @section('page-title', 'Edit Unit')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-7">
<div class="card">
    <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-pencil text-warning me-2"></i>Edit Unit</h6>
        <a href="{{ route('instructor.courses.show', $course) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('instructor.courses.modules.units.update', [$course, $module, $unit]) }}" method="POST">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label fw-medium">Unit Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $unit->title) }}" required>
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-medium">Description</label>
                <textarea name="description" rows="3" class="form-control">{{ old('description', $unit->description) }}</textarea>
            </div>
            
            <hr class="my-4 text-muted opacity-25">
            <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-award me-2"></i>NVQ Structure Data</h6>
            
            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label fw-medium">NVQ Unit Code <span class="text-muted fw-normal small">(Optional)</span></label>
                    <input type="text" name="nvq_unit_code" class="form-control @error('nvq_unit_code') is-invalid @enderror" value="{{ old('nvq_unit_code', $unit->nvq_unit_code) }}" placeholder="e.g. EMV/IT/1/01">
                    @error('nvq_unit_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-medium">NVQ Level</label>
                    <select name="nvq_level" class="form-select @error('nvq_level') is-invalid @enderror">
                        @for($i=1; $i<=7; $i++)
                            <option value="{{ $i }}" {{ old('nvq_level', $unit->nvq_level ?? 4) == $i ? 'selected' : '' }}>Level {{ $i }}</option>
                        @endfor
                    </select>
                    @error('nvq_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-medium">Learning Outcomes</label>
                <textarea name="learning_outcomes" rows="3" class="form-control @error('learning_outcomes') is-invalid @enderror" placeholder="List the learning outcomes...">{{ old('learning_outcomes', $unit->learning_outcomes) }}</textarea>
                @error('learning_outcomes')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-medium">Performance Criteria</label>
                <textarea name="performance_criteria" rows="3" class="form-control @error('performance_criteria') is-invalid @enderror" placeholder="List the performance criteria...">{{ old('performance_criteria', $unit->performance_criteria) }}</textarea>
                @error('performance_criteria')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-medium">Assessment Criteria</label>
                <textarea name="assessment_criteria" rows="3" class="form-control @error('assessment_criteria') is-invalid @enderror" placeholder="List the assessment criteria...">{{ old('assessment_criteria', $unit->assessment_criteria) }}</textarea>
                @error('assessment_criteria')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <hr class="my-4 text-muted opacity-25">
            <div class="mb-3">
                <label class="form-label fw-medium">Order</label>
                <input type="number" name="order" class="form-control" value="{{ old('order', $unit->order) }}" min="0">
            </div>
            <div class="mb-4 form-check">
                <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" {{ old('is_active', $unit->is_active) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-warning px-4"><i class="bi bi-check-circle me-2"></i>Update Unit</button>
                <a href="{{ route('instructor.courses.show', $course) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection
