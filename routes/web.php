<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Instructor\CourseController;
use App\Http\Controllers\Instructor\ModuleController;
use App\Http\Controllers\Instructor\UnitController;
use App\Http\Controllers\Instructor\LessonController;
use App\Http\Controllers\Instructor\AssignmentController as InstructorAssignmentController;
use App\Http\Controllers\Instructor\QuizController as InstructorQuizController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\Student\AssignmentController as StudentAssignmentController;
use App\Http\Controllers\Student\QuizController as StudentQuizController;
use App\Http\Controllers\Assessor\GradingController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────────────────────────────────────
// Public routes
// ─────────────────────────────────────────────────────────────────────────────
Route::get('/', function () {
    return view('welcome');
});

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
        }
        );

        // ── Instructor Routes ─────────────────────────────────────────────────
        Route::middleware('role:admin,instructor')
            ->prefix('instructor')
            ->name('instructor.')
            ->group(function () {
            Route::get('/dashboard', [DashboardController::class , 'instructor'])->name('dashboard');

            // Course CRUD
            Route::resource('courses', CourseController::class);

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
            }
            );

            // ── Assessor Routes ───────────────────────────────────────────────────
            Route::middleware('role:admin,assessor')
                ->prefix('assessor')
                ->name('assessor.')
                ->group(function () {
            Route::get('/dashboard', [DashboardController::class , 'assessor'])->name('dashboard');

            // Phase 3: Grading
            Route::prefix('grading')->name('grading.')->group(function () {
                    Route::get('/', [GradingController::class , 'index'])->name('index');
                    Route::get('/{submission}', [GradingController::class , 'show'])->name('show');
                    Route::post('/{submission}/grade', [GradingController::class , 'grade'])->name('grade');
                }
                );
            }
            );

            // ── Student Routes ────────────────────────────────────────────────────
            Route::middleware('role:admin,student')
                ->prefix('student')
                ->name('student.')
                ->group(function () {
            Route::get('/dashboard', [DashboardController::class , 'student'])->name('dashboard');

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
                    Route::get('/{quiz}/take', [StudentQuizController::class , 'take'])->name('take');
                    Route::post('/{quiz}/submit', [StudentQuizController::class , 'submit'])->name('submit');
                    Route::get('/{quiz}/result/{attempt}', [StudentQuizController::class , 'result'])->name('result');
                }
                );
            }
            );
        });

require __DIR__ . '/auth.php';
