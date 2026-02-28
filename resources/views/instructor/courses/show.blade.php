@extends('layouts.app')

@section('title', $course->title . ' — Manage')
@section('page-title', 'Course Structure')

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
    <div>
        <h5 class="fw-bold mb-1">{{ $course->title }}</h5>
        <span class="badge bg-{{ ['draft'=>'secondary','published'=>'success','archived'=>'dark'][$course->status] ?? 'secondary' }} me-2">{{ ucfirst($course->status) }}</span>
        <span class="text-muted small">{{ $course->enrollments()->count() }} students enrolled</span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('instructor.courses.edit', $course) }}" class="btn btn-outline-warning btn-sm">
            <i class="bi bi-pencil me-1"></i>Edit Course
        </a>
        <a href="{{ route('instructor.courses.modules.create', $course) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i>Add Module
        </a>
    </div>
</div>

{{-- Module / Unit / Lesson Tree --}}
@if ($course->modules->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-diagram-3" style="font-size:2.5rem;"></i>
            <p class="mt-3 mb-2">No modules yet. Add your first module to start building this course.</p>
            <a href="{{ route('instructor.courses.modules.create', $course) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus me-1"></i>Add Module
            </a>
        </div>
    </div>
@else
    @foreach ($course->modules as $module)
    <div class="card mb-3">
        <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-collection text-primary me-2"></i>
                <span class="fw-semibold">Module {{ $loop->iteration }}: {{ $module->title }}</span>
                @unless($module->is_active)<span class="badge bg-secondary ms-2 small">Inactive</span>@endunless
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('instructor.courses.modules.units.create', [$course, $module]) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-plus me-1"></i>Add Unit
                </a>
                <a href="{{ route('instructor.courses.modules.edit', [$course, $module]) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-pencil"></i>
                </a>
                <form action="{{ route('instructor.courses.modules.destroy', [$course, $module]) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Delete this module and all its content?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
            </div>
        </div>

        @if($module->units->isNotEmpty())
        <div class="card-body px-4 py-2">
            @foreach ($module->units as $unit)
            <div class="border rounded-3 mb-2 p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <i class="bi bi-folder2-open text-warning me-2"></i>
                        <span class="fw-medium">Unit {{ $loop->iteration }}: {{ $unit->title }}</span>
                        @unless($unit->is_active)<span class="badge bg-secondary ms-2 small">Inactive</span>@endunless
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('instructor.courses.modules.units.lessons.create', [$course, $module, $unit]) }}" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-plus me-1"></i>Add Lesson
                        </a>
                        <a href="{{ route('instructor.courses.modules.units.edit', [$course, $module, $unit]) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('instructor.courses.modules.units.destroy', [$course, $module, $unit]) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Delete this unit?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </div>

                {{-- Lessons list --}}
                @if($unit->lessons->isNotEmpty())
                <ul class="list-group list-group-flush mt-2 ms-3">
                    @foreach ($unit->lessons as $lesson)
                    <li class="list-group-item border-0 px-0 py-1 d-flex justify-content-between align-items-center">
                        <span>
                            @php
                                $icons = ['video'=>'bi-camera-video text-danger','pdf'=>'bi-file-earmark-pdf text-danger','text'=>'bi-file-text text-info','mixed'=>'bi-layers text-purple'];
                            @endphp
                            <i class="bi {{ $icons[$lesson->type] ?? 'bi-file' }} me-2"></i>
                            <span class="small fw-medium">{{ $lesson->title }}</span>
                            @unless($lesson->is_active)<span class="badge bg-secondary ms-1" style="font-size:0.65rem;">Hidden</span>@endunless
                        </span>
                        <div class="d-flex gap-1">
                            <a href="{{ route('instructor.courses.modules.units.lessons.edit', [$course, $module, $unit, $lesson]) }}" class="btn btn-xs btn-outline-secondary" style="font-size:0.75rem; padding:2px 8px;">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('instructor.courses.modules.units.lessons.destroy', [$course, $module, $unit, $lesson]) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete lesson?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger" style="font-size:0.75rem; padding:2px 8px;"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </li>
                    @endforeach
                </ul>
                @else
                    <p class="text-muted small mt-2 ms-3 mb-0">No lessons yet.</p>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @endforeach
@endif
@endsection
