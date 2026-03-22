<?php

namespace App\Http\Controllers\Assessor;

use App\Http\Controllers\Controller;
use App\Models\AssignmentSubmission;
use App\Models\AssignmentResult;
use App\Services\CourseCompletionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GradingController extends Controller
{
    /**
     * List all pending (ungraded) submissions for assessors to review.
     */
    public function index()
    {
        // All submissions not yet graded (reviewed by instructor)
        $pending = AssignmentSubmission::where('status', AssignmentSubmission::STATUS_REVIEWED)
            ->with(['assignment.unit.module.course', 'student', 'result'])
            ->latest()
            ->paginate(20);

        // Recently graded by this assessor
        $recentlyGraded = AssignmentResult::with([
            'submission.assignment.unit.module.course',
            'submission.student',
        ])
            ->where('assessor_id', Auth::id())
            ->latest('graded_at')
            ->take(10)
            ->get();

        $pendingCount = AssignmentSubmission::where('status', AssignmentSubmission::STATUS_REVIEWED)->count();

        return view('assessor.grading.index', compact('pending', 'recentlyGraded', 'pendingCount'));
    }

    /**
     * Open a submission to review the file and grade it.
     */
    public function show(AssignmentSubmission $submission)
    {
        $submission->load(['assignment.unit.module.course', 'student', 'result.assessor']);

        return view('assessor.grading.show', compact('submission'));
    }

    /**
     * Save the competency grade (C / NYC) and any feedback.
     * After saving, triggers the course completion check to auto-award certificates.
     */
    public function grade(Request $request, AssignmentSubmission $submission)
    {
        if (!$submission->isReviewed()) {
            return back()->with('error', 'This submission has not been reviewed by an instructor yet.');
        }

        if ($submission->isAssessed()) {
            return back()->with('error', 'This submission has already been assessed.');
        }

        $data = $request->validate([
            'competency_status' => 'required|in:competent,not_yet_competent',
            'marks' => 'nullable|integer|min:0',
            'feedback' => 'nullable|string|max:2000',
        ]);

        // Use DB Transaction to ensure both result and submission status are updated atomically
        DB::transaction(function () use ($submission, $data) {
            AssignmentResult::updateOrCreate(
                ['submission_id' => $submission->id],
                [
                    'assessor_id' => Auth::id(),
                    'competency_status' => $data['competency_status'],
                    'marks' => $data['marks'] ?? null,
                    'feedback' => $data['feedback'] ?? null,
                    'graded_at' => now(),
                ]
            );

            // Mark the submission as assessed and log the assessor ID
            $submission->update([
                'status' => AssignmentSubmission::STATUS_ASSESSED,
                'assessor_id' => Auth::id(),
            ]);
        });

        // ── Phase 4: Auto-award certificate if student is now fully competent ──
        // Using centralized CertificateService for unified eligibility rules
        $course = $submission->assignment->unit->module->course;
        $student = $submission->student;

        app(\App\Services\CertificateService::class)->checkAndIssueCertificate($student, $course);

        $message = 'Submission graded successfully.';
        if ($data['competency_status'] === 'competent') {
            $message .= ' Competency check triggered.';
        }

        return redirect()->route('assessor.grading.index')
            ->with('success', $message);
    }
}
