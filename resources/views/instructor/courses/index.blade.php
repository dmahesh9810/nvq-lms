@extends('layouts.app')

@section('title', 'My Courses')
@section('page-title', 'Course Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-1 fw-bold">My Courses</h5>
        <p class="text-muted mb-0 small">Manage your courses, modules, units, and lessons.</p>
    </div>
    <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>New Course
    </a>
</div>

@if ($courses->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-journal-x text-muted" style="font-size: 3rem;"></i>
            <h5 class="mt-3">No courses yet</h5>
            <p class="text-muted">Create your first course to get started.</p>
            <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary btn-sm">Create Course</a>
        </div>
    </div>
@else
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Course</th>
                            <th>Status</th>
                            <th>Students</th>
                            <th>Created</th>
                            <th class="pe-4 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($courses as $course)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-medium">{{ $course->title }}</div>
                                <small class="text-muted">{{ Str::limit($course->description, 60) }}</small>
                            </td>
                            <td>
                                @php $sc = ['draft'=>'secondary','published'=>'success','archived'=>'dark']; @endphp
                                <span class="badge bg-{{ $sc[$course->status] ?? 'secondary' }}">{{ ucfirst($course->status) }}</span>
                            </td>
                            <td>{{ $course->enrollments_count }}</td>
                            <td class="text-muted small">{{ $course->created_at->format('d M Y') }}</td>
                            <td class="pe-4 text-end">
                                <a href="{{ route('instructor.courses.show', $course) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Manage
                                </a>
                                <a href="{{ route('instructor.courses.edit', $course) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('instructor.courses.destroy', $course) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this course? This cannot be undone.')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer px-4 py-3">
            {{ $courses->links() }}
        </div>
    </div>
@endif
@endsection
