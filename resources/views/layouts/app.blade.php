<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'IQBrave LMS') }} — @yield('title', 'Dashboard')</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f6fb; }

        /* ── Sidebar ───────────────────────────────────────────── */
        .sidebar {
            min-height: 100vh;
            width: 260px;
            background: linear-gradient(180deg, #1e2a45 0%, #16213e 100%);
            position: fixed;
            top: 0; left: 0;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }
        .sidebar .brand {
            padding: 1.4rem 1.25rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }
        .sidebar .brand a {
            text-decoration: none;
            color: #fff;
            font-weight: 700;
            font-size: 1.15rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .sidebar .nav-section-label {
            font-size: 0.68rem;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: rgba(255,255,255,0.3);
            padding: 0.9rem 1.25rem 0.3rem;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.65);
            padding: 0.6rem 1rem;
            border-radius: 8px;
            margin: 1px 0.6rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            transition: all 0.18s;
            font-size: 0.875rem;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.12);
            color: #fff;
        }
        .sidebar .nav-link i { font-size: 1rem; min-width: 18px; }

        /* ── Topbar ────────────────────────────────────────────── */
        .main-wrapper { margin-left: 260px; }
        .topbar {
            background: #fff;
            border-bottom: 1px solid #e5e9f2;
            padding: 0.7rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        /* ── Main content area ──────────────────────────────────── */
        .main-content { padding: 1.75rem; }

        /* ── Stat cards ─────────────────────────────────────────── */
        .stat-card {
            border: none;
            border-radius: 14px;
            padding: 1.4rem;
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 28px rgba(0,0,0,0.1); }
        .stat-icon {
            width: 50px; height: 50px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 12px;
            font-size: 1.35rem;
            margin-bottom: 1rem;
        }
        .stat-value { font-size: 1.9rem; font-weight: 700; line-height: 1; margin-bottom: 0.2rem; }
        .stat-label { font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.7; }

        .bg-soft-blue   { background: #e8f0fe; } .text-soft-blue   { color: #1a73e8; }
        .bg-soft-green  { background: #e6f4ea; } .text-soft-green  { color: #2d9e5a; }
        .bg-soft-amber  { background: #fef8e1; } .text-soft-amber  { color: #e8a000; }
        .bg-soft-red    { background: #fce9e8; } .text-soft-red    { color: #d93025; }
        .bg-soft-purple { background: #f3e8fd; } .text-soft-purple { color: #8430c4; }
        .bg-soft-teal   { background: #e4f4f2; } .text-soft-teal   { color: #0a7a6e; }

        /* ── Misc ───────────────────────────────────────────────── */
        .card { border: none; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
        .card-header { border-bottom: 1px solid #f0f2f7; background: #fff; border-radius: 12px 12px 0 0 !important; }
        .progress { height: 8px; border-radius: 4px; }
        .table > :not(caption) > * > * { padding: 0.75rem 1rem; }
        .badge-role { font-size: 0.7rem; padding: 3px 8px; border-radius: 20px; font-weight: 500; }

        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .main-wrapper { margin-left: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>

{{-- ── Sidebar ─────────────────────────────────────────────── --}}
<nav class="sidebar">
    <div class="brand">
        <a href="{{ url('/') }}">
            <i class="bi bi-mortarboard-fill text-warning"></i>
            IQBrave LMS
        </a>
    </div>

    @include('layouts.partials.sidebar-nav')
</nav>

{{-- ── Main wrapper ────────────────────────────────────────── --}}
<div class="main-wrapper">

    {{-- Topbar --}}
    <header class="topbar d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-semibold text-dark">@yield('page-title', 'Dashboard')</h6>
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted small d-none d-sm-inline">{{ Auth::user()->name }}</span>
            @php
                $roleColors = ['admin'=>'danger','instructor'=>'primary','assessor'=>'warning text-dark','student'=>'success'];
                $roleColor  = $roleColors[Auth::user()->role] ?? 'secondary';
            @endphp
            <span class="badge bg-{{ $roleColor }} badge-role">{{ ucfirst(Auth::user()->role) }}</span>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary px-3">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </button>
            </form>
        </div>
    </header>

    {{-- Page content --}}
    <main class="main-content">
        {{-- Flash messages --}}
        @foreach (['success','error','info','warning'] as $msgType)
            @if(session($msgType))
                <div class="alert alert-{{ $msgType === 'error' ? 'danger' : $msgType }} alert-dismissible fade show rounded-3 mb-3" role="alert">
                    <i class="bi bi-{{ $msgType === 'success' ? 'check-circle' : ($msgType === 'error' ? 'x-circle' : 'info-circle') }}-fill me-2"></i>
                    {{ session($msgType) }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        @endforeach

        @yield('content')
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
