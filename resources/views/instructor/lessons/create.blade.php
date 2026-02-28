@extends('layouts.app')
@section('title', 'Add Lesson') @section('page-title', 'Add Lesson')

@push('styles')
<!-- Quill.js rich text editor -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<style>
    #quill-editor { min-height: 200px; background: #fff; }
    .ql-toolbar { border-radius: 8px 8px 0 0 !important; }
    .ql-container { border-radius: 0 0 8px 8px !important; font-size: 0.9rem; }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
<div class="col-lg-9">
<div class="card">
    <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-camera-video text-danger me-2"></i>New Lesson — {{ $unit->title }}</h6>
        <a href="{{ route('instructor.courses.show', $course) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('instructor.courses.modules.units.lessons.store', [$course, $module, $unit]) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row g-3 mb-3">
                <div class="col-md-8">
                    <label class="form-label fw-medium">Lesson Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title') }}" required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Lesson Type <span class="text-danger">*</span></label>
                    <select name="type" class="form-select @error('type') is-invalid @enderror">
                        @foreach (['text'=>'Text / Article','video'=>'Video (YouTube)','pdf'=>'PDF Document','mixed'=>'Mixed Content'] as $val => $label)
                        <option value="{{ $val }}" {{ old('type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-medium">Short Description</label>
                <textarea name="description" rows="2" class="form-control">{{ old('description') }}</textarea>
            </div>

            {{-- Video URL --}}
            <div class="mb-3">
                <label class="form-label fw-medium">
                    <i class="bi bi-youtube text-danger me-2"></i>YouTube / Video URL
                </label>
                <input type="url" name="video_url" class="form-control @error('video_url') is-invalid @enderror"
                       value="{{ old('video_url') }}" placeholder="https://www.youtube.com/watch?v=...">
                <div class="form-text">Supports youtube.com/watch?v= and youtu.be/ formats.</div>
                @error('video_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- PDF Upload --}}
            <div class="mb-3">
                <label class="form-label fw-medium">
                    <i class="bi bi-file-earmark-pdf text-danger me-2"></i>PDF Document
                    <span class="text-muted small">(max 10MB)</span>
                </label>
                <input type="file" name="pdf_file" class="form-control @error('pdf_file') is-invalid @enderror" accept=".pdf">
                @error('pdf_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Rich Text Content --}}
            <div class="mb-3">
                <label class="form-label fw-medium">
                    <i class="bi bi-file-richtext me-2 text-info"></i>Rich Text Content
                </label>
                <div id="quill-editor">{!! old('content') !!}</div>
                {{-- Hidden input to submit Quill content --}}
                <input type="hidden" name="content" id="content-input">
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-medium">Order</label>
                    <input type="number" name="order" class="form-control" value="{{ old('order', 0) }}" min="0">
                </div>
                <div class="col-md-8 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active (visible to enrolled students)</label>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-plus-circle me-2"></i>Create Lesson</button>
                <a href="{{ route('instructor.courses.show', $course) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
    const quill = new Quill('#quill-editor', {
        theme: 'snow',
        placeholder: 'Write lesson content here...',
        modules: {
            toolbar: [
                [{ 'header': [1,2,3,false] }],
                ['bold','italic','underline','strike'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                ['blockquote','code-block'],
                ['link'],
                ['clean']
            ]
        }
    });

    // Sync Quill content to hidden input on form submit
    document.querySelector('form').addEventListener('submit', function() {
        document.getElementById('content-input').value = quill.root.innerHTML;
    });
</script>
@endpush
