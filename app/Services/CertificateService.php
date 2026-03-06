<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;
use App\Models\Certificate;
use App\Models\Quiz;
use App\Models\AssignmentResult;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CertificateService
{
    /**
     * Check if a student meets all criteria for course completion.
     * 
     * Centralized logic:
     * 1. 100% Lesson Completion
     * 2. All active Quizzes passed
     * 3. All Assignments marked "Competent" (NVQ)
     */
    public function getEligibilityStatus(User $user, Course $course): array
    {
        $reasons = [];

        // 1. Lessons Check
        $totalLessons = $course->totalLessons();
        if ($totalLessons > 0) {
            $completedLessons = $user->lessonProgress()
                ->whereHas('lesson', function ($q) use ($course) {
                $q->where('is_active', true)
                    ->whereHas('unit.module', fn($sq) => $sq->where('course_id', $course->id));
            })
                ->whereNotNull('completed_at')
                ->count();

            if ($completedLessons < $totalLessons) {
                $reasons[] = "You must complete all lessons before receiving the certificate ($completedLessons/$totalLessons completed).";
            }
        }

        // 2. Quizzes Check
        $courseQuizzes = Quiz::whereHas('unit.module', function ($q) use ($course) {
            $q->where('course_id', $course->id);
        })->where('is_active', true)->get();

        foreach ($courseQuizzes as $quiz) {
            $lastAttempt = $quiz->lastAttemptByUser($user->id);
            if (!$lastAttempt || $lastAttempt->result !== 'PASS') {
                $reasons[] = "You must pass all quizzes. Incomplete: '{$quiz->title}'.";
            }
        }

        // 3. Assignments Check (NVQ Competency)
        $course->loadMissing(['modules.units.assignments']);
        $allAssignmentIds = $course->modules->flatMap->units->flatMap->assignments->pluck('id');

        if ($allAssignmentIds->isNotEmpty()) {
            $competentCount = AssignmentResult::join('assignment_submissions', 'assignment_results.submission_id', '=', 'assignment_submissions.id')
                ->where('assignment_submissions.user_id', $user->id)
                ->whereIn('assignment_submissions.assignment_id', $allAssignmentIds)
                ->where('assignment_results.competency_status', 'competent')
                ->count();

            if ($competentCount < $allAssignmentIds->count()) {
                $reasons[] = "You must be marked 'Competent' in all project assignments to earn this NVQ certificate.";
            }
        }

        return [
            'is_eligible' => empty($reasons),
            'reasons' => $reasons,
        ];
    }

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
            return $existingCert;
        }

        // 2. Check full eligibility
        $status = $this->getEligibilityStatus($user, $course);
        if (!$status['is_eligible']) {
            return null;
        }

        // 3. Issue the Certificate
        try {
            return DB::transaction(function () use ($user, $course) {
                return Certificate::firstOrCreate(
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
            });
        }
        catch (\Exception $e) {
            Log::error("Failed to generate certificate for user {$user->id} in course {$course->id}: " . $e->getMessage());
            return null;
        }
    }
}
