<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Instructor\CourseController;
use App\Http\Controllers\Instructor\ModuleController;
use App\Http\Controllers\Instructor\UnitController;
use App\Http\Controllers\Instructor\LessonController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────────────────────────────────────
// Public routes
// ─────────────────────────────────────────────────────────────────────────────
Route::get('/', function () {
    return view('welcome');
});

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

        // ── Instructor Routes (admin & instructor can access) ─────────────────
        Route::middleware('role:admin,instructor')
            ->prefix('instructor')
            ->name('instructor.')
            ->group(function () {
            Route::get('/dashboard', [DashboardController::class , 'instructor'])->name('dashboard');

            // Course CRUD
            Route::resource('courses', CourseController::class);

            // Module CRUD — nested under course
            Route::prefix('courses/{course}/modules')
                ->name('courses.modules.')
                ->group(function () {
                Route::get('/create', [ModuleController::class , 'create'])->name('create');
                Route::post('/', [ModuleController::class , 'store'])->name('store');
                Route::get('/{module}/edit', [ModuleController::class , 'edit'])->name('edit');
                Route::put('/{module}', [ModuleController::class , 'update'])->name('update');
                Route::delete('/{module}', [ModuleController::class , 'destroy'])->name('destroy');
            }
            );

            // Unit CRUD — nested under course/module
            Route::prefix('courses/{course}/modules/{module}/units')
                ->name('courses.modules.units.')
                ->group(function () {
                Route::get('/create', [UnitController::class , 'create'])->name('create');
                Route::post('/', [UnitController::class , 'store'])->name('store');
                Route::get('/{unit}/edit', [UnitController::class , 'edit'])->name('edit');
                Route::put('/{unit}', [UnitController::class , 'update'])->name('update');
                Route::delete('/{unit}', [UnitController::class , 'destroy'])->name('destroy');
            }
            );

            // Lesson CRUD — nested under course/module/unit
            Route::prefix('courses/{course}/modules/{module}/units/{unit}/lessons')
                ->name('courses.modules.units.lessons.')
                ->group(function () {
                Route::get('/create', [LessonController::class , 'create'])->name('create');
                Route::post('/', [LessonController::class , 'store'])->name('store');
                Route::get('/{lesson}/edit', [LessonController::class , 'edit'])->name('edit');
                Route::put('/{lesson}', [LessonController::class , 'update'])->name('update');
                Route::delete('/{lesson}', [LessonController::class , 'destroy'])->name('destroy');
            }
            );
        }
        );

        // ── Assessor Dashboard (admin & assessor) ─────────────────────────────
        Route::middleware('role:admin,assessor')
            ->prefix('assessor')
            ->name('assessor.')
            ->group(function () {
            Route::get('/dashboard', [DashboardController::class , 'assessor'])->name('dashboard');
        }
        );

        // ── Student Routes (admin & student) ─────────────────────────────────
        Route::middleware('role:admin,student')
            ->prefix('student')
            ->name('student.')
            ->group(function () {
            Route::get('/dashboard', [DashboardController::class , 'student'])->name('dashboard');

            // Browse available courses
            Route::get('/courses', [StudentController::class , 'browseCourses'])->name('courses.browse');
            // Enroll in a course
            Route::post('/courses/{course}/enroll', [StudentController::class , 'enroll'])->name('courses.enroll');
            // View enrolled course structure
            Route::get('/courses/{course}', [StudentController::class , 'showCourse'])->name('courses.show');
            // Lesson player
            Route::get('/courses/{course}/lessons/{lesson}', [StudentController::class , 'showLesson'])->name('lessons.show');
            // Mark lesson complete
            Route::post('/courses/{course}/lessons/{lesson}/complete', [StudentController::class , 'markComplete'])->name('lessons.complete');
        }
        );    });

require __DIR__ . '/auth.php';
