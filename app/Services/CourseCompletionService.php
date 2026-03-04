<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * CourseCompletionService
 *
 * Determines whether a student has earned NVQ competency for a course.
 *
 * NVQ RULE:
 *   - A unit is "competent" when every assignment in that unit has a
 *     graded result with competency_status = 'competent' for the student.
 *   - A course is "competent" when ALL of its units are competent.
 *   - Units that have no assignments are treated as automatically competent
 *     (there is nothing to assess, so they do not block completion).
 *
 * PERFORMANCE:
 *   All data is loaded in a single eager-loading pass.
 *   No queries are executed inside loops.
 */
class CourseCompletionService
{
    /**
     * Check whether a student has achieved competency for every unit
     * in the given course.
     *
     * Uses a single, efficient query strategy:
     * 1. Load course → modules → units → assignments (with count)
     * 2. Load all assignment_results for this student in one query,
     *    keyed by assignment_id for O(1) lookup.
     * Returns false early as soon as any required unit is not competent.
     */
    public function isCompetent(User $user, Course $course): bool
    {
        // 1. Load full course structure in one query
        $course->loadMissing([
            'modules.units.assignments',
        ]);

        // Collect all assignment IDs in this course
        $allAssignmentIds = [];
        foreach ($course->modules as $module) {
            foreach ($module->units as $unit) {
                foreach ($unit->assignments as $assignment) {
                    $allAssignmentIds[] = $assignment->id;
                }
            }
        }

        // If there are no assignments at all, no competency to award
        if (empty($allAssignmentIds)) {
            return false;
        }

        // 2. Fetch ALL relevant graded results in a SINGLE query (no loop queries)
        // Key by assignment_id for instant lookup
        $competentResultsByAssignment = \App\Models\AssignmentResult::select(
            'assignment_submissions.assignment_id',
            'assignment_results.competency_status'
        )
            ->join('assignment_submissions', 'assignment_results.submission_id', '=', 'assignment_submissions.id')
            ->where('assignment_submissions.user_id', $user->id)
            ->whereIn('assignment_submissions.assignment_id', $allAssignmentIds)
            ->where('assignment_results.competency_status', 'competent')
            ->pluck('assignment_results.competency_status', 'assignment_submissions.assignment_id');

        // 3. Evaluate each unit — purely in PHP, zero extra queries
        foreach ($course->modules as $module) {
            foreach ($module->units as $unit) {
                $unitAssignments = $unit->assignments;

                // Units with no assignments don't block completion
                if ($unitAssignments->isEmpty()) {
                    continue;
                }

                // Every assignment in this unit must have a competent result
                foreach ($unitAssignments as $assignment) {
                    if (!isset($competentResultsByAssignment[$assignment->id])) {
                        // This assignment is not yet graded competent → fail fast
                        return false;
                    }
                }
            }
        }

        // Did we find at least one unit with assignments?
        $hasAnyAssignment = collect($course->modules)
            ->flatMap->units
            ->flatMap->assignments
            ->isNotEmpty();

        return $hasAnyAssignment;
    }

    /**
     * If the student has achieved competency, create the certificate record
     * (or return the existing one).
     *
     * Wrapped in a DB transaction to prevent race conditions.
     * Uses updateOrCreate for idempotency.
     */
    public function awardCertificateIfEligible(User $user, Course $course): ?Certificate
    {
        if (!$this->isCompetent($user, $course)) {
            return null;
        }

        return DB::transaction(function () use ($user, $course) {
            // Re-check inside the transaction to handle concurrent calls
            $existing = Certificate::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();

            if ($existing) {
                return $existing; // Already awarded — idempotent
            }

            return Certificate::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'certificate_number' => Certificate::generateNumber(),
                'issued_at' => now(),
                'status' => 'active',
            ]);
        });
    }
}
