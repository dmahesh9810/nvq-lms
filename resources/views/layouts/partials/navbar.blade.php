    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
                <img src="{{ asset('logo.png') }}" alt="{{ config('app.name') }}" height="40" style="object-fit: contain;">
                <span style="font-weight: 700; font-size: 1.2rem;">{{ config('app.name') }}</span>
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="{{ url('/') }}">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('courses.index') }}">Courses</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/#learning-process') }}">Learning Process</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/#about') }}">About</a></li>
                </ul>
                <div class="d-flex gap-3 align-items-center mt-3 mt-lg-0">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-primary"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-primary">Login</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-primary">Register</a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </nav>
