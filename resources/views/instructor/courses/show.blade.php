@extends('layouts.app')

@section('title', $course->title . ' — Manage')
@section('page-title', 'Course Structure')

@section('content')
@php
    $isAdmin         = auth()->user()->isAdmin();
    $isCourseLvlAdmin = $isAdmin || 
                        $course->instructor_id == auth()->id() || 
                        $course->assignedInstructors->contains('id', auth()->id());

    // Build a map of pending change requests for this course's resources.
    // Key: "type:target_id:action" => ChangeRequest
    $myPendingRequests = \App\Models\ChangeRequest::where('user_id', auth()->id())
        ->where('status', 'pending')
        ->whereIn('type', ['course', 'module', 'unit', 'lesson'])
        ->get()
        ->keyBy(fn($r) => "{$r->type}:{$r->target_id}:{$r->action}");

    $courseEditPending   = $myPendingRequests->has("course:{$course->id}:update");
    $courseDeletePending = $myPendingRequests->has("course:{$course->id}:delete");
@endphp

<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
    <div>
        <h5 class="fw-bold mb-1">{{ $course->title }}</h5>
        @php $sc = ['draft'=>'secondary','pending'=>'warning','published'=>'success','rejected'=>'danger','archived'=>'dark']; @endphp
        <span class="badge bg-{{ $sc[$course->status] ?? 'secondary' }} me-2">{{ ucfirst($course->status) }}</span>
        <span class="text-muted small">{{ $course->enrollments()->count() }} students enrolled</span>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        {{-- Submit for Review (instructors + admins on draft/rejected) --}}
        @if($isCourseLvlAdmin)
            @if($course->status === 'draft' || $course->status === 'rejected')
            <form action="{{ route('instructor.courses.submit', $course) }}" method="POST">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Submit this course for Admin approval? You will not be able to edit it while pending.')">
                    <i class="bi bi-send me-1"></i>Submit for Review
                </button>
            </form>
            @endif
        @endif

        @if($course->status !== 'pending')
            @if($isAdmin)
                {{-- Admin: direct edit button --}}
                <a href="{{ route('instructor.courses.edit', $course) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-pencil me-1"></i>Edit Course
                </a>
                {{-- Admin: direct delete button --}}
                <form action="{{ route('instructor.courses.destroy', $course) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Permanently delete this course and all its content?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i>Delete Course</button>
                </form>
            @elseif($isCourseLvlAdmin)
                {{-- Instructor: Request Edit --}}
                @if($courseEditPending)
                    <span class="btn btn-outline-secondary btn-sm disabled" title="Pending admin review">
                        <i class="bi bi-hourglass-split me-1 text-warning"></i>Edit Pending…
                    </span>
                @else
                    <button type="button" class="btn btn-outline-secondary btn-sm"
                            data-bs-toggle="modal" data-bs-target="#requestEditCourseModal">
                        <i class="bi bi-pencil-square me-1"></i>Request Edit
                    </button>
                @endif

                {{-- Instructor: Request Delete --}}
                @if($courseDeletePending)
                    <span class="btn btn-outline-danger btn-sm disabled" title="Pending admin review">
                        <i class="bi bi-hourglass-split me-1 text-warning"></i>Delete Pending…
                    </span>
                @else
                    <button type="button" class="btn btn-outline-danger btn-sm"
                            data-bs-toggle="modal" data-bs-target="#requestDeleteCourseModal">
                        <i class="bi bi-trash me-1"></i>Request Delete
                    </button>
                @endif
            @endif

            @if($isAdmin)
            <button type="button" class="btn btn-info text-white btn-sm" data-bs-toggle="modal" data-bs-target="#assignCourseInstructorsModal">
                <i class="bi bi-people me-1"></i>Manage Instructors
            </button>
            <a href="{{ route('instructor.courses.modules.create', $course) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i>Add Module
            </a>
            @endif
        @endif
    </div>
</div>

