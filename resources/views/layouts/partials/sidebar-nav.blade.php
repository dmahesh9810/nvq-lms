{{-- Dynamic sidebar navigation based on user role --}}
@php $role = Auth::user()->role; @endphp

<div class="mt-2 flex-grow-1">
    {{-- ── Common Links ── --}}
    <div class="nav-section-label">Main</div>

    @if ($role === 'admin')
        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
    @elseif ($role === 'instructor')
        <a href="{{ route('instructor.dashboard') }}" class="nav-link {{ request()->routeIs('instructor.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
    @elseif ($role === 'assessor')
        <a href="{{ route('assessor.dashboard') }}" class="nav-link {{ request()->routeIs('assessor.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
    @else
        <a href="{{ route('student.dashboard') }}" class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
    @endif

    {{-- ── Admin Links ── --}}
    @if ($role === 'admin')
        <div class="nav-section-label">Administration</div>
        <a href="{{ route('instructor.courses.index') }}" class="nav-link {{ request()->routeIs('instructor.courses.*') ? 'active' : '' }}">
            <i class="bi bi-book-half"></i> All Courses
        </a>
        <a href="{{ route('instructor.dashboard') }}" class="nav-link">
            <i class="bi bi-people-fill"></i> Users
        </a>
    @endif

    {{-- ── Instructor Links ── --}}
    @if (in_array($role, ['admin', 'instructor']))
        <div class="nav-section-label">Teaching</div>
        <a href="{{ route('instructor.courses.index') }}" class="nav-link {{ request()->routeIs('instructor.courses.*') ? 'active' : '' }}">
            <i class="bi bi-journals"></i> My Courses
        </a>
        <a href="{{ route('instructor.courses.create') }}" class="nav-link {{ request()->routeIs('instructor.courses.create') ? 'active' : '' }}">
            <i class="bi bi-plus-circle"></i> New Course
        </a>
    @endif

    {{-- ── Assessor Links ── --}}
    @if ($role === 'assessor')
        <div class="nav-section-label">Assessment</div>
        <a href="{{ route('assessor.dashboard') }}" class="nav-link">
            <i class="bi bi-clipboard-check"></i> Assessments
        </a>
    @endif

    {{-- ── Student Links ── --}}
    @if ($role === 'student')
        <div class="nav-section-label">Learning</div>
        <a href="{{ route('student.courses.browse') }}" class="nav-link {{ request()->routeIs('student.courses.browse') ? 'active' : '' }}">
            <i class="bi bi-search"></i> Browse Courses
        </a>
        <a href="{{ route('student.dashboard') }}" class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
            <i class="bi bi-collection-play"></i> My Learning
        </a>
    @endif

    {{-- ── Profile / Account ── --}}
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
