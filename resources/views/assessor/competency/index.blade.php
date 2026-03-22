@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1 text-dark fw-bold">Competency Assessment</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('assessor.progress.index') }}" class="text-decoration-none">Student Progress</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('assessor.progress.detail', [$student, $course]) }}" class="text-decoration-none">{{ $student->name }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Competencies</li>
                    </ol>
                </nav>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="row mb-4 bg-light p-3 rounded mx-0">
                    <div class="col-md-6 mb-2 mb-md-0">
                        <div class="text-muted small text-uppercase fw-semibold mb-1">Student</div>
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px; font-weight: bold; font-size: 1.2rem;">
                                {{ strtoupper(substr($student->name, 0, 1)) }}
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">{{ $student->name }}</h6>
                                <span class="text-muted small">{{ $student->email }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small text-uppercase fw-semibold mb-1">Course</div>
                        <div class="d-flex align-items-center h-100">
                            <h6 class="mb-0 fw-bold text-primary">{{ $course->title }}</h6>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="width: 35%">Module & Unit</th>
                                <th scope="col" style="width: 20%">NVQ Standards</th>
                                <th scope="col" style="width: 15%">Status</th>
                                <th scope="col" style="width: 20%">Remarks</th>
                                <th scope="col" style="width: 10%" class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($course->modules as $module)
                                <tr class="bg-light bg-opacity-50">
                                    <td colspan="5" class="py-2">
                                        <span class="badge bg-secondary me-2">Module</span> <strong>{{ $module->title }}</strong>
                                    </td>
                                </tr>
                                @forelse($module->units as $unit)
                                    @php
                                        // Find competency for this specific student from the eager loaded relation
                                        $competency = $unit->competencyAssessments->first();
                                        $status = $competency ? $competency->status : 'not_assessed';
                                        $statusClass = match($status) {
                                            'competent' => 'success',
                                            'not_competent' => 'danger',
                                            default => 'warning'
                                        };
                                        $statusLabel = match($status) {
                                            'competent' => 'Competent',
                                            'not_competent' => 'Not Yet Competent',
                                            default => 'Not Assessed'
                                        };
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex mb-1">
                                                @if($unit->nvq_unit_code)
                                                    <span class="badge bg-dark me-2">{{ $unit->nvq_unit_code }}</span>
                                                @endif
                                                <span class="fw-semibold text-dark">{{ $unit->title }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @if($unit->learning_outcomes || $unit->performance_criteria)
                                                <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#standardsModal{{ $unit->id }}">
                                                    <i class="bi bi-eye"></i> View Standards
                                                </button>
                                                
                                                <!-- Standards Modal -->
                                                <div class="modal fade" id="standardsModal{{ $unit->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">{{ $unit->title }} - NVQ Level {{ $unit->nvq_level }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                @if($unit->learning_outcomes)
                                                                    <h6>Learning Outcomes</h6>
                                                                    <div class="bg-light p-3 rounded mb-3 text-break" style="white-space: pre-wrap;">{{ $unit->learning_outcomes }}</div>
                                                                @endif
                                                                @if($unit->performance_criteria)
                                                                    <h6>Performance Criteria</h6>
                                                                    <div class="bg-light p-3 rounded mb-3 text-break" style="white-space: pre-wrap;">{{ $unit->performance_criteria }}</div>
                                                                @endif
                                                                @if($unit->assessment_criteria)
                                                                    <h6>Assessment Criteria</h6>
                                                                    <div class="bg-light p-3 rounded mb-0 text-break" style="white-space: pre-wrap;">{{ $unit->assessment_criteria }}</div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted small">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $statusClass }}-subtle text-{{ $statusClass }} rounded-pill px-3 py-2">
                                                @if($status === 'competent') <i class="bi bi-check-circle me-1"></i>
                                                @elseif($status === 'not_competent') <i class="bi bi-x-circle me-1"></i>
                                                @else <i class="bi bi-dash-circle me-1"></i> @endif
                                                {{ $statusLabel }}
                                            </span>
                                            @if($competency && $competency->assessed_at)
                                                <div class="small text-muted mt-1" style="font-size: 11px;">
                                                    {{ $competency->assessed_at->format('M d, Y') }}
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($competency && $competency->remarks)
                                                <span class="d-inline-block text-truncate text-muted small" style="max-width: 150px;" title="{{ $competency->remarks }}">
                                                    {{ $competency->remarks }}
                                                </span>
                                            @else
                                                <span class="text-muted small fst-italic">--</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#assessModal{{ $unit->id }}">
                                                <i class="bi bi-pencil-square"></i> Assess
                                            </button>
                                            
                                            <!-- Assessment Modal -->
                                            <div class="modal fade" id="assessModal{{ $unit->id }}" tabindex="-1" aria-labelledby="assessModalLabel{{ $unit->id }}" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content text-start">
                                                        <form action="{{ route('assessor.competency.update', [$student, $unit]) }}" method="POST">
                                                            @csrf
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="assessModalLabel{{ $unit->id }}">Assess Unit Competency</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <h6 class="fw-bold mb-3">{{ $unit->title }}</h6>
                                                                
                                                                <div class="mb-4">
                                                                    <label class="form-label fw-semibold">Competency Decision <span class="text-danger">*</span></label>
                                                                    
                                                                    <div class="form-check custom-radio border p-3 rounded mb-2 @if($status === 'competent') border-success bg-success-subtle @endif">
                                                                        <input class="form-check-input" type="radio" name="status" id="status_cmp_{{ $unit->id }}" value="competent" required @checked($status === 'competent')>
                                                                        <label class="form-check-label w-100 d-flex justify-content-between align-items-center cursor-pointer" for="status_cmp_{{ $unit->id }}">
                                                                            <span class="fw-bold text-success">Competent</span>
                                                                            <i class="bi bi-check-circle-fill text-success ms-2"></i>
                                                                        </label>
                                                                    </div>
                                                                    
                                                                    <div class="form-check custom-radio border p-3 rounded mb-2 @if($status === 'not_competent') border-danger bg-danger-subtle @endif">
                                                                        <input class="form-check-input" type="radio" name="status" id="status_not_cmp_{{ $unit->id }}" value="not_competent" required @checked($status === 'not_competent')>
                                                                        <label class="form-check-label w-100 d-flex justify-content-between align-items-center cursor-pointer" for="status_not_cmp_{{ $unit->id }}">
                                                                            <span class="fw-bold text-danger">Not Yet Competent</span>
                                                                            <i class="bi bi-x-circle-fill text-danger ms-2"></i>
                                                                        </label>
                                                                    </div>
                                                                    
                                                                    <div class="form-check custom-radio border p-3 rounded @if($status === 'not_assessed') border-warning bg-warning-subtle @endif">
                                                                        <input class="form-check-input" type="radio" name="status" id="status_na_{{ $unit->id }}" value="not_assessed" required @checked($status === 'not_assessed')>
                                                                        <label class="form-check-label w-100 d-flex justify-content-between align-items-center cursor-pointer" for="status_na_{{ $unit->id }}">
                                                                            <span class="fw-bold text-warning-emphasis">Clear Assessment</span>
                                                                            <i class="bi bi-dash-circle text-warning ms-2"></i>
                                                                        </label>
                                                                    </div>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label for="remarks_{{ $unit->id }}" class="form-label fw-semibold">Assessor Remarks (Optional)</label>
                                                                    <textarea class="form-control" name="remarks" id="remarks_{{ $unit->id }}" rows="3" placeholder="Provide feedback or notes...">{{ $competency->remarks ?? '' }}</textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer bg-light">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-primary">Save Assessment</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-3 text-muted">No units found in this module.</td>
                                    </tr>
                                @endforelse
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-journal-x mb-2 d-block" style="font-size: 2rem;"></i>
                                            This course does not have any modules or units yet.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.cursor-pointer { cursor: pointer; }
.custom-radio { transition: all 0.2s; }
.custom-radio:hover { background-color: #f8f9fa; }
</style>
@endsection
