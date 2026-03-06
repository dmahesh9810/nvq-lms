<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$u = tap(App\Models\User::where('role', 'student')->first(), function ($user) {
    echo "Student ID: " . $user->id . "\n";
});

// Create a mock un-enrolled student scenario
$newStudent = App\Models\User::factory()->create(['role' => 'student']);
echo "New Unenrolled Student ID: " . $newStudent->id . "\n";

echo "Seeded student enrolledCourses count: " . $u->enrolledCourses()->count() . "\n";
echo "Seeded student whereHas assignments: " . App\Models\Assignment::whereHas('unit.module.course.enrollments', function ($q) use ($u) {
    $q->where('student_enrollments.user_id', $u->id); })->count() . "\n";
echo "Seeded student whereHas quizzes: " . App\Models\Quiz::whereHas('unit.module.course.enrollments', function ($q) use ($u) {
    $q->where('student_enrollments.user_id', $u->id); })->count() . "\n";

echo "New student enrolledCourses count: " . $newStudent->enrolledCourses()->count() . "\n";
echo "New student whereHas assignments: " . App\Models\Assignment::whereHas('unit.module.course.enrollments', function ($q) use ($newStudent) {
    $q->where('user_id', $newStudent->id); })->count() . "\n";
echo "New student whereHas quizzes: " . App\Models\Quiz::whereHas('unit.module.course.enrollments', function ($q) use ($newStudent) {
    $q->where('user_id', $newStudent->id); })->count() . "\n";

echo "Total Student Enrollments count: " . App\Models\StudentEnrollment::count() . "\n";
