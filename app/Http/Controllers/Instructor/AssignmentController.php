<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssignmentController extends Controller
{
    /** List all assignments for courses owned by this instructor */
    public function index()
    {
        $userId = Auth::id();
        $assignments = Assignment::whereHas('unit.module.course', function ($q) use ($userId) {
            $q->where('instructor_id', $userId)
              ->orWhereHas('assignedInstructors', function ($q2) use ($userId) {
                  $q2->where('users.id', $userId);
              })
              ->orWhereHas('modules.assignedInstructors', function ($q3) use ($userId) {
                  $q3->where('users.id', $userId);
              });
        })->with('unit.module.course')->withCount('submissions')->latest()->paginate(15);

        return view('instructor.assignments.index', compact('assignments'));
    }

    /** Show create form */
    public function create()
    {
        $userId = Auth::id();
        $units = Unit::whereHas('module.course', function ($q) use ($userId) {
            $q->where('instructor_id', $userId)
              ->orWhereHas('assignedInstructors', function ($q2) use ($userId) {
                  $q2->where('users.id', $userId);
              })
              ->orWhereHas('modules.assignedInstructors', function ($q3) use ($userId) {
                  $q3->where('users.id', $userId);
              });
        })->with('module.course')->get();

        return view('instructor.assignments.create', compact('units'));
    }

    /** Store a new assignment */
    public function store(Request $request)
    {
        $data = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'max_marks' => 'nullable|integer|min:1',
        ]);

        // Authorization: unit must belong to a course owned by this instructor
        $unit = Unit::with('module.course')->findOrFail($data['unit_id']);
        $this->authorizeUnit($unit);

        $data['is_active'] = $request->boolean('is_active', true);
        Assignment::create($data);

        return redirect()->route('instructor.assignments.index')
            ->with('success', 'Assignment created successfully.');
    }

    /** Edit form */
    public function edit(Assignment $assignment)
    {
        $this->authorizeAssignment($assignment);
        $userId = Auth::id();
        $units = Unit::whereHas('module.course', function ($q) use ($userId) {
            $q->where('instructor_id', $userId)
              ->orWhereHas('assignedInstructors', function ($q2) use ($userId) {
                  $q2->where('users.id', $userId);
              })
              ->orWhereHas('modules.assignedInstructors', function ($q3) use ($userId) {
                  $q3->where('users.id', $userId);
              });
        })->with('module.course')->get();

        return view('instructor.assignments.edit', compact('assignment', 'units'));
    }

    /** Update an assignment */
    public function update(Request $request, Assignment $assignment)
    {
        $this->authorizeAssignment($assignment);
        $data = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'max_marks' => 'nullable|integer|min:1',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $assignment->update($data);

        return redirect()->route('instructor.assignments.index')
            ->with('success', 'Assignment updated.');
    }

    /** Delete an assignment */
    public function destroy(Assignment $assignment)
    {
        $this->authorizeAssignment($assignment);
        $assignment->delete();

        return redirect()->route('instructor.assignments.index')
            ->with('success', 'Assignment deleted.');
    }

    /** View all submissions for an assignment */
    public function submissions(Assignment $assignment)
    {
        $this->authorizeAssignment($assignment);
        $submissions = $assignment->submissions()
            ->with(['student', 'result.assessor'])
            ->latest()->get();

        return view('instructor.assignments.submissions', compact('assignment', 'submissions'));
    }

    /** Ensure the instructor owns this assignment's course */
    private function authorizeAssignment(Assignment $assignment): void
    {
        $this->authorizeUnit($assignment->unit);
    }

    /** Review and forward submission to assessor */
    public function reviewSubmission(Request $request, AssignmentSubmission $submission)
    {
        $this->authorizeAssignment($submission->assignment);

        $data = $request->validate([
            'instructor_review' => 'required|string|max:2000',
        ]);

        if ($submission->isAssessed()) {
            return back()->with('error', 'Cannot alter review for a submission that has already been assessed.');
        }

        DB::transaction(function () use ($submission, $data) {
            $submission->update([
                'instructor_id' => Auth::id(),
                'instructor_review' => $data['instructor_review'],
                'instructor_reviewed_at' => now(),
                'status' => AssignmentSubmission::STATUS_REVIEWED,
            ]);
        });

        return back()->with('success', 'Submission reviewed and forwarded to the assessor.');
    }

    /** Ensure the instructor owns this unit's course */
    private function authorizeUnit(Unit $unit): void
    {
        $userId = Auth::id();
        if (Auth::user()->isAdmin()) {
            return;
        }

        $course = $unit->module->course;

        if ($course->instructor_id === $userId) {
            return;
        }

        if ($course->assignedInstructors()->where('users.id', $userId)->exists()) {
            return;
        }

        if ($course->modules()->whereHas('assignedInstructors', function($q) use ($userId) {
            $q->where('users.id', $userId);
        })->exists()) {
            return;
        }

        abort(403, 'You do not own the course this unit belongs to.');
    }
}
