<?php
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Instructor\CourseController;
use App\Http\Controllers\Instructor\ModuleController;
use App\Http\Controllers\Instructor\UnitController;
use App\Http\Controllers\Instructor\LessonController;
use App\Http\Controllers\Instructor\InstructorAnalyticsController;
use App\Http\Controllers\Instructor\AssignmentController as InstructorAssignmentController;
use App\Http\Controllers\Instructor\QuizController as InstructorQuizController;
use App\Http\Controllers\Instructor\ChangeRequestController as InstructorChangeRequestController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\AssignmentController as StudentAssignmentController;
use App\Http\Controllers\Student\QuizController as StudentQuizController;
use App\Http\Controllers\Student\CertificateController as StudentCertificateController;
use App\Http\Controllers\Assessor\GradingController;
use App\Http\Controllers\Admin\CertificateController as AdminCertificateController;
use App\Http\Controllers\Admin\ChangeRequestController as AdminChangeRequestController;
use App\Http\Controllers\VerifyCertificateController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Assessor\ProgressController;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────────────────────────────────────
// Public routes
// ─────────────────────────────────────────────────────────────────────────────
Route::get('/', [HomeController::class , 'index'])->name('home');
Route::get('/courses', [HomeController::class , 'courses'])->name('courses.index');
Route::get('/courses/{id}', [HomeController::class , 'showCourse'])->name('courses.show');

Route::get('/verify-certificate', [VerifyCertificateController::class , 'showForm'])->name('verify.form');
Route::get('/verify-certificate/{certificate_number}', [VerifyCertificateController::class , 'verifyByUrl'])->name('certificate.verify');
Route::post('/verify-certificate', [VerifyCertificateController::class , 'verify'])->name('verify.submit');

// ─────────────────────────────────────────────────────────────────────────────
// Fallback /dashboard route — required by Breeze's default tests.
// Redirects to the correct role-based dashboard.
// ─────────────────────────────────────────────────────────────────────────────
Route::get('/dashboard', function () {
    $user = auth()->user();
    if (!$user) {
        return redirect('/login');
    }
    return match ($user->role) {
        'admin' => redirect()->route('admin.dashboard'),
        'instructor' => redirect()->route('instructor.dashboard'),
        'assessor' => redirect()->route('assessor.dashboard'),
        default => redirect()->route('student.dashboard'),
    };
})->middleware('auth')->name('dashboard');

