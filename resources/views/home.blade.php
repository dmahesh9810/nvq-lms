@extends('layouts.main')

@section('title', config('app.name') . ' - Learn NVQ ICT Skills Online')

@section('content')
    <!-- 7. NOTICE SECTION -->
    <div class="container mt-4 mb-2">
        <div class="notice-bar rounded-3">
            <i class="bi bi-info-circle-fill fs-4 flex-shrink-0"></i>
            <div>
                <strong>Note:</strong> {{ config('app.name') }} supports NVQ students in learning ICT subjects. <em>NVQ certificates are issued only by authorized vocational training institutes.</em> This platform is strictly for online learning and assessment support.
            </div>
        </div>
    </div>

    <!-- 2. HERO SECTION -->
    <section class="hero-section mt-4 mb-5 mx-3 rounded-4">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill mb-4 fw-bold">
                        <i class="bi bi-book me-1"></i> NVQ Learning Support Platform
                    </span>
                    <h1 class="hero-title">Learn NVQ ICT Skills <span class="text-primary">Online</span></h1>
                    <p class="hero-subtitle">{{ config('app.name') }} helps NVQ students learn ICT subjects online, complete lessons, attempt quizzes, and track their learning progress collaboratively with assessors.</p>
                    <div class="d-flex flex-column flex-sm-row gap-3">
                        <a href="{{ route('courses.index') }}" class="btn btn-primary btn-lg px-4 py-3 shadow">Browse Courses <i class="bi bi-arrow-right ms-2"></i></a>
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-primary btn-lg px-4 py-3">Go to Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-outline-primary btn-lg px-4 py-3">Student Login</a>
                        @endauth
                    </div>
                </div>
                <div class="col-lg-6">
                    <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" alt="Students learning online" class="img-fluid hero-image w-100">
                </div>
            </div>
        </div>
    </section>

    <!-- 4. WHO IS THIS PLATFORM FOR -->
    <section id="about" class="py-5 bg-white">
        <div class="container py-5">
            <div class="text-center mb-5">
                <span class="section-label">Platform Roles</span>
                <h2 class="section-title">Who is this platform for?</h2>
                <p class="text-muted fs-5 max-w-700 mx-auto">A collaborative environment bridging the gap between NVQ students and their assessors for theoretical learning.</p>
            </div>

            <div class="row g-4 justify-content-center">
                <!-- For Students -->
                <div class="col-12 col-md-4">
                    <div class="audience-card">
                        <div class="audience-icon icon-student">
                            <i class="bi bi-mortarboard"></i>
                        </div>
                        <h4 class="fw-bold mb-3">For Students</h4>
                        <ul class="list-unstyled text-muted mb-0">
                            <li class="mb-3 d-flex"><i class="bi bi-check2-circle text-primary me-2 fs-5"></i> Learn NVQ ICT subjects online at your own pace</li>
                            <li class="mb-3 d-flex"><i class="bi bi-check2-circle text-primary me-2 fs-5"></i> Complete interactive quizzes and submit assignments</li>
                            <li class="d-flex"><i class="bi bi-check2-circle text-primary me-2 fs-5"></i> Track your learning progress through the dashboard</li>
                        </ul>
                    </div>
                </div>

                <!-- For Instructors (NEW) -->
                <div class="col-12 col-md-4">
                    <div class="audience-card">
                        <div class="audience-icon icon-assessor" style="background: #f0fdf4;">
                            <i class="bi bi-easel2" style="color: #16a34a;"></i>
                        </div>
                        <h4 class="fw-bold mb-3">For Instructors</h4>
                        <ul class="list-unstyled text-muted mb-0">
                            <li class="mb-3 d-flex"><i class="bi bi-check2-circle text-success me-2 fs-5"></i> Create and manage courses easily</li>
                            <li class="mb-3 d-flex"><i class="bi bi-check2-circle text-success me-2 fs-5"></i> Upload lessons, videos, and learning materials</li>
                            <li class="mb-3 d-flex"><i class="bi bi-check2-circle text-success me-2 fs-5"></i> Track student progress and performance</li>
                            <li class="d-flex"><i class="bi bi-check2-circle text-success me-2 fs-5"></i> Manage quizzes and assignments</li>
                        </ul>
                    </div>
                </div>

                <!-- For Assessors -->
                <div class="col-12 col-md-4">
                    <div class="audience-card">
                        <div class="audience-icon icon-assessor">
                            <i class="bi bi-person-badge"></i>
                        </div>
                        <h4 class="fw-bold mb-3">For Assessors</h4>
                        <ul class="list-unstyled text-muted mb-0">
                            <li class="mb-3 d-flex"><i class="bi bi-check2-circle text-primary me-2 fs-5"></i> Monitor student lesson completion and activity</li>
                            <li class="mb-3 d-flex"><i class="bi bi-check2-circle text-primary me-2 fs-5"></i> Review, grade, and provide feedback on assignments</li>
                            <li class="d-flex"><i class="bi bi-check2-circle text-primary me-2 fs-5"></i> Evaluate theoretical competencies securely</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. COURSES SECTION (Dynamic) -->
    <section class="py-5 bg-light">
        <div class="container py-5">
            <div class="d-flex justify-content-between align-items-end mb-5">
                <div>
                    <span class="section-label">Curriculum</span>
                    <h2 class="section-title mb-0">Recent ICT Subjects</h2>
                </div>
                <a href="{{ route('courses.index') }}" class="btn btn-outline-primary d-none d-md-inline-block">View All Subjects</a>
            </div>
            
            <div class="row g-4">
                @forelse($courses as $course)
                <div class="col-lg-3 col-md-6">
                    <div class="course-card d-flex flex-column">
                        @if($course->thumbnail)
                            <img src="{{ Storage::url($course->thumbnail) }}" class="card-img-top" alt="{{ $course->title }}">
                        @else
                            <img src="https://images.unsplash.com/photo-1517694712202-14dd9538aa97?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" class="card-img-top" alt="{{ $course->title }}">
                        @endif
                        <div class="card-body d-flex flex-column p-4">
                            <span class="badge bg-secondary mb-3 align-self-start">ICT Support</span>
                            <h5 class="fw-bold mb-2">{{ Str::limit($course->title, 45) }}</h5>
                            <p class="text-muted small flex-grow-1">{{ Str::limit(strip_tags($course->description), 75) }}</p>
                            <a href="{{ route('courses.show', $course->id) }}" class="btn btn-outline-primary mt-3 w-100">View Course</a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center py-5">
                    <p class="text-muted fs-5">New courses are being prepared. Check back soon.</p>
                </div>
                @endforelse
            </div>
            
            <div class="text-center mt-4 d-md-none">
                <a href="{{ route('courses.index') }}" class="btn btn-outline-primary w-100">View All Subjects</a>
            </div>
        </div>
    </section>

    <!-- 5. LEARNING PROCESS SECTION & 6. PROGRESS TRACKING -->
    <section id="learning-process" class="py-5 bg-white">
        <div class="container py-5">
            <div class="row g-5 align-items-center">
                <!-- Learning Process Flow -->
                <div class="col-lg-7">
                    <span class="section-label">Workflow</span>
                    <h2 class="section-title mb-5">The Learning Process</h2>
                    
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="process-step">
                                <div class="process-number">1</div>
                                <h6 class="fw-bold mb-0">Register an Account</h6>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="process-step">
                                <div class="process-number bg-success">2</div>
                                <h6 class="fw-bold mb-0">Enroll in an NVQ Course</h6>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="process-step">
                                <div class="process-number bg-warning text-dark">3</div>
                                <h6 class="fw-bold mb-0">Study Lessons & Complete Quizzes</h6>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="process-step">
                                <div class="process-number bg-danger">4</div>
                                <h6 class="fw-bold mb-0">Submit Assignments</h6>
                            </div>
                        </div>
                        <div class="col-12 mt-3">
                            <div class="process-step bg-primary bg-opacity-10 border-primary-subtle text-primary">
                                <i class="bi bi-person-check-fill fs-1 mb-2"></i>
                                <h5 class="fw-bold mb-0">5. Assessors Evaluate Learning Progress</h5>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Progress Tracking Features -->
                <div class="col-lg-5 ps-lg-5 border-start border-light d-none d-lg-block">
                    <h3 class="fw-bold mb-4">Learning Progress Tracking</h3>
                    <p class="text-muted mb-4">Our platform provides comprehensive tools to ensure you are meeting the theoretical requirements of your NVQ studies.</p>
                    
                    <div class="progress-feature">
                        <i class="bi bi-check-circle-fill feature-check"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Track Lesson Completion</h6>
                            <p class="text-muted small mb-0">Visual indicators show exactly which topics you've mastered and what's next.</p>
                        </div>
                    </div>
                    
                    <div class="progress-feature">
                        <i class="bi bi-bar-chart-fill feature-check text-primary"></i>
                        <div>
                            <h6 class="fw-bold mb-1">View Quiz Results</h6>
                            <p class="text-muted small mb-0">Immediate automated grading on theory quizzes to test your comprehension.</p>
                        </div>
                    </div>
                    
                    <div class="progress-feature">
                        <i class="bi bi-chat-text-fill feature-check text-warning"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Monitor Assignment Feedback</h6>
                            <p class="text-muted small mb-0">Receive direct feedback and grades from registered assessors on your submissions.</p>
                        </div>
                    </div>

                    <div class="progress-feature">
                        <i class="bi bi-graph-up-arrow feature-check text-purple"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Follow Your Learning Progress</h6>
                            <p class="text-muted small mb-0">A dedicated student dashboard aggregating your ongoing efforts in one place.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 8. CALL TO ACTION -->
    <section class="cta-section">
        <div class="container">
            <h2 class="display-5 fw-bold text-white mb-4">Start Learning NVQ ICT Subjects Today</h2>
            <p class="lead text-white-50 mb-5 mx-auto" style="max-width: 600px;">Access theoretical knowledge, complete assignments, and track your understanding with our dedicated learning support platform.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="{{ route('courses.index') }}" class="btn btn-light btn-lg px-4 py-3 fw-bold text-dark shadow">Browse Courses</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg px-4 py-3 fw-bold border-white">Register</a>
                @endif
            </div>
        </div>
    </section>

    <!-- 9. FOOTER -->
    <footer class="footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-5 pe-lg-5">
                    <a href="{{ url('/') }}" class="footer-logo mb-3 d-flex align-items-center gap-2">
                        <img src="{{ asset('logo.png') }}" alt="{{ config('app.name') }}" height="36" style="object-fit:contain;">
                        <span>{{ config('app.name') }}</span>
                    </a>
                    <p class="text-muted small mb-4">An online learning support platform dedicated to assisting NVQ ICT students track their progress, consume theoretical lessons, and interact with course assessors online.</p>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <h6 class="fw-bold text-uppercase mb-3">Quick Links</h6>
                    <ul class="list-unstyled mb-0">
                        <li><a href="{{ route('courses.index') }}" class="footer-link">Browse Courses</a></li>
                        @auth
                            <li><a href="{{ route('dashboard') }}" class="footer-link">Dashboard</a></li>
                        @else
                            <li><a href="{{ route('login') }}" class="footer-link">Student/Assessor Login</a></li>
                        @endauth
                        <li><a href="#learning-process" class="footer-link">Learning Process</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <h6 class="fw-bold text-uppercase mb-3">Contact Information</h6>
                    <ul class="list-unstyled text-muted small mb-0">
                        <li class="mb-2"><i class="bi bi-envelope text-primary me-2"></i> support@iqbravelms.lk</li>
                        <li class="mb-2"><i class="bi bi-telephone text-primary me-2"></i> +94 11 234 5678</li>
                        <li><i class="bi bi-info-circle text-primary me-2"></i> Support Hours: 9 AM - 5 PM (Mon-Fri)</li>
                    </ul>
                </div>
            </div>
            
            <div class="border-top pt-4 mt-5 text-center text-muted small">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved. <br>
                <span class="fst-italic opacity-75">Not an official certification body. Final NVQ certificates are issued only by authorized institutes.</span>
            </div>
        </div>
    </footer>
