<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    /** List all assignments from the student's enrolled courses */
    public function index()
    {
        $user = Auth::user();
        $assignments = Assignment::whereHas('unit.module.course.enrollments', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->where('is_active', true)
            ->with('unit.module.course')
            ->latest()
            ->paginate(15);

        // Map user's submission keyed by assignment_id
        $submissions = AssignmentSubmission::where('user_id', $user->id)
            ->whereIn('assignment_id', $assignments->pluck('id'))
            ->with('result')
            ->get()
            ->keyBy('assignment_id');

        return view('student.assignments.index', compact('assignments', 'submissions'));
    }

    /** Show a single assignment with upload form / submission status */
    public function show(Assignment $assignment)
    {
        $this->authorizeAccess($assignment);

        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('user_id', Auth::id())
            ->with('result.assessor')
            ->first();

        return view('student.assignments.show', compact('assignment', 'submission'));
    }

    /** Submit or re-submit an assignment file */
    public function submit(Request $request, Assignment $assignment)
    {
        $this->authorizeAccess($assignment);

        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,zip|max:10240',
        ]);

        $existing = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('user_id', Auth::id())
            ->first();

        // Block re-submission if already graded
        if ($existing && $existing->status === 'graded') {
            return back()->with('error', 'This assignment has already been graded and cannot be resubmitted.');
        }

        // Store the file on the public disk
        $path = $request->file('file')->store('assignments/submissions', 'public');

        if ($existing) {
            // Delete old file and update
            Storage::disk('public')->delete($existing->file_path);
            $existing->update([
                'file_path' => $path,
                'submitted_at' => now(),
                'status' => 'resubmitted',
            ]);
        }
        else {
            AssignmentSubmission::create([
                'assignment_id' => $assignment->id,
                'user_id' => Auth::id(),
                'file_path' => $path,
                'submitted_at' => now(),
                'status' => 'submitted',
            ]);
        }

        return redirect()->route('student.assignments.show', $assignment)
            ->with('success', 'Assignment submitted successfully.');
    }

    /** Ensure the student is enrolled in the assignment's course */
    private function authorizeAccess(Assignment $assignment): void
    {
        $course = $assignment->unit->module->course;
        abort_unless(
            $course->enrollments()->where('user_id', Auth::id())->exists(),
            403,
            'You are not enrolled in this course.'
        );
    }
}
