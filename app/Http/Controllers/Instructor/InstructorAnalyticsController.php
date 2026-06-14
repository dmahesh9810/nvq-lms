<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\StudentGamificationStat;
use App\Models\StudentTopicProgress;
use App\Models\MicroTopic;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class InstructorAnalyticsController extends Controller
{
    /**
     * Display the comprehensive instructor analytics dashboard.
     */
    public function dashboard()
    {
        $user = Auth::user();

        // 1. Fetch courses owned by the instructor with deep aggregated relationships.
        $courses = $user->instructedCourses()
            ->withCount(['enrollments', 'certificates'])
            ->with([
            'modules.units.lessons' => function ($query) {
            $query->withCount(['progress as completions_count' => function ($subQuery) {
                    $subQuery->whereNotNull('completed_at');
                }
                    ]);
            },
            'modules.units.quizzes' => function ($query) {
            $query->withCount([
                    'attempts as total_attempts' => function ($subQuery) {
                $subQuery->whereNotNull('completed_at');
            }
                    ,
                    'attempts as passed_attempts' => function ($subQuery) {
                $subQuery->where('result', 'PASS')->whereNotNull('completed_at');
            }
                ])->withAvg(['attempts' => function ($subQuery) {
                $subQuery->whereNotNull('completed_at');
            }
                ], 'score');
        }
        ])->get();

        // 2. Map calculated metrics to each course
        $courses->each(function ($course) {

            // A. Tally total lessons across all nested structures
            $totalLessons = 0;
            $courseTotalCompletions = 0;

            foreach ($course->modules as $module) {
                foreach ($module->units as $unit) {
                    foreach ($unit->lessons as $lesson) {
                        $totalLessons++;
                        $courseTotalCompletions += $lesson->completions_count;
                    }
                }
            }

            // B. Learning Progress - Average Completion %
            $totalEnrollments = $course->enrollments_count;
            $maxPossibleCompletions = $totalEnrollments * $totalLessons;

            if ($maxPossibleCompletions > 0) {
                $course->average_completion_percentage = round(($courseTotalCompletions / $maxPossibleCompletions) * 100, 1);
            }
            else {
                $course->average_completion_percentage = 0;
            }

            $course->total_lessons_count = $totalLessons;
        });

        return view('instructor.analytics.dashboard', compact('courses'));
    }

    /**
     * ─── STUDENT INTELLIGENCE DASHBOARD ─────────────────────────────────────
     * Shows per-student mastery, status (at_risk / learning / mastered),
     * days inactive, XP, streak, and topic breakdown.
     * This is the "Smart Mirror" — the lecturer can see WHO is really learning.
     */
    public function studentIntelligence()
    {
        $instructorId = Auth::id();
        $totalTopics  = MicroTopic::count();

        // Get all students enrolled in any course instructed by this lecturer
        $enrolledStudentIds = \App\Models\StudentEnrollment::whereHas('course', function ($q) use ($instructorId) {
            $q->where('instructor_id', $instructorId);
        })->pluck('user_id')->unique();

        // Fallback: if no enrollments found, show ALL students
        if ($enrolledStudentIds->isEmpty()) {
            $enrolledStudentIds = User::where('role', 'student')->pluck('id');
        }

        $students = User::whereIn('id', $enrolledStudentIds)
            ->where('role', 'student')
            ->get();

        $studentData = $students->map(function ($student) use ($totalTopics) {
            // Gamification stats
            $stat = StudentGamificationStat::where('user_id', $student->id)->first();

            // Topic progress
            $topicProgress = StudentTopicProgress::where('user_id', $student->id)->get();
            $masteredCount  = $topicProgress->where('mastery_score', '>=', 80)->count();
            $learningCount  = $topicProgress->whereBetween('mastery_score', [50, 79])->count();
            $strugglingTopics = $topicProgress->where('mastery_score', '<', 50)
                ->where('attempts', '>', 0)->count();

            // Average mastery score
            $avgMastery = $topicProgress->count() > 0
                ? round($topicProgress->avg('mastery_score'), 1)
                : 0;

            // Days since last activity
            $lastActivity = $stat?->last_activity_date
                ? Carbon::parse($stat->last_activity_date)
                : null;
            $daysInactive = $lastActivity
                ? (int) $lastActivity->startOfDay()->diffInDays(Carbon::today())
                : 999;

            // Status classification
            if ($daysInactive >= 5 || ($avgMastery < 30 && $topicProgress->count() > 0)) {
                $status = 'at_risk';
            } elseif ($avgMastery >= 70) {
                $status = 'mastered';
            } else {
                $status = 'learning';
            }

            // Never logged in?
            $neverActive = $topicProgress->isEmpty() && $daysInactive >= 999;

            return [
                'id'               => $student->id,
                'name'             => $student->name,
                'email'            => $student->email,
                'status'           => $neverActive ? 'never_active' : $status,
                'avg_mastery'      => $avgMastery,
                'mastered_count'   => $masteredCount,
                'learning_count'   => $learningCount,
                'struggling_count' => $strugglingTopics,
                'topics_attempted' => $topicProgress->count(),
                'total_topics'     => $totalTopics,
                'xp'               => $stat?->total_xp ?? 0,
                'streak'           => $stat?->current_streak ?? 0,
                'hearts'           => $stat?->hearts ?? 5,
                'days_inactive'    => $daysInactive >= 999 ? null : $daysInactive,
                'last_active_label'=> $lastActivity ? $lastActivity->diffForHumans() : 'Never',
                'shield_active'    => (bool) ($stat?->streak_shield_active ?? false),
            ];
        });

        // Sort: at_risk first → never_active → learning → mastered
        $statusOrder = ['at_risk' => 0, 'never_active' => 1, 'learning' => 2, 'mastered' => 3];
        $sorted = $studentData->sortBy(fn($s) => $statusOrder[$s['status']] ?? 99);

        // Summary stats for the header cards
        $summary = [
            'total'        => $sorted->count(),
            'at_risk'      => $sorted->where('status', 'at_risk')->count(),
            'never_active' => $sorted->where('status', 'never_active')->count(),
            'learning'     => $sorted->where('status', 'learning')->count(),
            'mastered'     => $sorted->where('status', 'mastered')->count(),
            'avg_class_mastery' => $sorted->count() > 0
                ? round($sorted->avg('avg_mastery'), 1)
                : 0,
        ];

        return view('instructor.analytics.students', [
            'students' => $sorted->values(),
            'summary'  => $summary,
        ]);
    }
}
