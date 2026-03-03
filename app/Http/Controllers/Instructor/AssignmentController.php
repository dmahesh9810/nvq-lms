<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssignmentController extends Controller
{
    /** List all assignments for courses owned by this instructor */
    public function index()
    {
        $assignments = Assignment::whereHas('unit.module.course', function ($q) {
            $q->where('instructor_id', Auth::id());
        })->with('unit.module.course')->withCount('submissions')->latest()->paginate(15);

        return view('instructor.assignments.index', compact('assignments'));
    }

    /** Show create form */
    public function create()
    {
        $units = Unit::whereHas('module.course', function ($q) {
            $q->where('instructor_id', Auth::id());
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
        if ($unit->module->course->instructor_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'You do not own the course this unit belongs to.');
        }

        $data['is_active'] = $request->boolean('is_active', true);
        Assignment::create($data);

        return redirect()->route('instructor.assignments.index')
            ->with('success', 'Assignment created successfully.');
    }

    /** Edit form */
    public function edit(Assignment $assignment)
    {
        $this->authorizeAssignment($assignment);
        $units = Unit::whereHas('module.course', function ($q) {
            $q->where('instructor_id', Auth::id());
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
        if ($assignment->unit->module->course->instructor_id !== Auth::id()
        && !Auth::user()->isAdmin()) {
            abort(403);
        }
    }
}
