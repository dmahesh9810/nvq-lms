@extends('layouts.app')
@section('title', 'Student Dashboard')
@section('page-title', 'Student Dashboard')

@section('content')

    <div class="row g-4 mb-4">
        {{-- Enrolled Courses --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card" style="background:#e8f0fe;">
                <div class="stat-icon bg-white text-soft-blue"><i class="bi bi-collection-play-fill"></i></div>
                <div class="stat-value text-dark">{{ $courses->count() }}</div>
                <div class="stat-label text-muted">Enrolled Courses</div>
            </div>
        </div>

        {{-- Lessons Completed --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card" style="background:#e6f4ea;">
                <div class="stat-icon bg-white text-soft-green"><i class="bi bi-play-btn-fill"></i></div>
                <div class="stat-value text-dark">
                    {{ $totalLessonsCompleted }} <span class="fs-6 text-muted font-normal">/ {{ $totalLessons }}</span>
                </div>
                <div class="stat-label text-muted">Lessons Completed</div>
            </div>
        </div>

        {{-- Quizzes Passed --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card" style="background:#e4f4f2;">
                <div class="stat-icon bg-white text-soft-teal"><i class="bi bi-patch-question-fill"></i></div>
                <div class="stat-value text-dark">
                    {{ $totalQuizzesPassed }} <span class="fs-6 text-muted font-normal">/ {{ $totalQuizzes }}</span>
                </div>
                <div class="stat-label text-muted">Quizzes Passed</div>
            </div>
        </div>

        {{-- Certificates Earned --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card" style="background:#fef8e1;">
                <div class="stat-icon bg-white text-soft-amber"><i class="bi bi-award-fill"></i></div>
                <div class="stat-value text-dark">{{ $certificates->count() }}</div>
                <div class="stat-label text-muted">Certificates Earned</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Left Column (Wider: My Courses & Quiz History) --}}
        <div class="col-lg-8 space-y-4">
            
            {{-- My Courses --}}
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between py-3 px-4">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-journal-bookmark-fill text-primary me-2"></i>My Learning Path</h6>
                </div>
                <div class="card-body px-4 py-3">
                    @if($courses->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-search fs-1 mb-3 d-block"></i>
                            <p class="mb-0">You are not enrolled in any courses yet.</p>
                            <a href="{{ route('student.courses.browse') }}" class="btn btn-sm btn-outline-primary mt-3">Browse Catalog</a>
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($courses as $course)
                                @php $progress = $courseProgress[$course->id] ?? 0; @endphp
                                <div class="list-group-item px-0 py-3 border-bottom {{ $loop->last ? 'border-0 pb-0' : '' }}">
                                    <div class="row align-items-center g-3">
                                        <div class="col-md-7">
                                            <a href="{{ route('student.courses.show', $course) }}" class="text-decoration-none fw-bold fs-6 text-primary user-select-none">
                                                {{ $course->title }}
                                            </a>
                                            <div class="text-muted small mt-1">
                                                <i class="bi bi-layers me-1"></i>{{ $course->modules->count() }} Modules
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="small fw-semibold {{ $progress == 100 ? 'text-success' : 'text-primary' }}">{{ $progress }}% Completed</span>
                                            </div>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar {{ $progress == 100 ? 'bg-success' : 'bg-primary' }}" role="progressbar" style="width: {{ $progress }}%" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Quiz Results --}}
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between py-3 px-4">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-clipboard-data text-info me-2"></i>Recent Quiz Results</h6>
                </div>
                <div class="card-body p-0">
                    @if($quizAttempts->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-clock-history fs-1 mb-3 d-block"></i>
                            <p class="mb-0">No quizzes attempted yet.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover table-borderless align-middle mb-0">
                                <thead class="table-light text-muted small text-uppercase">
                                    <tr>
                                        <th class="ps-4 fw-medium">Quiz</th>
                                        <th class="text-center fw-medium">Score</th>
                                        <th class="text-center fw-medium">Result</th>
                                        <th class="text-end pe-4 fw-medium">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($quizAttempts->take(5) as $attempt)
                                        <tr class="border-bottom">
                                            <td class="ps-4 py-3">
                                                <a href="{{ route('student.quizzes.result', [$attempt->quiz_id, $attempt->id]) }}" class="text-decoration-none fw-medium text-dark">
                                                    {{ $attempt->quiz->title }}
                                                </a>
                                            </td>
                                            <td class="text-center py-3 fw-bold">
                                                {{ $attempt->score }} <span class="fw-normal text-muted small">marks</span>
                                            </td>
                                            <td class="text-center py-3">
                                                @if($attempt->result === 'PASS')
                                                    <span class="badge bg-soft-green text-success px-2 py-1 rounded-pill">PASS</span>
                                                @else
                                                    <span class="badge bg-soft-red text-danger px-2 py-1 rounded-pill">FAIL</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-4 py-3 text-muted small">
                                                {{ $attempt->completed_at->format('M j, Y') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- Right Column (Narrower: Certificates) --}}
        <div class="col-lg-4">
            
            {{-- Certificates Panel --}}
            <div class="card h-100 border-top border-4 border-warning">
                <div class="card-header d-flex align-items-center justify-content-between py-3 px-4">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-award-fill text-warning me-2"></i>My Certificates</h6>
                </div>
                <div class="card-body px-4 py-3">
                    @if($certificates->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-patch-exclamation fs-1 mb-3 d-block text-warning opacity-50"></i>
                            <p class="mb-0 small">Complete a course to earn your first certificate!</p>
                        </div>
                    @else
                        <div class="list-group list-group-flush gap-3 mt-2">
                            @foreach($certificates as $cert)
                                <div class="list-group-item bg-light border-0 rounded-3 p-3">
                                    <div class="mb-2">
                                        <h6 class="fw-bold text-dark text-truncate mb-1" title="{{ $cert->course->title }}">{{ $cert->course->title }}</h6>
                                        <div class="text-muted small">Issued: {{ $cert->issued_at->format('M j, Y') }}</div>
                                        <div class="font-monospace text-muted small" style="font-size: 0.75rem;">ID: {{ $cert->certificate_number }}</div>
                                    </div>
                                    
                                    <div class="mt-3 d-flex align-items-center justify-content-between">
                                        @if($cert->status === 'active')
                                            <span class="badge bg-soft-green text-success rounded-pill px-2">Active</span>
                                            <a href="{{ route('student.certificates.download', $cert) }}" class="btn btn-sm btn-outline-primary py-1 px-2" style="font-size: 0.75rem;">
                                                <i class="bi bi-download me-1"></i> PDF
                                            </a>
                                        @else
                                            <span class="badge bg-soft-red text-danger rounded-pill px-2">Revoked</span>
                                            <span class="small text-muted fst-italic">Unavailable</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>

    </div>
@endsection
