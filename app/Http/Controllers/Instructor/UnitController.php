<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\UnitRequest;
use App\Models\Course;
use App\Models\Module;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;

class UnitController extends Controller
{
    /**
     * Show form to create unit within a module.
     */
    public function create(Course $course, Module $module)
    {
        $canEdit = Auth::user()->isAdmin() || 
                   (Auth::user()->isInstructor() && in_array($course->status, ['draft', 'rejected']));

        abort_unless($canEdit, 403, 'Instructors can only add units while the course is in Draft or Rejected state.');

        $this->authorizeModule($course, $module);

        return view('instructor.units.create', compact('course', 'module'));
    }

    /**
     * Store a new unit.
     */
    public function store(UnitRequest $request, Course $course, Module $module)
    {
        $canEdit = Auth::user()->isAdmin() || 
                   (Auth::user()->isInstructor() && in_array($course->status, ['draft', 'rejected']));

        abort_unless($canEdit, 403, 'Instructors can only add units while the course is in Draft or Rejected state.');

        $this->authorizeModule($course, $module);

        $data = $request->validated();
        $data['module_id'] = $module->id;
        $data['is_active'] = $request->boolean('is_active', true);

        if (empty($data['order'])) {
            $data['order'] = $module->units()->max('order') + 1;
        }

        Unit::create($data);

        return redirect()
            ->route('instructor.courses.show', $course)
            ->with('success', 'Unit added successfully!');
    }

    /**
     * Show edit form for a unit.
     * Admin-only: instructors must use the change request flow.
     */
    public function edit(Course $course, Module $module, Unit $unit)
    {
        $canEdit = Auth::user()->isAdmin() || 
                   (Auth::user()->isInstructor() && in_array($course->status, ['draft', 'rejected']));

        abort_unless($canEdit, 403,
            'Instructors can only edit units while the course is in Draft or Rejected state. Please use the "Request Edit" button.');

        $this->authorizeModule($course, $module);

        return view('instructor.units.edit', compact('course', 'module', 'unit'));
    }

    /**
     * Update a unit.
     * Admin-only: instructors must use the change request flow.
     */
    public function update(UnitRequest $request, Course $course, Module $module, Unit $unit)
    {
        $canEdit = Auth::user()->isAdmin() || 
                   (Auth::user()->isInstructor() && in_array($course->status, ['draft', 'rejected']));

        abort_unless($canEdit, 403,
            'Instructors can only update units while the course is in Draft or Rejected state. Please submit a Request Edit instead.');

        $this->authorizeModule($course, $module);

        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        $unit->update($data);

        return redirect()
            ->route('instructor.courses.show', $course)
            ->with('success', 'Unit updated successfully!');
    }

    /**
     * Delete a unit.
     * Admin-only: instructors must use the change request flow.
     */
    public function destroy(Course $course, Module $module, Unit $unit)
    {
        $canEdit = Auth::user()->isAdmin() || 
                   (Auth::user()->isInstructor() && in_array($course->status, ['draft', 'rejected']));

        abort_unless($canEdit, 403,
            'Instructors can only delete units while the course is in Draft or Rejected state. Please submit a Request Delete instead.');

        $this->authorizeModule($course, $module);

        $unit->delete();

        return redirect()
            ->route('instructor.courses.show', $course)
            ->with('success', 'Unit deleted!');
    }

    private function authorizeModule(Course $course, Module $module): void
    {
        $isCourseAdmin = $course->instructor_id === \Illuminate\Support\Facades\Auth::id() || 
                         \Illuminate\Support\Facades\Auth::user()->isAdmin() || 
                         $course->assignedInstructors()->where('users.id', \Illuminate\Support\Facades\Auth::id())->exists();

        if ($isCourseAdmin) {
            return;
        }

        $isModuleAssigned = $module->assignedInstructors()->where('users.id', \Illuminate\Support\Facades\Auth::id())->exists();
        if ($isModuleAssigned) {
            return;
        }

        abort(403, 'You do not own this course and are not assigned to it or this module.');
    }
}
