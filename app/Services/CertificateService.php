<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;
use App\Models\Certificate;
use App\Models\Quiz;
use Illuminate\Support\Facades\Log;

class CertificateService
{
    /**
     * Check if a student meets all criteria, and if so, issue the certificate.
     */
    public function checkAndIssueCertificate(User $user, Course $course): ?Certificate
    {
        // 1. Check if certificate already exists
        $existingCert = Certificate::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();
        if ($existingCert) {
            return $existingCert; // Already certified
        }

        // 2. Check 100% Lesson Completion
        $totalLessons = $course->totalLessons();
        if ($totalLessons === 0) {
            return null; // Don't issue certificates for empty courses
        }

        $completedLessons = $user->lessonProgress()
            ->whereHas('lesson', function ($q) use ($course) {
            $q->where('is_active', true)
                ->whereHas('unit.module', fn($sq) => $sq->where('course_id', $course->id));
        })
            ->whereNotNull('completed_at')
            ->count();

        if ($completedLessons < $totalLessons) {
            return null; // Has not completed all lessons
        }

        // 3. Check that ALL active quizzes in this course are PASSED
        $courseQuizzes = Quiz::whereHas('unit.module', function ($q) use ($course) {
            $q->where('course_id', $course->id);
        })
            ->where('is_active', true)
            ->get();

        foreach ($courseQuizzes as $quiz) {
            $lastAttempt = $quiz->lastAttemptByUser($user->id);
            if (!$lastAttempt || $lastAttempt->result !== 'PASS') {
                return null; // Found a quiz they haven't passed yet
            }
        }

        // 4. Issue the Certificate using firstOrCreate to prevent race condition duplicates
        try {
            $certificate = Certificate::firstOrCreate(
            [
                'user_id' => $user->id,
                'course_id' => $course->id,
            ],
            [
                'certificate_number' => Certificate::generateNumber(),
                'issued_at' => now(),
                'status' => 'active',
            ]
            );

            return $certificate;

        }
        catch (\Exception $e) {
            Log::error("Failed to generate certificate for user {$user->id} in course {$course->id}: " . $e->getMessage());
            return null;
        }
    }
}
