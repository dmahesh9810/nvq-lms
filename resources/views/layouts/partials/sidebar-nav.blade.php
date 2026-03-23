{{-- Dynamic sidebar navigation based on user role --}}
@php $role = Auth::user()->role; @endphp

<div class="mt-2 flex-grow-1">
    {{-- ── LEARNING ── --}}
    <div class="nav-section-label">Learning</div>

    @if ($role === 'admin')
        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
    @elseif ($role === 'instructor')
        <a href="{{ route('instructor.dashboard') }}" class="nav-link {{ request()->routeIs('instructor.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="{{ route('instructor.courses.index') }}" class="nav-link {{ request()->routeIs('instructor.courses.*') && !request()->routeIs('instructor.courses.create') ? 'active' : '' }}">
            <i class="bi bi-journals"></i> My Courses
        </a>
    @elseif ($role === 'assessor')
        <a href="{{ route('assessor.dashboard') }}" class="nav-link {{ request()->routeIs('assessor.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
    @elseif ($role === 'student')
        <a href="{{ route('student.courses.browse') }}" class="nav-link {{ request()->routeIs('student.courses.browse') ? 'active' : '' }}">
            <i class="bi bi-search"></i> Browse Courses
        </a>
        <a href="{{ route('student.dashboard') }}" class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
            <i class="bi bi-collection-play"></i> My Learning
        </a>
    @endif

    {{-- ── ASSESSMENT ── --}}
    <div class="nav-section-label">Assessment</div>
    
    @if ($role === 'student')
        <a href="{{ route('student.assignments.index') }}" class="nav-link {{ request()->routeIs('student.assignments.*') ? 'active' : '' }}">
            <i class="bi bi-journal-text"></i> Assignments
        </a>
        <a href="{{ route('student.quizzes.index') }}" class="nav-link {{ request()->routeIs('student.quizzes.*') ? 'active' : '' }}">
            <i class="bi bi-patch-question"></i> Quizzes
        </a>
    @endif

    @if ($role === 'instructor' || $role === 'admin')
        <a href="{{ route('instructor.assignments.index') }}" class="nav-link {{ request()->routeIs('instructor.assignments.*') ? 'active' : '' }}">
            <i class="bi bi-journal-text"></i> Assignments
        </a>
        <a href="{{ route('instructor.quizzes.index') }}" class="nav-link {{ request()->routeIs('instructor.quizzes.*') ? 'active' : '' }}">
            <i class="bi bi-patch-question"></i> Quizzes
        </a>
    @endif

    @if (in_array($role, ['admin', 'assessor']))
        <a href="{{ route('assessor.progress.index') }}" class="nav-link {{ request()->routeIs('assessor.progress.*') ? 'active' : '' }}">
            <i class="bi bi-graph-up-arrow"></i> Progress Tracking
        </a>
        <a href="{{ route('assessor.grading.index') }}" class="nav-link {{ request()->routeIs('assessor.grading.*') ? 'active' : '' }}">
            <i class="bi bi-clipboard-check"></i> Grading Queue
        </a>
    @endif


    {{-- ── ADMINISTRATION ── --}}
    <div class="nav-section-label">Administration</div>
    
    @if ($role === 'student')
        <a href="{{ route('student.certificates.index') }}" class="nav-link {{ request()->routeIs('student.certificates.*') ? 'active' : '' }}">
            <i class="bi bi-award"></i> My Certificates
        </a>
    @endif

    @if ($role === 'instructor')
        <a href="{{ route('instructor.change-requests.index') }}" class="nav-link {{ request()->routeIs('instructor.change-requests.*') ? 'active' : '' }}">
            <i class="bi bi-arrow-left-right"></i> Change Requests
        </a>
    @endif

    @if (in_array($role, ['admin', 'assessor']))
        <a href="{{ route('assessor.students.index') }}" class="nav-link {{ request()->routeIs('assessor.students.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Manage Students
        </a>
        <a href="{{ route('assessor.courses.index') }}" class="nav-link {{ request()->routeIs('assessor.courses.*') ? 'active' : '' }}">
            <i class="bi bi-journal-check"></i> Course Analytics
        </a>
    @endif

    @if ($role === 'admin')
        <a href="{{ route('instructor.courses.index') }}" class="nav-link {{ request()->routeIs('instructor.courses.index', 'instructor.courses.show', 'instructor.courses.edit', 'instructor.courses.modules.*', 'instructor.courses.units.*', 'instructor.courses.lessons.*') ? 'active' : '' }}">
            <i class="bi bi-book-half"></i> All Courses
        </a>
        <a href="{{ route('instructor.courses.create') }}" class="nav-link {{ request()->routeIs('instructor.courses.create') ? 'active' : '' }}">
            <i class="bi bi-plus-circle"></i> New Course
        </a>
        <a href="{{ route('admin.certificates.index') }}" class="nav-link {{ request()->routeIs('admin.certificates.*') ? 'active' : '' }}">
            <i class="bi bi-award-fill"></i> Certificates
        </a>
        <a href="{{ route('admin.audits.index') }}" class="nav-link {{ request()->routeIs('admin.audits.*') ? 'active' : '' }}">
            <i class="bi bi-shield-check"></i> Audit Logs
        </a>
    @endif

    {{-- ── Account ── --}}
    <div class="nav-section-label">Account</div>
    <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
        <i class="bi bi-person-circle"></i> Profile
    </a>
    <form method="POST" action="{{ route('logout') }}" class="m-0">
        @csrf
        <button type="submit" class="nav-link border-0 w-100 text-start">
            <i class="bi bi-box-arrow-left"></i> Logout
        </button>
    </form>
</div>