// ─────────────────────────────────────────────────────────────────────────────
// All authenticated routes
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {

    // ── Profile (Breeze default) ──────────────────────────────────────────
    Route::get('/profile', [ProfileController::class , 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class , 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class , 'destroy'])->name('profile.destroy');

    // ── Admin Dashboard ───────────────────────────────────────────────────
    Route::middleware('role:admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::get('/dashboard', [DashboardController::class , 'admin'])->name('dashboard');

            // Course Approval
            Route::patch('/courses/{course}/approve', [DashboardController::class, 'approveCourse'])->name('courses.approve');
            Route::patch('/courses/{course}/reject', [DashboardController::class, 'rejectCourse'])->name('courses.reject');

            // Change Requests (Admin side)
            Route::prefix('change-requests')->name('change-requests.')->group(function () {
                Route::get('/', [AdminChangeRequestController::class, 'index'])->name('index');
                Route::get('/{changeRequest}', [AdminChangeRequestController::class, 'show'])->name('show');
                Route::patch('/{changeRequest}/approve', [AdminChangeRequestController::class, 'approve'])->name('approve');
                Route::patch('/{changeRequest}/reject', [AdminChangeRequestController::class, 'reject'])->name('reject');
            });

            // Phase 4: Certificates Management
            Route::prefix('certificates')->name('certificates.')->group(function () {
                    Route::get('/', [AdminCertificateController::class , 'index'])->name('index');
                    Route::patch('/{certificate}/revoke', [AdminCertificateController::class , 'revoke'])->name('revoke');
                    Route::patch('/{certificate}/reinstate', [AdminCertificateController::class , 'reinstate'])->name('reinstate');
                }
            );

            // Phase 4: TVEC Verification Logs
            Route::get('/audits', [\App\Http\Controllers\Admin\AuditController::class, 'index'])->name('audits.index');
        });

            // ── Instructor Routes ─────────────────────────────────────────────────
            Route::middleware('role:admin,instructor')
                ->prefix('instructor')
                ->name('instructor.')
                ->group(function () {
            Route::get('/dashboard', [DashboardController::class , 'instructor'])->name('dashboard');


            // Course CRUD
            Route::resource('courses', CourseController::class);
            Route::patch('courses/{course}/submit-for-review', [CourseController::class, 'submitForReview'])->name('courses.submit');

            // Phase 4: Instructor Assignment routes (Admin only checks inside controller)
            Route::post('courses/{course}/instructors/sync', [\App\Http\Controllers\Instructor\CourseInstructorController::class, 'syncCourseInstructors'])->name('courses.instructors.sync');
            Route::post('courses/{course}/modules/{module}/instructors/sync', [\App\Http\Controllers\Instructor\CourseInstructorController::class, 'syncModuleInstructors'])->name('courses.modules.instructors.sync');

            // Module CRUD — nested under course
            Route::prefix('courses/{course}/modules')->name('courses.modules.')->group(function () {
                    Route::get('/create', [ModuleController::class , 'create'])->name('create');
                    Route::post('/', [ModuleController::class , 'store'])->name('store');
                    Route::get('/{module}/edit', [ModuleController::class , 'edit'])->name('edit');
                    Route::put('/{module}', [ModuleController::class , 'update'])->name('update');
                    Route::delete('/{module}', [ModuleController::class , 'destroy'])->name('destroy');
                }
                );

                // Unit CRUD — nested under course/module
                Route::prefix('courses/{course}/modules/{module}/units')->name('courses.modules.units.')->group(function () {
                    Route::get('/create', [UnitController::class , 'create'])->name('create');
                    Route::post('/', [UnitController::class , 'store'])->name('store');
                    Route::get('/{unit}/edit', [UnitController::class , 'edit'])->name('edit');
                    Route::put('/{unit}', [UnitController::class , 'update'])->name('update');
                    Route::delete('/{unit}', [UnitController::class , 'destroy'])->name('destroy');
                }
                );

                // Lesson CRUD — nested under course/module/unit
                Route::prefix('courses/{course}/modules/{module}/units/{unit}/lessons')->name('courses.modules.units.lessons.')->group(function () {
                    Route::get('/create', [LessonController::class , 'create'])->name('create');
                    Route::post('/', [LessonController::class , 'store'])->name('store');
                    Route::get('/{lesson}/edit', [LessonController::class , 'edit'])->name('edit');
                    Route::put('/{lesson}', [LessonController::class , 'update'])->name('update');
                    Route::delete('/{lesson}', [LessonController::class , 'destroy'])->name('destroy');
                }
                );

                // ── Phase 3: Assignments ──────────────────────────────────────
                Route::prefix('assignments')->name('assignments.')->group(function () {
                    Route::get('/', [InstructorAssignmentController::class , 'index'])->name('index');
                    Route::get('/create', [InstructorAssignmentController::class , 'create'])->name('create');
                    Route::post('/', [InstructorAssignmentController::class , 'store'])->name('store');
                    Route::get('/{assignment}/edit', [InstructorAssignmentController::class , 'edit'])->name('edit');
                    Route::put('/{assignment}', [InstructorAssignmentController::class , 'update'])->name('update');
                    Route::delete('/{assignment}', [InstructorAssignmentController::class , 'destroy'])->name('destroy');
                    Route::get('/{assignment}/submissions', [InstructorAssignmentController::class , 'submissions'])->name('submissions');
                    Route::post('/submissions/{submission}/review', [InstructorAssignmentController::class, 'reviewSubmission'])->name('submissions.review');
                }
                );

                // ── Phase 3: Quizzes ─────────────────────────────────────────
                Route::prefix('quizzes')->name('quizzes.')->group(function () {
                    Route::get('/', [InstructorQuizController::class , 'index'])->name('index');
                    Route::get('/create', [InstructorQuizController::class , 'create'])->name('create');
                    Route::post('/', [InstructorQuizController::class , 'store'])->name('store');
                    Route::get('/{quiz}/edit', [InstructorQuizController::class , 'edit'])->name('edit');
                    Route::put('/{quiz}', [InstructorQuizController::class , 'update'])->name('update');
                    Route::delete('/{quiz}', [InstructorQuizController::class , 'destroy'])->name('destroy');
                    Route::get('/{quiz}/questions', [InstructorQuizController::class , 'questions'])->name('questions');
                    Route::post('/{quiz}/questions', [InstructorQuizController::class , 'storeQuestion'])->name('questions.store');
                    Route::delete('/{quiz}/questions/{question}', [InstructorQuizController::class , 'destroyQuestion'])->name('questions.destroy');
                }
                );

                // ── Change Requests (Instructor submit / view own) ────────────
                Route::prefix('change-requests')->name('change-requests.')->group(function () {
                    Route::get('/', [InstructorChangeRequestController::class, 'index'])->name('index');
                    Route::post('/', [InstructorChangeRequestController::class, 'store'])->name('store');
                });
            }
            );

            // ── Assessor Routes ───────────────────────────────────────────────────
            Route::middleware('role:admin,assessor')
                ->prefix('assessor')
                ->name('assessor.')
                ->group(function () {
            Route::get('/dashboard', [\App\Http\Controllers\Assessor\AssessorController::class , 'dashboard'])->name('dashboard');
            Route::get('/students', [\App\Http\Controllers\Assessor\AssessorController::class , 'students'])->name('students.index');
            Route::get('/courses', [\App\Http\Controllers\Assessor\AssessorController::class , 'courses'])->name('courses.index');

            // Phase 4: Assessor Progress Tracking
            Route::prefix('progress')->name('progress.')->group(function () {
                    Route::get('/', [ProgressController::class , 'index'])->name('index');
                    Route::get('/student/{student}/course/{course}', [ProgressController::class , 'show'])->name('detail');
                }
            );

            // Phase 2: Competency Assessment
            Route::prefix('competency')->name('competency.')->group(function () {
                Route::get('/student/{student}/course/{course}', [\App\Http\Controllers\Assessor\CompetencyController::class, 'index'])->name('index');
                Route::post('/student/{student}/unit/{unit}', [\App\Http\Controllers\Assessor\CompetencyController::class, 'update'])->name('update');
            });

            // Phase 3: Grading
            Route::prefix('grading')->name('grading.')->group(function () {
                    Route::get('/', [GradingController::class , 'index'])->name('index');
                    Route::get('/{submission}', [GradingController::class , 'show'])->name('show');
                    Route::post('/{submission}/verify', [GradingController::class , 'verify'])->name('verify');
                }
                );
            }
            );

            // ── Student Routes ────────────────────────────────────────────────────
            Route::middleware('role:admin,student')
                ->prefix('student')
                ->name('student.')
                ->group(function () {
            Route::get('/dashboard', [StudentDashboardController::class , 'index'])->name('dashboard');

            // Phase 2: Courses & lessons
            Route::get('/courses', [StudentController::class , 'browseCourses'])->name('courses.browse');
            Route::post('/courses/{course}/enroll', [StudentController::class , 'enroll'])->name('courses.enroll');
            Route::get('/courses/{course}', [StudentController::class , 'showCourse'])->name('courses.show');
            Route::get('/courses/{course}/lessons/{lesson}', [StudentController::class , 'showLesson'])->name('lessons.show');
            Route::post('/courses/{course}/lessons/{lesson}/complete', [StudentController::class , 'markComplete'])->name('lessons.complete');

            // Phase 3: Assignments
            Route::prefix('assignments')->name('assignments.')->group(function () {
                    Route::get('/', [StudentAssignmentController::class , 'index'])->name('index');
                    Route::get('/{assignment}', [StudentAssignmentController::class , 'show'])->name('show');
                    Route::post('/{assignment}/submit', [StudentAssignmentController::class , 'submit'])->name('submit');
                }
                );

                // Phase 3: Quizzes
                Route::prefix('quizzes')->name('quizzes.')->group(function () {
                    Route::get('/', [StudentQuizController::class , 'index'])->name('index');
                    Route::get('/{quiz}/start', [StudentQuizController::class , 'showStart'])->name('start.view');
                    Route::post('/{quiz}/start', [StudentQuizController::class , 'startQuiz'])->name('start');
                    Route::get('/{quiz}/attempt/{attempt}', [StudentQuizController::class , 'showQuiz'])->name('attempt');
                    Route::post('/{quiz}/attempt/{attempt}/submit', [StudentQuizController::class , 'submitQuiz'])->name('submit');
                    Route::get('/{quiz}/result/{attempt}', [StudentQuizController::class , 'showResult'])->name('result');
                }
                );

                // Phase 4: Certificates
                Route::prefix('certificates')->name('certificates.')->group(function () {
                    Route::get('/', [StudentCertificateController::class , 'index'])->name('index');
                    Route::get('/{certificate}/download', [StudentCertificateController::class , 'download'])->name('download');
                }
                );
            }
            );
        });

require __DIR__ . '/auth.php';