{{-- Module / Unit / Lesson Tree --}}
@if ($course->modules->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-diagram-3" style="font-size:2.5rem;"></i>
            <p class="mt-3 mb-2">No modules yet. Add your first module to start building this course.</p>
            @if($isAdmin)
            <a href="{{ route('instructor.courses.modules.create', $course) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus me-1"></i>Add Module
            </a>
            @endif
        </div>
    </div>
@else
    @foreach ($course->modules as $module)
    @php
        $isModuleAssigned      = $module->assignedInstructors->contains('id', auth()->id());
        $modEditPending        = $myPendingRequests->has("module:{$module->id}:update");
        $modDeletePending      = $myPendingRequests->has("module:{$module->id}:delete");
    @endphp
    @if($isCourseLvlAdmin || $isModuleAssigned)
    <div class="card mb-3">
        <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-collection text-primary me-2"></i>
                <span class="fw-semibold">Module {{ $loop->iteration }}: {{ $module->title }}</span>
                @unless($module->is_active)<span class="badge bg-secondary ms-2 small">Inactive</span>@endunless
            </div>
            <div class="d-flex gap-2 flex-wrap">
                @if($isAdmin)
                    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#assignModuleInstructorsModal{{ $module->id }}">
                        <i class="bi bi-people"></i> Assign
                    </button>
                    <a href="{{ route('instructor.courses.modules.units.create', [$course, $module]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-plus me-1"></i>Add Unit
                    </a>
                    {{-- Admin: direct edit & delete --}}
                    <a href="{{ route('instructor.courses.modules.edit', [$course, $module]) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="{{ route('instructor.courses.modules.destroy', [$course, $module]) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Delete this module and all its content?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                @else
                    {{-- Instructor: Request Edit Module --}}
                    @if($modEditPending)
                        <span class="btn btn-sm btn-outline-secondary disabled" title="Edit request pending">
                            <i class="bi bi-hourglass-split text-warning"></i>
                        </span>
                    @else
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                data-bs-toggle="modal" data-bs-target="#requestEditModuleModal{{ $module->id }}"
                                title="Request Edit">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                    @endif

                    {{-- Instructor: Request Delete Module --}}
                    @if($modDeletePending)
                        <span class="btn btn-sm btn-outline-danger disabled" title="Delete request pending">
                            <i class="bi bi-hourglass-split text-warning"></i>
                        </span>
                    @else
                        <button type="button" class="btn btn-sm btn-outline-danger"
                                data-bs-toggle="modal" data-bs-target="#requestDeleteModuleModal{{ $module->id }}"
                                title="Request Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    @endif
                @endif
            </div>
        </div>

        @if($module->units->isNotEmpty())
        <div class="card-body px-4 py-2">
            @foreach ($module->units as $unit)
            @php
                $unitEditPending   = $myPendingRequests->has("unit:{$unit->id}:update");
                $unitDeletePending = $myPendingRequests->has("unit:{$unit->id}:delete");
            @endphp
            <div class="border rounded-3 mb-2 p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <i class="bi bi-folder2-open text-warning me-2"></i>
                        <span class="fw-medium">Unit {{ $loop->iteration }}: {{ $unit->title }}</span>
                        @unless($unit->is_active)<span class="badge bg-secondary ms-2 small">Inactive</span>@endunless
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        @if($isAdmin)
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
                        @else
                            {{-- Instructor: Request Edit Unit --}}
                            @if($unitEditPending)
                                <span class="btn btn-sm btn-outline-secondary disabled" title="Edit request pending">
                                    <i class="bi bi-hourglass-split text-warning"></i>
                                </span>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                        data-bs-toggle="modal" data-bs-target="#requestEditUnitModal{{ $unit->id }}"
                                        title="Request Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                            @endif

                            {{-- Instructor: Request Delete Unit --}}
                            @if($unitDeletePending)
                                <span class="btn btn-sm btn-outline-danger disabled" title="Delete request pending">
                                    <i class="bi bi-hourglass-split text-warning"></i>
                                </span>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                        data-bs-toggle="modal" data-bs-target="#requestDeleteUnitModal{{ $unit->id }}"
                                        title="Request Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- Lessons list --}}
                @if($unit->lessons->isNotEmpty())
                <ul class="list-group list-group-flush mt-2 ms-3">
                    @foreach ($unit->lessons as $lesson)
                    @php
                        $lessonEditPending   = $myPendingRequests->has("lesson:{$lesson->id}:update");
                        $lessonDeletePending = $myPendingRequests->has("lesson:{$lesson->id}:delete");
                    @endphp
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
                            @if($isAdmin)
                                <a href="{{ route('instructor.courses.modules.units.lessons.edit', [$course, $module, $unit, $lesson]) }}" class="btn btn-xs btn-outline-secondary" style="font-size:0.75rem; padding:2px 8px;">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('instructor.courses.modules.units.lessons.destroy', [$course, $module, $unit, $lesson]) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete lesson?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-outline-danger" style="font-size:0.75rem; padding:2px 8px;"><i class="bi bi-trash"></i></button>
                                </form>
                            @else
                                {{-- Instructor: Request Edit Lesson --}}
                                @if($lessonEditPending)
                                    <span class="btn btn-xs btn-outline-secondary disabled" style="font-size:0.75rem; padding:2px 8px;" title="Edit request pending">
                                        <i class="bi bi-hourglass-split text-warning"></i>
                                    </span>
                                @else
                                    <button type="button"
                                            class="btn btn-xs btn-outline-secondary"
                                            style="font-size:0.75rem; padding:2px 8px;"
                                            data-bs-toggle="modal"
                                            data-bs-target="#requestEditLessonModal{{ $lesson->id }}"
                                            title="Request Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                @endif

                                {{-- Instructor: Request Delete Lesson --}}
                                @if($lessonDeletePending)
                                    <span class="btn btn-xs btn-outline-danger disabled" style="font-size:0.75rem; padding:2px 8px;" title="Delete request pending">
                                        <i class="bi bi-hourglass-split text-warning"></i>
                                    </span>
                                @else
                                    <button type="button"
                                            class="btn btn-xs btn-outline-danger"
                                            style="font-size:0.75rem; padding:2px 8px;"
                                            data-bs-toggle="modal"
                                            data-bs-target="#requestDeleteLessonModal{{ $lesson->id }}"
                                            title="Request Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @endif
                            @endif
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
    @endif
    @endforeach
@endif

{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
{{-- Instructor Pending Requests section (non-admin instructors only)            --}}
{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
@if(! $isAdmin && $isCourseLvlAdmin)
@php
    $courseRelatedPending = \App\Models\ChangeRequest::where('user_id', auth()->id())
        ->where('status', 'pending')
        ->get()
        ->filter(function($r) use ($course) {
            if ($r->type === 'course') return $r->target_id === $course->id;
            if ($r->type === 'module') return $course->modules->pluck('id')->contains($r->target_id);
            if ($r->type === 'unit')   return $course->modules->flatMap->units->pluck('id')->contains($r->target_id);
            if ($r->type === 'lesson') return $course->modules->flatMap->units->flatMap->lessons->pluck('id')->contains($r->target_id);
            return false;
        });
@endphp
@if($courseRelatedPending->isNotEmpty())
<div class="card mt-4 border-warning">
    <div class="card-header bg-warning bg-opacity-10 py-2 px-4">
        <i class="bi bi-hourglass-split text-warning me-2"></i>
        <span class="fw-semibold">My Pending Change Requests for this Course</span>
        <a href="{{ route('instructor.change-requests.index') }}" class="float-end small text-primary">View All</a>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Type</th>
                    <th>Action</th>
                    <th>Target</th>
                    <th>Submitted</th>
                    <th class="pe-3 text-end">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($courseRelatedPending as $req)
                <tr>
                    <td class="ps-3"><span class="badge bg-secondary">{{ $req->typeLabel() }}</span></td>
                    <td><span class="badge bg-{{ $req->action === 'delete' ? 'danger' : 'primary' }} bg-opacity-75">{{ $req->actionLabel() }}</span></td>
                    <td class="text-muted small">{{ $req->target_title }}</td>
                    <td class="text-muted small">{{ $req->created_at->diffForHumans() }}</td>
                    <td class="pe-3 text-end"><span class="badge bg-warning text-dark">Pending</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endif

{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
{{-- Admin: Course Instructor Modals                                             --}}
{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
@if($isAdmin)
<!-- Course Instructors Modal -->
<div class="modal fade" id="assignCourseInstructorsModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('instructor.courses.instructors.sync', $course) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Course Instructors</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">Select the instructors who should have full access to manage this course.</p>
                    <div class="list-group">
                        @foreach($allInstructors as $inst)
                            @php $isCreator = $course->instructor_id == $inst->id; @endphp
                            <label class="list-group-item d-flex gap-2">
                                <input class="form-check-input flex-shrink-0" type="checkbox" name="instructor_ids[]" value="{{ $inst->id }}" 
                                    {{ $course->assignedInstructors->contains('id', $inst->id) || $isCreator ? 'checked' : '' }}
                                    {{ $isCreator ? 'onclick="return false;"' : '' }}>
                                <span>
                                    {{ $inst->name }}
                                    @if($isCreator) <span class="badge bg-secondary ms-1">Creator</span> @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Module Instructors Modals -->
@foreach($course->modules as $mod)
<div class="modal fade" id="assignModuleInstructorsModal{{ $mod->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('instructor.courses.modules.instructors.sync', [$course, $mod]) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Module Instructors</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">Select the instructors who should have access specifically to this module.</p>
                    <div class="list-group">
                        @foreach($allInstructors as $inst)
                            @php
                                $isCourseLvl = $course->assignedInstructors->contains('id', $inst->id) || $course->instructor_id == $inst->id;
                            @endphp
                            <label class="list-group-item d-flex gap-2 {{ $isCourseLvl ? 'bg-light' : '' }}">
                                <input class="form-check-input flex-shrink-0" type="checkbox" name="instructor_ids[]" value="{{ $inst->id }}" 
                                    {{ $mod->assignedInstructors->contains('id', $inst->id) || $isCourseLvl ? 'checked' : '' }}
                                    {{ $isCourseLvl ? 'onclick="return false;"' : '' }}>
                                <span>
                                    {{ $inst->name }}
                                    @if($isCourseLvl) <span class="badge bg-secondary ms-1">Course Level</span> @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endforeach
@endif {{-- end isAdmin modals --}}

{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
{{-- Instructor Change Request Modals (non-admin only)                           --}}
{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
@if(! $isAdmin)

@if($isCourseLvlAdmin)
{{-- ── Request Edit Course ──────────────────────────────────────────────── --}}
<div class="modal fade" id="requestEditCourseModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('instructor.change-requests.store') }}" method="POST">
            @csrf
            <input type="hidden" name="type" value="course">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="target_id" value="{{ $course->id }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2 text-primary"></i>Request Course Edit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small py-2">Your request will be reviewed by an admin before changes are applied.</div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Title</label>
                        <input type="text" name="payload[title]" class="form-control" value="{{ $course->title }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="payload[description]" class="form-control" rows="3">{{ $course->description }}</textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col">
                            <label class="form-label fw-semibold">Category</label>
                            <input type="text" name="payload[category]" class="form-control" value="{{ $course->category }}">
                        </div>
                        <div class="col">
                            <label class="form-label fw-semibold">Level</label>
                            <select name="payload[level]" class="form-select">
                                @foreach(['beginner','intermediate','advanced'] as $lvl)
                                <option value="{{ $lvl }}" {{ $course->level == $lvl ? 'selected' : '' }}>{{ ucfirst($lvl) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Submit Request</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ── Request Delete Course ────────────────────────────────────────────── --}}
<div class="modal fade" id="requestDeleteCourseModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('instructor.change-requests.store') }}" method="POST">
            @csrf
            <input type="hidden" name="type" value="course">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="target_id" value="{{ $course->id }}">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-trash me-2"></i>Request Course Deletion</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning small py-2">
                        <strong>Warning:</strong> You are requesting permanent deletion of <strong>{{ $course->title }}</strong>. An admin will review this before deletion.
                    </div>
                    <p class="text-muted small mb-0">Once approved, this course and all its modules, units, and lessons will be permanently removed.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-send me-1"></i>Submit Delete Request</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

{{-- ── Per-Module modals ────────────────────────────────────────────────── --}}
@foreach($course->modules as $module)
@php
    $isModuleAssigned = $module->assignedInstructors->contains('id', auth()->id());
@endphp
@if($isCourseLvlAdmin || $isModuleAssigned)

{{-- Request Edit Module --}}
<div class="modal fade" id="requestEditModuleModal{{ $module->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('instructor.change-requests.store') }}" method="POST">
            @csrf
            <input type="hidden" name="type" value="module">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="target_id" value="{{ $module->id }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2 text-primary"></i>Request Module Edit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small py-2">Changes will be applied after admin approval.</div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Title</label>
                        <input type="text" name="payload[title]" class="form-control" value="{{ $module->title }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="payload[description]" class="form-control" rows="2">{{ $module->description }}</textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="payload[is_active]" value="1" {{ $module->is_active ? 'checked' : '' }}>
                        <label class="form-check-label">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Submit Request</button>
                </div>
            </div>
        </form>
    </div>
</div>
{{-- Request Delete Module --}}
<div class="modal fade" id="requestDeleteModuleModal{{ $module->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('instructor.change-requests.store') }}" method="POST">
            @csrf
            <input type="hidden" name="type" value="module">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="target_id" value="{{ $module->id }}">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-trash me-2"></i>Request Module Deletion</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning small py-2 mb-2">
                        You are requesting deletion of module: <strong>{{ $module->title }}</strong>
                    </div>
                    <p class="text-muted small mb-0">Admin will review this before any removal occurs.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-send me-1"></i>Submit Request</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ── Per-Unit modals ──────────────────────────────────────────────────── --}}
@foreach($module->units as $unit)
{{-- Request Edit Unit --}}
<div class="modal fade" id="requestEditUnitModal{{ $unit->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('instructor.change-requests.store') }}" method="POST">
            @csrf
            <input type="hidden" name="type" value="unit">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="target_id" value="{{ $unit->id }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2 text-primary"></i>Request Unit Edit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small py-2">Changes will be applied after admin approval.</div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Title</label>
                        <input type="text" name="payload[title]" class="form-control" value="{{ $unit->title }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="payload[description]" class="form-control" rows="2">{{ $unit->description }}</textarea>
                    </div>

                    <hr class="my-3 text-muted opacity-25">
                    <h6 class="fw-bold text-primary small"><i class="bi bi-award me-1"></i>NVQ Data</h6>
                    
                    <div class="row">
                        <div class="col-md-8 mb-2">
                            <label class="form-label fw-semibold small">NVQ Unit Code</label>
                            <input type="text" name="payload[nvq_unit_code]" class="form-control form-control-sm" value="{{ $unit->nvq_unit_code }}">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label fw-semibold small">NVQ Level</label>
                            <select name="payload[nvq_level]" class="form-select form-select-sm">
                                @for($i=1; $i<=7; $i++)
                                    <option value="{{ $i }}" {{ ($unit->nvq_level ?? 4) == $i ? 'selected' : '' }}>Lvl {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold small">Learning Outcomes</label>
                        <textarea name="payload[learning_outcomes]" rows="2" class="form-control form-control-sm">{{ $unit->learning_outcomes }}</textarea>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold small">Performance Criteria</label>
                        <textarea name="payload[performance_criteria]" rows="2" class="form-control form-control-sm">{{ $unit->performance_criteria }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Assessment Criteria</label>
                        <textarea name="payload[assessment_criteria]" rows="2" class="form-control form-control-sm">{{ $unit->assessment_criteria }}</textarea>
                    </div>
                    <hr class="my-3 text-muted opacity-25">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="payload[is_active]" value="1" {{ $unit->is_active ? 'checked' : '' }}>
                        <label class="form-check-label">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Submit Request</button>
                </div>
            </div>
        </form>
    </div>
</div>
{{-- Request Delete Unit --}}
<div class="modal fade" id="requestDeleteUnitModal{{ $unit->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('instructor.change-requests.store') }}" method="POST">
            @csrf
            <input type="hidden" name="type" value="unit">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="target_id" value="{{ $unit->id }}">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-trash me-2"></i>Request Unit Deletion</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Requesting deletion of unit: <strong>{{ $unit->title }}</strong>. Admin will review before removal.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-send me-1"></i>Submit Request</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ── Per-Lesson modals ────────────────────────────────────────────────── --}}
@foreach($unit->lessons as $lesson)
{{-- Request Edit Lesson --}}
<div class="modal fade" id="requestEditLessonModal{{ $lesson->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('instructor.change-requests.store') }}" method="POST">
            @csrf
            <input type="hidden" name="type" value="lesson">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="target_id" value="{{ $lesson->id }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2 text-primary"></i>Request Lesson Edit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small py-2">Changes will be applied after admin approval.</div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Title</label>
                        <input type="text" name="payload[title]" class="form-control" value="{{ $lesson->title }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Content</label>
                        <textarea name="payload[content]" class="form-control" rows="3">{{ $lesson->content }}</textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col">
                            <label class="form-label fw-semibold">Type</label>
                            <select name="payload[type]" class="form-select">
                                @foreach(['video','pdf','text','mixed'] as $ltype)
                                <option value="{{ $ltype }}" {{ $lesson->type == $ltype ? 'selected' : '' }}>{{ ucfirst($ltype) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col">
                            <label class="form-label fw-semibold">Duration (mins)</label>
                            <input type="number" name="payload[duration]" class="form-control" value="{{ $lesson->duration }}" min="0">
                        </div>
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="payload[is_active]" value="1" {{ $lesson->is_active ? 'checked' : '' }}>
                        <label class="form-check-label">Active / Visible</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Submit Request</button>
                </div>
            </div>
        </form>
    </div>
</div>
{{-- Request Delete Lesson --}}
<div class="modal fade" id="requestDeleteLessonModal{{ $lesson->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('instructor.change-requests.store') }}" method="POST">
            @csrf
            <input type="hidden" name="type" value="lesson">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="target_id" value="{{ $lesson->id }}">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-trash me-2"></i>Request Lesson Deletion</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Requesting deletion of lesson: <strong>{{ $lesson->title }}</strong>. Admin will review before removal.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-send me-1"></i>Submit Request</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endforeach {{-- lessons --}}
@endforeach {{-- units --}}
@endif {{-- end isCourseLvlAdmin || isModuleAssigned --}}
@endforeach {{-- modules --}}

@endif {{-- end non-admin modals --}}
@endsection
