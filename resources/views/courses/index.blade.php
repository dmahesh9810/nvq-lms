<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>All Courses - IQBrave LMS</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #334155; }
        .course-card {
            background: white; border-radius: 16px; border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); transition: all 0.3s;
            height: 100%; display: flex; flex-direction: column; overflow: hidden;
        }
        .course-card:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); }
        .card-img-top { height: 200px; object-fit: cover; border-bottom: 1px solid #f1f5f9; }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg bg-white sticky-top border-bottom py-3 shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-dark fs-4" href="{{ url('/') }}">
                <i class="bi bi-award-fill text-primary"></i> IQBrave LMS
            </a>
            <div class="ms-auto d-flex gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary"><i class="bi bi-speedometer2"></i> Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-primary">Login</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-primary d-none d-sm-inline-block">Register</a>
                    @endif
                @endauth
            </div>
        </div>
    </nav>

    <!-- COURSES SECTION -->
    <div class="container py-5">
        <h2 class="fw-bold mb-4">All Courses</h2>
        <div class="row g-4 mb-5">
            @forelse($courses as $course)
                <div class="col-lg-3 col-md-6">
                    <div class="course-card">
                        @if($course->thumbnail)
                            <img src="{{ Storage::url($course->thumbnail) }}" class="card-img-top" alt="{{ $course->title }}">
                        @else
                            <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" class="card-img-top" alt="{{ $course->title }}">
                        @endif
                        <div class="p-4 d-flex flex-column flex-grow-1">
                            <h5 class="fw-bold mb-2">{{ Str::limit($course->title, 45) }}</h5>
                            <p class="text-muted small flex-grow-1">{{ Str::limit(strip_tags($course->description), 80) }}</p>
                            <a href="{{ route('courses.show', $course->id) }}" class="btn btn-outline-primary w-100">View Course</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <p class="text-muted fs-5">No courses available at the moment.</p>
                </div>
            @endforelse
        </div>
        
        <div class="d-flex justify-content-center">
            {{ $courses->links() }}
        </div>
    </div>

</body>
</html>