@endsection

@push('styles')
    <style>
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            padding: 100px 0;
            border-bottom: 1px solid #bae6fd;
            overflow: hidden;
        }
        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 1.5rem;
            letter-spacing: -1px;
        }
        .hero-subtitle {
            font-size: 1.2rem;
            color: #475569;
            margin-bottom: 2.5rem;
            line-height: 1.7;
        }
        .hero-image {
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            border: 4px solid white;
        }

        /* Notice Section */
        .notice-bar {
            background-color: #fffbeb;
            border-left: 4px solid #fbbf24;
            padding: 1rem 1.5rem;
            color: #92400e;
            font-size: 0.95rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        /* Target Audience Section */
        .audience-card {
            background: white;
            border-radius: 16px;
            padding: 2.5rem;
            height: 100%;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }
        .audience-card:hover { transform: translateY(-5px); }
        .audience-icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 1.5rem;
        }
        .icon-student { background: #eff6ff; color: #2563eb; }
        .icon-assessor { background: #fdf4ff; color: #c026d3; }

        /* Section Headings */
        .section-label {
            color: #2563eb;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-size: 0.85rem;
            display: inline-block;
            background: #eff6ff;
            padding: 6px 16px;
            border-radius: 50px;
            margin-bottom: 1rem;
        }
        .section-title { font-size: 2.5rem; margin-bottom: 1rem; }
        
        /* Course Cards */
        .course-card {
            background: white; border-radius: 16px; border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); transition: all 0.3s;
            height: 100%; overflow: hidden;
        }
        .course-card:hover { transform: translateY(-8px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); }
        .course-card .card-img-top { height: 200px; object-fit: cover; }

        /* Learning Process */
        .process-step {
            position: relative;
            padding: 2rem;
            background: white;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            text-align: center;
            height: 100%;
            z-index: 2;
        }
        .process-number {
            width: 50px; height: 50px;
            background: #2563eb; color: white;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem; font-weight: 800;
            margin: 0 auto 1.25rem;
            box-shadow: 0 4px 6px -1px rgba(37,99,235,0.3);
        }

        /* Progress Features */
        .progress-feature {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }
        .feature-check {
            color: #10b981;
            font-size: 1.5rem;
            margin-right: 1rem;
            line-height: 1;
        }

        /* CTA */
        .cta-section {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            padding: 80px 0;
            color: white;
            text-align: center;
        }

        /* Footer */
        .footer {
            background-color: #f8fafc;
            padding: 60px 0 30px;
            border-top: 1px solid #e2e8f0;
        }
        .footer-logo {
            font-size: 1.5rem; font-weight: 800; color: #0f172a;
            text-decoration: none; display: flex; align-items: center; gap: 0.5rem;
        }
        .footer-link {
            color: #64748b; text-decoration: none; display: block;
            margin-bottom: 0.5rem; transition: color 0.2s;
        }
        .footer-link:hover { color: #2563eb; }
    </style>
@endpush

