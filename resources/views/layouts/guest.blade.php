<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; }
    </style>
</head>
<body>
    <div class="container d-flex flex-column justify-content-center align-items-center min-vh-100 py-5">
        <div class="mb-4 text-center">
            <a href="/" class="text-decoration-none d-flex align-items-center justify-content-center gap-2">
                <img src="{{ asset('logo.png') }}" alt="{{ config('app.name') }}" height="44" style="object-fit:contain;">
                <span class="text-dark fs-3 fw-bold">{{ config('app.name') }}</span>
            </a>
        </div>
        <div class="row w-100 justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                {{ $slot }}
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
