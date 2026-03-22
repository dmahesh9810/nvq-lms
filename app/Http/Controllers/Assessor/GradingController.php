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
        // All submissions not yet verified (assessed by instructor)
        $pending = AssignmentSubmission::where('status', AssignmentSubmission::STATUS_INSTRUCTOR_ASSESSED)
            ->with(['assignment.unit.module.course', 'student', 'result'])
            ->latest()
            ->paginate(20);

        // Recently verified by this assessor
        $recentlyGraded = \App\Models\AssignmentSubmission::with([
            'assignment.unit.module.course',
            'student',
            'result'
        ])
            ->where('assessor_id', Auth::id())
            ->whereNotNull('verified_at')
            ->latest('verified_at')
            ->take(10)
            ->get();

        $pendingCount = AssignmentSubmission::where('status', AssignmentSubmission::STATUS_INSTRUCTOR_ASSESSED)->count();

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

    public function verify(Request $request, AssignmentSubmission $submission)
    {
        if (!$submission->isInstructorAssessed()) {
            return back()->with('error', 'This submission has not been evaluated by an instructor yet.');
        }

        if ($submission->isAssessorActioned()) {
            return back()->with('error', 'This submission has already been verified.');
        }

        $data = $request->validate([
            'action' => 'required|in:verify,reject',
            'note' => 'nullable|string|max:2000',
        ]);

        DB::transaction(function () use ($submission, $data) {
            $newStatus = $data['action'] === 'verify' 
                ? AssignmentSubmission::STATUS_ASSESSOR_VERIFIED 
                : AssignmentSubmission::STATUS_ASSESSOR_REJECTED;

            if ($data['action'] === 'verify') {
                AssignmentResult::updateOrCreate(
                    ['submission_id' => $submission->id],
                    [
                        'assessor_id' => Auth::id(),
                        'competency_status' => $submission->instructor_competency_status,
                        'feedback' => 'Verified by Assessor',
                        'graded_at' => now(),
                    ]
                );
            }

            $submission->update([
                'status' => $newStatus,
                'assessor_id' => Auth::id(),
                'assessor_verification_note' => $data['note'] ?? null,
                'verified_at' => now(),
            ]);

            \App\Models\VerificationLog::create([
                'assessor_id' => Auth::id(),
                'instructor_id' => $submission->instructor_id,
                'submission_id' => $submission->id,
                'action' => $data['action'],
                'note' => $data['note'] ?? null,
            ]);
        });

        // Auto-award certificate if verified and competent
        if ($data['action'] === 'verify' && $submission->instructor_competency_status === 'competent') {
            $course = $submission->assignment->unit->module->course;
            app(\App\Services\CertificateService::class)->checkAndIssueCertificate($submission->student, $course);
        }

        $msg = $data['action'] === 'verify' ? 'Submission successfully verified.' : 'Submission rejected.';
        return redirect()->route('assessor.grading.index')->with('success', $msg);
    }
}
