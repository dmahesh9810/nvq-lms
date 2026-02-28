@extends('layouts.app')

@section('title', 'Edit: ' . $course->title)
@section('page-title', 'Edit Course')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-pencil text-warning me-2"></i>Edit Course</h6>
                <a href="{{ route('instructor.courses.show', $course) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
            </div>
            <div class="card-body px-4 py-4">
                <form action="{{ route('instructor.courses.update', $course) }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-medium">Course Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title', $course->title) }}" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Slug</label>
                        <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
                               value="{{ old('slug', $course->slug) }}">
                        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Description</label>
                        <textarea name="description" rows="4"
                                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $course->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Thumbnail</label>
                        @if ($course->thumbnail)
                            <div class="mb-2">
                                <img src="{{ asset('storage/'.$course->thumbnail) }}" alt="Current thumbnail"
                                     style="height:80px; border-radius:8px; object-fit:cover;">
                                <small class="text-muted ms-2">Current thumbnail</small>
                            </div>
                        @endif
                        <input type="file" name="thumbnail" class="form-control @error('thumbnail') is-invalid @enderror" accept="image/*">
                        @error('thumbnail')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-medium">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            @foreach (['draft','published','archived'] as $s)
                            <option value="{{ $s }}" {{ old('status', $course->status) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning px-4">
                            <i class="bi bi-check-circle me-2"></i>Update Course
                        </button>
                        <a href="{{ route('instructor.courses.show', $course) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
