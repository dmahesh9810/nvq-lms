<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name'))</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            color: #334155;
            background-color: #f8fafc;
        }
        
        /* Shared Navbar Styles */
        .navbar {
            padding: 1rem 0;
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }
        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            color: #1e293b;
        }
        .navbar-brand i { color: #2563eb; }
        .nav-link {
            font-weight: 500;
            color: #475569 !important;
            margin: 0 12px;
            transition: color 0.2s;
        }
        .nav-link:hover { color: #2563eb !important; }

        .btn {
            font-weight: 600;
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background-color: #2563eb;
            border-color: #2563eb;
            color: white;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
            transform: translateY(-2px);
        }
        .btn-outline-primary {
            color: #2563eb;
            border-color: #2563eb;
        }
        .btn-outline-primary:hover {
            background-color: #eff6ff;
            color: #1d4ed8;
            border-color: #1d4ed8;
            transform: translateY(-2px);
        }
    </style>
    @stack('styles')
</head>
<body>
    @include('layouts.partials.navbar')

    <main>
        @yield('content')
    </main>

    @isset($slot)
        {{ $slot }}
    @endisset

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
