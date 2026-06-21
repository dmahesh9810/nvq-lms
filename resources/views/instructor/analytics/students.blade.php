@extends('layouts.app')

@section('title', 'Student Intelligence Dashboard')
@section('page-title', 'Student Intelligence Dashboard')

@section('content')
<div class="container-fluid py-4">

    {{-- ── PAGE HEADER ──────────────────────────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">🧠 Student Intelligence</h2>
            <p class="text-muted mb-0">Real-time view of who is learning, who is struggling, and who needs your attention.</p>
        </div>
        <a href="{{ route('instructor.dashboard') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Analytics
        </a>
    </div>

    {{-- ── SUMMARY STAT CARDS ───────────────────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        {{-- Total Students --}}
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body py-3">
                    <div class="fs-1 fw-bold text-primary">{{ $summary['total'] }}</div>
                    <div class="small text-muted">Total Students</div>
                </div>
            </div>
        </div>
        {{-- At Risk --}}
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm text-center h-100" style="border-left: 4px solid #dc3545 !important;">
                <div class="card-body py-3">
                    <div class="fs-1 fw-bold text-danger">{{ $summary['at_risk'] }}</div>
                    <div class="small text-muted">🔴 At Risk</div>
                </div>
            </div>
        </div>
        {{-- Never Active --}}
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm text-center h-100" style="border-left: 4px solid #6c757d !important;">
                <div class="card-body py-3">
                    <div class="fs-1 fw-bold text-secondary">{{ $summary['never_active'] }}</div>
                    <div class="small text-muted">⚫ Never Logged In</div>
                </div>
            </div>
        </div>
        {{-- Learning --}}
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm text-center h-100" style="border-left: 4px solid #ffc107 !important;">
                <div class="card-body py-3">
                    <div class="fs-1 fw-bold text-warning">{{ $summary['learning'] }}</div>
                    <div class="small text-muted">🟡 Learning</div>
                </div>
            </div>
        </div>
        {{-- Mastered --}}
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm text-center h-100" style="border-left: 4px solid #198754 !important;">
                <div class="card-body py-3">
                    <div class="fs-1 fw-bold text-success">{{ $summary['mastered'] }}</div>
                    <div class="small text-muted">🟢 On Track</div>
                </div>
            </div>
        </div>
        {{-- Class Avg Mastery --}}
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm text-center h-100" style="border-left: 4px solid #0d6efd !important;">
                <div class="card-body py-3">
                    <div class="fs-1 fw-bold text-primary">{{ $summary['avg_class_mastery'] }}%</div>
                    <div class="small text-muted">📈 Class Avg</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── PRIORITY ALERT BOX (At Risk Students) ───────────────────────────── --}}
    @php $atRiskStudents = $students->where('status', 'at_risk'); @endphp
    @if($atRiskStudents->count() > 0)
    <div class="alert border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #fff5f5, #ffe0e0); border-left: 4px solid #dc3545 !important;">
        <div class="d-flex align-items-start gap-3">
            <div class="fs-2">⚠️</div>
            <div>
                <h6 class="fw-bold text-danger mb-1">{{ $atRiskStudents->count() }} Students Need Your Attention Today</h6>
                <div class="d-flex flex-wrap gap-2 mt-2">
                    @foreach($atRiskStudents->take(5) as $s)
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 fs-6">
                            {{ $s['name'] }}
                            @if($s['days_inactive'])
                                — {{ $s['days_inactive'] }} days inactive
                            @endif
                        </span>
                    @endforeach
                    @if($atRiskStudents->count() > 5)
                        <span class="badge bg-secondary px-3 py-2 fs-6">+{{ $atRiskStudents->count() - 5 }} more</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── FILTER TABS ──────────────────────────────────────────────────────── --}}
    <div class="d-flex gap-2 mb-3 flex-wrap">
        <button class="btn btn-sm btn-dark active" onclick="filterStudents('all', this)">All ({{ $summary['total'] }})</button>
        <button class="btn btn-sm btn-outline-danger" onclick="filterStudents('at_risk', this)">🔴 At Risk ({{ $summary['at_risk'] }})</button>
        <button class="btn btn-sm btn-outline-secondary" onclick="filterStudents('never_active', this)">⚫ Never Active ({{ $summary['never_active'] }})</button>
        <button class="btn btn-sm btn-outline-warning" onclick="filterStudents('learning', this)">🟡 Learning ({{ $summary['learning'] }})</button>
        <button class="btn btn-sm btn-outline-success" onclick="filterStudents('mastered', this)">🟢 On Track ({{ $summary['mastered'] }})</button>
    </div>

    {{-- ── STUDENT CARDS GRID ───────────────────────────────────────────────── --}}
    <div class="row g-3" id="studentsGrid">
        @forelse($students as $student)
            @php
                $statusColors = [
                    'at_risk'      => ['border' => '#dc3545', 'bg' => '#fff5f5', 'badge' => 'danger',   'label' => '🔴 At Risk'],
                    'never_active' => ['border' => '#6c757d', 'bg' => '#f8f9fa', 'badge' => 'secondary','label' => '⚫ Never Active'],
                    'learning'     => ['border' => '#ffc107', 'bg' => '#fffdf0', 'badge' => 'warning',  'label' => '🟡 Learning'],
                    'mastered'     => ['border' => '#198754', 'bg' => '#f0fff4', 'badge' => 'success',  'label' => '🟢 On Track'],
                ];
                $c = $statusColors[$student['status']] ?? $statusColors['learning'];
                $masteryPct = $student['avg_mastery'];
                $masteryColor = $masteryPct >= 70 ? '#198754' : ($masteryPct >= 40 ? '#ffc107' : '#dc3545');
                $topicsProgress = $student['total_topics'] > 0
                    ? round(($student['topics_attempted'] / $student['total_topics']) * 100)
                    : 0;
            @endphp
            <div class="col-12 col-md-6 col-xl-4 student-card" data-status="{{ $student['status'] }}">
                <div class="card border-0 shadow-sm h-100 student-item"
                     style="border-left: 4px solid {{ $c['border'] }} !important; background: {{ $c['bg'] }};">
                    <div class="card-body p-3">

                        {{-- Top row: name + status badge + inactive warning --}}
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">{{ $student['name'] }}</h6>
                                <small class="text-muted">{{ $student['email'] }}</small>
                            </div>
                            <span class="badge bg-{{ $c['badge'] }} bg-opacity-15 text-{{ $c['badge'] }} border border-{{ $c['badge'] }} border-opacity-25 fs-6">
                                {{ $c['label'] }}
                            </span>
                        </div>

                        {{-- Inactive warning --}}
                        @if($student['days_inactive'] !== null && $student['days_inactive'] >= 3)
                            <div class="alert alert-warning py-1 px-2 mb-2 small mb-2">
                                <i class="bi bi-clock-history me-1"></i>
                                Inactive for <strong>{{ $student['days_inactive'] }} days</strong>
                                (Last seen: {{ $student['last_active_label'] }})
                            </div>
                        @elseif($student['status'] === 'never_active')
                            <div class="alert alert-secondary py-1 px-2 mb-2 small mb-2">
                                <i class="bi bi-person-x me-1"></i> Has never logged in to the app
                            </div>
                        @else
                            <small class="text-muted d-block mb-2">
                                <i class="bi bi-clock me-1"></i> Last active: {{ $student['last_active_label'] }}
                            </small>
                        @endif

                        {{-- Average Mastery Progress Bar --}}
                        <div class="mb-2">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="fw-semibold text-dark">Avg. Mastery</small>
                                <small class="fw-bold" style="color: {{ $masteryColor }}">{{ $masteryPct }}%</small>
                            </div>
                            <div class="progress" style="height: 10px; border-radius: 8px;">
                                <div class="progress-bar" role="progressbar"
                                     style="width: {{ $masteryPct }}%; background-color: {{ $masteryColor }}; border-radius: 8px;"
                                     aria-valuenow="{{ $masteryPct }}" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>

                        {{-- Topic Stats Row --}}
                        <div class="row g-2 mb-3">
                            <div class="col-4 text-center">
                                <div class="bg-success bg-opacity-10 rounded p-1">
                                    <div class="fw-bold text-success small">{{ $student['mastered_count'] }}</div>
                                    <div class="text-muted" style="font-size:10px;">Mastered</div>
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="bg-warning bg-opacity-10 rounded p-1">
                                    <div class="fw-bold text-warning small">{{ $student['learning_count'] }}</div>
                                    <div class="text-muted" style="font-size:10px;">Learning</div>
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="bg-danger bg-opacity-10 rounded p-1">
                                    <div class="fw-bold text-danger small">{{ $student['struggling_count'] }}</div>
                                    <div class="text-muted" style="font-size:10px;">Struggling</div>
                                </div>
                            </div>
                        </div>

                        {{-- Gamification Stats --}}
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <div class="d-flex gap-3">
                                <span class="small text-muted">
                                    <span class="fw-bold text-primary">{{ number_format($student['xp']) }}</span> XP
                                </span>
                                <span class="small text-muted">
                                    🔥 <span class="fw-bold">{{ $student['streak'] }}</span> day streak
                                </span>
                                @if($student['shield_active'])
                                    <span class="small" title="Streak Shield Active">🛡️</span>
                                @endif
                            </div>
                            <div>
                                @for($h = 0; $h < 5; $h++)
                                    <span style="font-size: 10px; color: {{ $h < $student['hearts'] ? '#dc3545' : '#dee2e6' }};">❤</span>
                                @endfor
                            </div>
                        </div>

                    </div>{{-- end card-body --}}
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-people display-4 text-muted opacity-50 d-block mb-3"></i>
                        <h5 class="text-muted">No students enrolled yet</h5>
                        <p class="text-muted small">Students will appear here once they enroll in your courses.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>{{-- end row --}}

</div>
@endsection

@push('scripts')
<script>
function filterStudents(status, btn) {
    // Update active button
    document.querySelectorAll('#studentsGrid ~ div button, .d-flex.gap-2 button').forEach(b => {
        b.classList.remove('active', 'btn-dark', 'btn-danger', 'btn-secondary', 'btn-warning', 'btn-success');
        b.classList.add(b.dataset.originalClass || 'btn-outline-secondary');
    });
    btn.classList.add('active');

    // Filter cards
    document.querySelectorAll('.student-card').forEach(card => {
        if (status === 'all' || card.dataset.status === status) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });

    // Show count
    const visible = document.querySelectorAll('.student-card:not([style*="display: none"])').length;
    console.log(`Showing ${visible} students`);
}
</script>
@endpush
